<?php

namespace Styla\CmsIntegration\Styla\Api;

use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\Currency\CurrencyEntity;
use Styla\CmsIntegration\Exception\UseCaseInteractorException;
use Styla\CmsIntegration\Styla\Api\DTO\Product\ProductAttributeInfo;
use Styla\CmsIntegration\Styla\Api\DTO\Product\ProductAttributeInfoList;
use Styla\CmsIntegration\Styla\Api\DTO\Product\ProductAttributeOption;
use Styla\CmsIntegration\Styla\Api\DTO\Product\ProductAttributeOptionsList;
use Styla\CmsIntegration\Styla\Api\DTO\Product\ProductDetailInfo;
use Styla\CmsIntegration\Styla\Api\DTO\Product\ProductReferenceInfo;
use Styla\CmsIntegration\Styla\Api\DTO\Product\ProductReferenceInfoList;
use Styla\CmsIntegration\Styla\Api\DTO\Product\TaxInfo;

class ProductDetailsTranslator
{
    protected EntityRepository $productRepository;
    protected EntityRepository $currencyRepository;

    public function __construct(
        EntityRepository $productRepository,
        EntityRepository $currencyRepository
    ) {
        $this->productRepository = $productRepository;
        $this->currencyRepository = $currencyRepository;
    }

    public function translateProductToDetailsInfo(
        ProductEntity $productEntity,
        Context $context
    ): ProductDetailInfo {
        
        $priceEntity = $productEntity->getCurrencyPrice($context->getCurrencyId());
        
        $currencySymbol = $this->getCurrencySymbol($context);

        $oldPrice = null;
        $price = null;
        if ($priceEntity) {
            $price = $priceEntity->getGross();
            $oldPrice = $priceEntity->getGross();

            if ($priceEntity->getListPrice() && $priceEntity->getListPrice()->getGross() > $price) {
                $oldPrice = $priceEntity->getListPrice()->getGross();
            }
        }

        if ($productEntity->getCategories()) {
            $categoriesIds = [];
            foreach ($productEntity->getCategories()->getElements() as $category) {
                $categoriesIds[] = $category->getId();
            }
        } else {
            $categoriesIds = [];
        }

        $brand = '';
        if ($productEntity->getManufacturer()) {
            $brand = $productEntity->getManufacturer()->getName();
        }
       
        return new ProductDetailInfo(
            $productEntity->getId(),
            $productEntity->getActive(),
            $price,
            $oldPrice,
            $productEntity->getName() ?? '',
            sprintf('#{price} %s', $currencySymbol),
            $productEntity->getMinPurchase(),
            $productEntity->getMaxPurchase(),
            $productEntity->getDescription() ?? '',
            '',
            $brand,
            $categoriesIds,
            $this->translateToTaxInfo($productEntity),
            $this->translateToTheAttributesList($productEntity, $context)
        );
    }

    private function translateToTaxInfo(ProductEntity $productEntity): ?TaxInfo
    {
        $tax = $productEntity->getTax();
        if (!$tax) {
            return null;
        }

        return new TaxInfo(
            $tax->getTaxRate(),
            true,
            true,
            $tax->getName()
        );
    }

    private function translateToTheAttributesList(
        ProductEntity $productEntity,
        Context $context
    ): ProductAttributeInfoList {
        $attributeList = new ProductAttributeInfoList();

        $productVariants = $this->getProductVariants($productEntity, $context);

        $availablePropertyGroups = [];

        foreach ($productVariants as $productVariant) {
            $options = $productVariant->getOptions();
            if ($options) {
                foreach ($options as $option) {
                        $availablePropertyGroups[$option->getGroup()->getId()] = $option->getGroup()->getName();
                }
            }
        }
       
            foreach ($availablePropertyGroups as $groupId => $groupLabel) {
                $attribute = new ProductAttributeInfo(
                    $groupId,
                    $groupLabel,
                    $this->translateOptions($groupId, $productVariants, $context)
                );
                $attributeList->add($attribute);
            }    

        return $attributeList;
    }

    private function translateOptions(
        string $groupId,
        ProductCollection $productVariants,
        Context $context
    ): ProductAttributeOptionsList {
        $productAttributeOptionsList = new ProductAttributeOptionsList();

        $productsPerOptionHasMap = [];
        $optionsByIdHashMap = [];

        foreach ($productVariants as $productVariant) {
            $options = $productVariant->getOptions();
            foreach ($options as $option) {
                if ($option->getGroup()->getId() == $groupId) {
                    $optionsByIdHashMap[$option->getId()] = $option;
                    $productsPerOptionHasMap[$option->getId()][] = $productVariant;
                }
            }
        }

        /**
         * @var  PropertyGroupOptionEntity $option
         * @var  ProductEntity $productVariant
         */
        foreach ($productsPerOptionHasMap as $optionId => $optionProductVariants) {
            $productAttributeOption = $this->translateProductAttributeOption(
                $optionsByIdHashMap[$optionId],
                $optionProductVariants,
                $context
            );
            $productAttributeOptionsList->add($productAttributeOption);
        }

        return $productAttributeOptionsList;
    }

    /**
     * @param PropertyGroupOptionEntity $option
     * @param ProductEntity[] $products
     * @param Context $context
     *
     * @return ProductAttributeOption
     */
    private function translateProductAttributeOption(PropertyGroupOptionEntity $option, array $products, Context $context): ProductAttributeOption
    {   
        return new ProductAttributeOption(
            $option->getId(),
            $option->getName() ?? '',
            $this->translateProductEntityToProductReference($products, $context)
        );
    }

    /**
     * @param ProductEntity[] $products
     * @param Context $context
     *
     * @return ProductReferenceInfoList
     */
    private function translateProductEntityToProductReference(
        array $products,
        Context $context
    ): ProductReferenceInfoList {
        $list = new ProductReferenceInfoList();
        
        foreach ($products as $product) {
            $price = $product->getCurrencyPrice($context->getCurrencyId());
    
            $oldPrice = null;
            
            /*
            shopware6 product entity does no longer have the cheapest price property
             Refer: https://github.com/shopware/shopware/blob/trunk/src/Core/Content/Product/ProductEntity.php
            */
            /*if ($product->getCheapestPrice()) {
                $oldPrice = $product->getCheapestPrice()->getCurrencyPrice($context->getCurrencyId());
            }*/

            $list->add(
                new ProductReferenceInfo(
                    $product->getId(),
                    $product->getActive(),
                    $price ? $price->getNet() : null,
                    $oldPrice ? $oldPrice->getNet() : null,
                )
            );
        }

        return $list;
    }

    private function getProductVariants(ProductEntity $productEntity, Context $context): ProductCollection
    {
        $criteria = new Criteria();

         $criteria
            ->addAssociation('categories')
            ->addAssociation('cover.media')
            ->addAssociation('prices')
            ->addAssociation('translation')
            ->addAssociation('options.translation')
            ->addAssociation('options.group.translation');

        $criteria->addFilter(new EqualsFilter('parentId', $productEntity->getId()));

        $result = $this->productRepository->search($criteria, $context);

        return $result->getEntities();
    }

    private function getCurrencySymbol(Context $context): string
    {
        $result = $this->currencyRepository->search(new Criteria([$context->getCurrencyId()]), $context);
        /** @var CurrencyEntity $currency */
        $currency = $result->first();
        if (!$currency) {
            throw new UseCaseInteractorException(
                sprintf('Currency[id=%s] was not found', $context->getCurrencyId())
            );
        }

        return $currency->getSymbol();
    }
}
