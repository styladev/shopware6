<?php

namespace Styla\CmsIntegration\Styla\Synchronization;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\AndFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Styla\CmsIntegration\Configuration\ConfigurationInterface;
use Styla\CmsIntegration\Entity\StylaIntegration\StylaSynchronizationDalHelper;
use Styla\CmsIntegration\Entity\StylaIntegration\StylaPagesSynchronization;
use Styla\CmsIntegration\Entity\StylaPage\StylaPage;
use Styla\CmsIntegration\Entity\StylaPage\StylaPageCollection;
use Styla\CmsIntegration\Entity\StylaPage\StylaPageDefinition;
use Styla\CmsIntegration\Exception\SynchronizationInstanceStateIsInvalid;
use Styla\CmsIntegration\Styla\Client\ClientRegistry;
use Styla\CmsIntegration\Styla\Client\DTO\GeneralPageInfo;

class PagesListSynchronizationProcessor
{
    private const BATCH_SIZE = 100;

    private ClientRegistry $clientRegistry;
    private StylaSynchronizationDalHelper $stylaSynchronizationDalHelper;
    private EntityRepository $stylaPagesRepository;
    private ConfigurationInterface $configuration;
    private CacheInvalidatorFactory $cacheInvalidatorFactory;
    private PageDataMapper $pageDataMapper;
    private LoggerInterface $logger;

    public function __construct(
        ClientRegistry $clientRegistry,
        StylaSynchronizationDalHelper $stylaSynchronizationDalHelper,
        EntityRepository $stylaPagesRepository,
        ConfigurationInterface $configuration,
        CacheInvalidatorFactory $cacheInvalidatorFactory,
        PageDataMapper $pageDataMapper,
        LoggerInterface $logger
    ) {
        $this->clientRegistry = $clientRegistry;
        $this->stylaSynchronizationDalHelper = $stylaSynchronizationDalHelper;
        $this->stylaPagesRepository = $stylaPagesRepository;
        $this->configuration = $configuration;
        $this->cacheInvalidatorFactory = $cacheInvalidatorFactory;
        $this->pageDataMapper = $pageDataMapper;
        $this->logger = $logger;
    }

    /**
     * @param StylaPagesSynchronization $synchronization
     * @param Context $context
     *
     * @throws \Throwable
     */
    public function synchronizePages(StylaPagesSynchronization $synchronization = null, Context $context): void
    {
        if (!$synchronization) {
            $synchronizationId = $this->stylaSynchronizationDalHelper->createSynchronization($context);
            $synchronization = $this->stylaSynchronizationDalHelper->getSynchronizationById($synchronizationId, $context);
        }
        
        $this->stylaSynchronizationDalHelper->markSynchronizationAsInProgress($synchronization, $context);

        $isSuccess = true;

        $cacheInvalidator = $this->cacheInvalidatorFactory->create($context);
        foreach ($this->configuration->getDefinedAccountNames() as $accountName) {
            $isAccountSynchronizationSuccess = $this->stylaSynchronizationDalHelper->transactional(
                function () use ($cacheInvalidator, $accountName, $context) {
                    $this->synchronizeAccountPages(
                        $accountName,
                        $context,
                        $cacheInvalidator
                    );
                    $cacheInvalidator->invalidateCaches();
                },
                function (\Throwable $exception) use ($cacheInvalidator, $accountName) {
                    $cacheInvalidator->clearState();

                    $this->logger->error(
                        sprintf(
                            'Failed to sync styla pages for account %s, reason: %s',
                            $accountName,
                            $exception->getMessage()
                        ),
                        [
                            'exception' => $exception
                        ]
                    );
                }
            );

            if (!$isAccountSynchronizationSuccess) {
                $isSuccess = false;
            }
        }

        $this->removePagesFromRemovedAccounts(
            $this->configuration->getDefinedAccountNames(),
            $context,
            $cacheInvalidator
        );
        $cacheInvalidator->invalidateCaches();

        if ($isSuccess) {
            $this->stylaSynchronizationDalHelper->markSynchronizationAsSuccess($synchronization, $context);
        } else {
            $this->stylaSynchronizationDalHelper->markSynchronizationAsFailed($synchronization, $context);
        }
    }

    private function assertSynchronizationEntity(StylaPagesSynchronization $synchronization)
    {
        if ($synchronization->getStatus() == StylaPagesSynchronization::STATUS_IN_PROGRESS) {
            throw new SynchronizationInstanceStateIsInvalid('Synchronization is already in progress');
        }
        if (!$synchronization->getActive()) {
            throw new SynchronizationInstanceStateIsInvalid('Synchronization is inactive');
        }
    }

