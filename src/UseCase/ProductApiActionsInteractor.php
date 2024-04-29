<?php

namespace Styla\CmsIntegration\UseCase;

use Psr\Log\LoggerInterface;
use Shopware\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Product\SalesChannel\ProductAvailableFilter;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\AndFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NandFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\OrFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Grouping\FieldGrouping;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Styla\CmsIntegration\Exception\ProductNotFoundException;
use Styla\CmsIntegration\Exception\UseCaseInteractorException;
use Styla\CmsIntegration\Styla\Api\DTO\Product\ProductDetailInfo;
use Styla\CmsIntegration\Styla\Api\DTO\Product\ProductInfoList;
use Styla\CmsIntegration\Styla\Api\ProductDetailsTranslator;
use Styla\CmsIntegration\Styla\Api\ProductInfoTranslator;

class ProductApiActionsInteractor
{
    public const PRODUCT_LIST_DEFAULT_LIMIT = 100;

    protected EntityRepository $productRepository;
    protected SalesChannelRepository $productSalesChannelRepository;
    protected ProductDetailsTranslator $productDetailsTranslator;
    protected ProductInfoTranslator $productInfoTranslator;
    protected LoggerInterface $logger;

    public function __construct(
        EntityRepository $entityRepository,
        SalesChannelRepository $productSalesChannelRepository,
        ProductDetailsTranslator $productDetailsTranslator,
        ProductInfoTranslator $productInfoTranslator,
        LoggerInterface $logger
    ) {
        $this->productRepository = $entityRepository;
        $this->productSalesChannelRepository = $productSalesChannelRepository;
        $this->productDetailsTranslator = $productDetailsTranslator;
        $this->productInfoTranslator = $productInfoTranslator;
        $this->logger = $logger;
    }

    /**
     * @param string|null $categoryId
     * @param string|null $term
     * @param int $limit
     * @param int|null $pageNumber
     * @param SalesChannelContext $salesChannelContext
     *
     * @return ProductInfoList
     * @throws UseCaseInteractorException
     */
    public function findProducts(
        ?string $categoryId,
        ?string $term,
        SalesChannelContext $salesChannelContext,
        int $limit = self::PRODUCT_LIST_DEFAULT_LIMIT,
        ?int $pageNumber = null
    ): ProductInfoList {
        try {
            $context = $salesChannelContext->getContext();

            $criteria = new Criteria();
            $criteria->addSorting(new FieldSorting('id'));

            if ($limit) {
                $criteria->setLimit($limit);

                if ($pageNumber) {
                    $offset = ($pageNumber - 1) * $limit;
                    $criteria->setOffset($offset);
                }
            }

            $criteriaFilter = new AndFilter();
            $criteriaFilter->addQuery(
                new ProductAvailableFilter(
                    $salesChannelContext->getSalesChannel()->getId(),
                    ProductVisibilityDefinition::VISIBILITY_ALL
                )
            );
            if ($categoryId) {
                $criteriaFilter->addQuery(new EqualsFilter('categoriesRo.id', $categoryId));
            }
            $criteria->addFilter($criteriaFilter);


            $products = $term
                ? $this->findProductsByTerm($criteria, $term, $salesChannelContext)
                : $this->findSimpleOrContainerProduct($criteria, $context);

            return $this->productInfoTranslator
                ->translateToProductInfoList($products, $context);
        } catch (\Throwable $exception) {
            $message = sprintf('Failed to get product list, reason: %s', $exception->getMessage());
            $this->logger->error($message, ['exception' => $exception]);

            if ($exception instanceof UseCaseInteractorException) {
                throw $exception;
            }

            throw new UseCaseInteractorException(
                $message,
                UseCaseInteractorException::CODE_FAILED_TO_GET_PRODUCT_LIST,
                $exception
            );
        }
    }

