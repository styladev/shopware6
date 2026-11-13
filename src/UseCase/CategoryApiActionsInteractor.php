<?php

namespace Styla\CmsIntegration\UseCase;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Styla\CmsIntegration\Exception\UseCaseInteractorException;
use Styla\CmsIntegration\Styla\Api\CategoryInfoTranslator;
use Styla\CmsIntegration\Styla\Api\DTO\Category\CategoryInfoList;

class CategoryApiActionsInteractor
{
    private EntityRepository $categoryRepository;
    private CategoryInfoTranslator $translator;
    private LoggerInterface $logger;

    public function __construct(
        EntityRepository $categoryRepository,
        CategoryInfoTranslator $translator,
        LoggerInterface $logger
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->translator = $translator;
        $this->logger = $logger;
    }

    /**
     * @param int|null $limit
     * @param int|null $numberOfPage
     * @param SalesChannelContext $salesChannelContext
     *
     * @return CategoryInfoList
     * @throws UseCaseInteractorException
     */
    public function getCategoriesList(
        ?int $limit,
        ?int $numberOfPage,
        SalesChannelContext $salesChannelContext
    ): CategoryInfoList {
        try {
            $criteria = new Criteria();

            // Only the calling sales channel's navigation tree, active categories only
            $criteria->addFilter(new EqualsFilter('id', $salesChannelContext->getSalesChannel()->getNavigationCategoryId()));
            $criteria->addFilter(new EqualsFilter('active', true));

            if ($limit) {
                $criteria->setLimit($limit);
            }

            if ($numberOfPage) {
                $offset = ($numberOfPage - 1) * $limit;
                $criteria->setOffset($offset);
            }

            $context  = $salesChannelContext->getContext();

            $categories = $this->categoryRepository->search($criteria, $context);
            return $this->translator->translateCategoriesList($categories->getEntities(), $context);
        } catch (\Throwable $exception) {
            $message = sprintf('Could not get styla categories list, reason: %s', $exception->getMessage());
            $this->logger->error(
                $message,
                [
                    'exception' => $exception,
                    // Never log the SalesChannelContext itself: it contains the context token and customer data
                    'salesChannelId' => $salesChannelContext->getSalesChannelId(),
                    'languageId' => $salesChannelContext->getLanguageId(),
                ]
            );

            if ($exception instanceof UseCaseInteractorException) {
                throw $exception;
            }

            throw new UseCaseInteractorException(
                $message,
                UseCaseInteractorException::CODE_FAILED_TO_GET_CATEGORIES_LIST,
                $exception
            );
        }
    }
}