    private function synchronizeAccountPages(
        string $accountName,
        Context $context,
        CacheInvalidator $cacheInvalidator
    ): void {
        $client = $this->clientRegistry->getClientByAccountName($accountName);

        $foundPageIds = [];

        $newPagesBatch = [];
        $newPagesBatchItemsToFlush = self::BATCH_SIZE;

        $existingPagesBatch = [];
        $existingPagesBatchItemsToFlush = self::BATCH_SIZE;
        foreach ($client->getPagesList(self::BATCH_SIZE) as $generalPageInfo) {
            // skip deleted pages in order them to be removed
            if ($generalPageInfo->getDeletedAt()) {
                continue;
            }

            $shopwareStylaPageEntity = $this->findExistingPage($generalPageInfo, $context);
            if ($shopwareStylaPageEntity) {
                $foundPageIds[] = $shopwareStylaPageEntity->getId();
            } else {
                $shopwareStylaPageEntity = new StylaPage();
            }

            $changedFieldsHasMap = $this->pageDataMapper->map($generalPageInfo, $shopwareStylaPageEntity);
            if (!$changedFieldsHasMap) {
                continue;
            }

            if ($shopwareStylaPageEntity->getId()) {
                $cacheInvalidator->addPageForCacheInvalidation($shopwareStylaPageEntity);
                $changedFieldsHasMap['id'] = $shopwareStylaPageEntity->getId();

                $existingPagesBatch[] = $changedFieldsHasMap;
            } else {
                $cacheInvalidator->addPageForHttpCacheInvalidation($shopwareStylaPageEntity);

                $newPagesBatch[] = $changedFieldsHasMap;
            }

            if (--$newPagesBatchItemsToFlush === 0) {
                $newEntitiesIds = $this->createStylaPages($newPagesBatch, $context);
                $foundPageIds = array_merge($foundPageIds, $newEntitiesIds);

                $newPagesBatch = [];
                $newPagesBatchItemsToFlush = self::BATCH_SIZE;
            }
            if (--$existingPagesBatchItemsToFlush === 0) {
                $this->stylaPagesRepository->update($existingPagesBatch, $context);
                $existingPagesBatch = [];
                $existingPagesBatchItemsToFlush = self::BATCH_SIZE;
            }
        }

        $this->removeNotFoundEntities($foundPageIds, $accountName, $context, $cacheInvalidator);

        if ($newPagesBatch) {
            $this->stylaPagesRepository->create($newPagesBatch, $context);
        }

        if ($existingPagesBatch) {
            $this->stylaPagesRepository->update($existingPagesBatch, $context);
        }
    }

    /**
     * @return string[] List of ids for new entities
     */
    private function createStylaPages($newPagesBatch, $context): array
    {
        $result = $this->stylaPagesRepository->create($newPagesBatch, $context);

        return $result->getPrimaryKeys(StylaPageDefinition::ENTITY_NAME);
    }

    private function findExistingPage(GeneralPageInfo $generalPageInfo, Context $context): ?StylaPage
    {
        $criteria = new Criteria();
        $criteria->addFilter(
            new AndFilter(
                [
                    new EqualsFilter('path', $generalPageInfo->getPath()),
                    new EqualsFilter('accountName', $generalPageInfo->getAccountName())
                ]
            )
        );

        $result = $this->stylaPagesRepository->search($criteria, $context);

        return $result->first();
    }

    private function removeNotFoundEntities(
        $foundEntitiesIds,
        string $accountName,
        Context $context,
        CacheInvalidator $cacheInvalidator
    ): void {
        $criteria = new Criteria();

        if ($foundEntitiesIds) {
            $foundEntitiesIds = array_unique($foundEntitiesIds);

            $criteria->addFilter(
                new AndFilter(
                    [
                        new EqualsFilter('accountName', $accountName),
                        new NotFilter(NotFilter::CONNECTION_AND, [new EqualsAnyFilter('id', $foundEntitiesIds)])
                    ]
                )
            );
        } else {
            $criteria->addFilter(new EqualsFilter('accountName', $accountName));
        }

        $this->removePagesByCriteria($criteria, $context, $cacheInvalidator);
    }

    private function removePagesFromRemovedAccounts(array $accountNames, Context $context, CacheInvalidator $cacheInvalidator): void
    {
        $criteria = new Criteria();

        if ($accountNames) {
            $criteria->addFilter(
                new NotFilter(NotFilter::CONNECTION_AND, [new EqualsAnyFilter('accountName', $accountNames)])
            );
        }

        $this->removePagesByCriteria($criteria, $context, $cacheInvalidator);
    }

    private function removePagesByCriteria(Criteria $criteria, Context $context, CacheInvalidator $cacheInvalidator)
    {
        // Raw connection was not used to support shopware events
        $entitiesForRemove = $this->stylaPagesRepository->search($criteria, $context);
        if ($entitiesForRemove->getTotal() === 0) {
            return;
        }

        $deleteDataSet = [];
        foreach ($entitiesForRemove->getIds() as $id) {
            $deleteDataSet[] = ['id' => $id];
        }

        $this->stylaPagesRepository->delete($deleteDataSet, $context);

        /** @var StylaPageCollection $entities */
        $entities = $entitiesForRemove->getEntities();
        $cacheInvalidator->addPagesForCacheInvalidation($entities);
    }
}