    private function findProductsByTerm(
        Criteria $baseCriteria,
        string $term,
        SalesChannelContext $salesChannelContext
    ): ProductCollection {
        $criteriaFilter = new AndFilter();
        $criteriaFilter->addQuery(
            new OrFilter(
                [
                    new NandFilter([new EqualsFilter('parentId', null)]),
                    new EqualsFilter('product.childCount', 0),
                    new EqualsFilter('product.childCount', null)
                ]
            )
        );

        $terms = explode(' ', $term);

        $termsFilter = new AndFilter();
        foreach ($terms as $term) {
            $term = trim($term);
            $termFilter = new OrFilter(
                [
                    new ContainsFilter('name', $term),
                    new ContainsFilter('description', $term),
                    new ContainsFilter('categoriesRo.name', $term)
                ]
            );
            $termsFilter->addQuery($termFilter);
        }
        $criteriaFilter->addQuery($termsFilter);

        $baseCriteria->addFilter($criteriaFilter);

        $baseCriteria->addGroupField(new FieldGrouping('displayGroup'));

        $matchedProductAndProductVariants = $this->productSalesChannelRepository
            ->search($baseCriteria, $salesChannelContext)
            ->getEntities();

        $parentEntitiesIds = [];
        /** @var ProductEntity $productEntity */
        foreach ($matchedProductAndProductVariants as $productEntity) {
            if ($productEntity->getParentId()) {
                $parentEntitiesIds[] = $productEntity->getParentId();
            }
        }

        $parentProductEntitiesHashMap = [];
        if (count($parentEntitiesIds) > 0) {
            $parentProductsSearchResult = $this->productRepository
                ->search(new Criteria($parentEntitiesIds), $salesChannelContext->getContext());
            /** @var ProductEntity $parentProduct */
            foreach ($parentProductsSearchResult->getEntities() as $parentProduct) {
                $parentProductEntitiesHashMap[$parentProduct->getId()] = $parentProduct;
            }
        }

        $matchedProductsCollection = new ProductCollection();
        /** @var ProductEntity $productEntity */
        foreach ($matchedProductAndProductVariants as $productEntity) {
            if ($productEntity->getParentId()) {
                if (!isset($parentProductEntitiesHashMap[$productEntity->getParentId()])) {
                    continue;
                }

                $matchedProductsCollection->add($parentProductEntitiesHashMap[$productEntity->getParentId()]);
            } else {
                $matchedProductsCollection->add($productEntity);
            }
        }

        return $matchedProductsCollection;
    }

    private function findSimpleOrContainerProduct(Criteria $criteria, Context $context): ProductCollection
    {
        $criteria->addFilter(new EqualsFilter('parentId', null));

        /** @var ProductCollection $result */
        $result = $this->productRepository
            ->search($criteria, $context)->getEntities();

        return $result;
    }

    /**
     * @param string $productId
     * @param SalesChannelContext $salesChannelContext
     *
     * @return ProductDetailInfo
     * @throws ProductNotFoundException Throw this exception if product not found
     * @throws UseCaseInteractorException In case of any other exception
     */
    public function getProductDetails(string $productId, SalesChannelContext $salesChannelContext): ProductDetailInfo
    {
        try {
            $criteria = new Criteria([$productId]);
            $criteria
                ->addAssociation('categories')
                ->addAssociation('manufacturer')
                ->addAssociation('cover.media')
                ->addAssociation('prices')
                ->addAssociation('translation')
                ->addAssociation('options.translation')
                ->addAssociation('options.group.translation');

            $result = $this->productRepository->search($criteria, $salesChannelContext->getContext());
            if ($result->getTotal() === 0) {
                throw new ProductNotFoundException(sprintf('Product[id: %s] not found', $productId));
            }

            return $this->translateProductEntityToProductDetails($result->first(), $salesChannelContext->getContext());
        } catch (\Throwable $exception) {
            $message = sprintf('Failed to get product details info, reason: %s', $exception->getMessage());
            $this->logger->error($message, ['exception' => $exception]);

            if ($exception instanceof UseCaseInteractorException) {
                throw $exception;
            }

            throw new UseCaseInteractorException(
                $message,
                UseCaseInteractorException::CODE_FAILED_TO_GET_PRODUCT_DETAILS,
                $exception
            );
        }
    }

    /**
     * @param ProductEntity $productEntity
     * @param Context $context
     *
     * @return ProductDetailInfo
     * @throws \Throwable
     */
    private function translateProductEntityToProductDetails(
        ProductEntity $productEntity,
        Context $context
    ): ProductDetailInfo {
        try {
            return $this->productDetailsTranslator
                ->translateProductToDetailsInfo($productEntity, $context);
        } catch (\Throwable $exception) {
            $this->logger->error(
                'Exception happened during the styla product details translation',
                [
                    'exception' => $exception
                ]
            );

            throw $exception;
        }
    }
}
