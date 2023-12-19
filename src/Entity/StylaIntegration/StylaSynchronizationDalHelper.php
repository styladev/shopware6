<?php

namespace Styla\CmsIntegration\Entity\StylaIntegration;

use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InvalidAggregationQueryException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Aggregation\Metric\CountAggregation;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\AndFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NandFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;

class StylaSynchronizationDalHelper
{
    private Connection $connection;
    private EntityRepository $synchronizationsRepository;
    private LoggerInterface $logger;

    public function __construct(
        Connection $connection,
        EntityRepository $synchronizationsRepository,
        LoggerInterface $logger
    ) {
        $this->connection = $connection;
        $this->synchronizationsRepository = $synchronizationsRepository;
        $this->logger = $logger;
    }

    public function transactional(\Closure $workDelegate, \Closure $onFailDelegate = null, &$transactionalWorkResult = null): bool
    {
        $transactionActive = false;
        try {
            $transactionActive = $this->connection->beginTransaction();

            $transactionalWorkResult = $workDelegate();
            $this->connection->commit();

            return true;
        } catch (\Throwable $exception) {
            if ($transactionActive) {
                // Avoid rollback in case if begin transaction was not executed
                $this->rollback();
            }

            if ($onFailDelegate) {
                $onFailDelegate($exception);
            }

            return false;
        }
    }

    private function rollback()
    {
        try {
            $this->connection->rollBack();
        } catch (\Throwable $exception) {
            $this->logger->critical(
                sprintf('Could not rollback Database transaction, reason: %s', $exception->getMessage()),
                [
                    'exception' => $exception
                ]
            );
        }
    }

    public function createSynchronization(Context $context): string
    {
        $createEntityResult =$this->synchronizationsRepository->create(
            [
                [
                    'status' => StylaPagesSynchronization::STATUS_NEW,
                    'active' => true
                ]
            ],
            $context
        );
        $keys = $createEntityResult->getPrimaryKeys(StylaPagesSynchronizationDefinition::ENTITY_NAME);
        if (empty($keys)) {
            throw new \RuntimeException('Could not create styla synchronization entity');
        }

        return reset($keys);
    }

    public function markSynchronizationAsSuccess(StylaPagesSynchronization $synchronization, Context $context)
    {
        $this->synchronizationsRepository->update(
            [
                [
                    'id' => $synchronization->getId(),
                    'status' => StylaPagesSynchronization::STATUS_SUCCESS,
                    'finishedAt' => new \DateTime('now', new \DateTimeZone('UTC')),
                    'active' => false
                ]
            ],
            $context
        );
    }

    public function tryMarkSynchronizationAsFailed(StylaPagesSynchronization $synchronization, Context $context)
    {
        try {
            $this->markSynchronizationAsFailed($synchronization, $context);
        } catch (\Throwable $exception) {
            $this->logger->error(
                sprintf(
                    'Exception happened during change synchronization status to "failed". Message: %s',
                    $exception->getMessage()
                ),
                [
                    'synchronizationId' => $synchronization->getId(),
                    'exception' => $exception,
                    'context' => $context
                ]
            );
        }
    }

    public function markSynchronizationAsFailed(StylaPagesSynchronization $synchronization, Context $context)
    {
        $this->synchronizationsRepository->update(
            [
                [
                    'id' => $synchronization->getId(),
                    'status' => StylaPagesSynchronization::STATUS_FAILED,
                    'finishedAt' => new \DateTime('now', new \DateTimeZone('UTC')),
                    'active' => false
                ]
            ],
            $context
        );
    }

    public function markSynchronizationAsInProgress(StylaPagesSynchronization $synchronization, Context $context)
    {
        $this->synchronizationsRepository->update(
            [
                [
                    'id' => $synchronization->getId(),
                    'startedAt' => new \DateTime('now', new \DateTimeZone('UTC')),
                    'status' => StylaPagesSynchronization::STATUS_IN_PROGRESS
                ]
            ],
            $context
        );
    }

    public function markSynchronizationAsStuck(StylaPagesSynchronization $synchronization, Context $context)
    {
        $this->synchronizationsRepository->update(
            [
                [
                    'id' => $synchronization->getId(),
                    'status' => StylaPagesSynchronization::STATUS_STUCK,
                    'finishedAt' => new \DateTime('now', new \DateTimeZone('UTC')),
                    'active' => false
                ]
            ],
            $context
        );
    }

    /**
     * Mark synchronization as pending and log error if something unexpected happened
     *
     * @param $createdSynchronizationId
     * @param Context $context
     */
    public function tryMarkSynchronizationAsPending($createdSynchronizationId, Context $context): void
    {
        try {
            // Fixed race condition
            // If it's already set as success before even setting it to pending
            $criteria = new Criteria();
            $criteria->setLimit(1);
            $criteria->addFilter(new EqualsFilter('id', $createdSynchronizationId));
            $existingSync = $this->synchronizationsRepository->search($criteria, $context)
                ->getEntities()
                ->first();
            if ($existingSync && $existingSync->getStatus() === StylaPagesSynchronization::STATUS_SUCCESS) {
                $this->logger->info(
                    sprintf('Synchronization %s status is already SUCCESS', $createdSynchronizationId)
                );
            } else {
                $this->synchronizationsRepository->update(
                    [
                        [
                            'id' => $createdSynchronizationId,
                            'status' => StylaPagesSynchronization::STATUS_PENDING,
                        ]
                    ],
                    $context
                );
            }
        } catch (\Throwable $throwable) {
            $this->logger->error(
                sprintf('Failed to change synchronization %s status to PENDING', $createdSynchronizationId),
                [
                    'exception' => $throwable
                ]
            );
        }
    }

    public function getNotFinishedSynchronization(Context $context): ?StylaPagesSynchronization
    {
        $criteria = new Criteria();
        $criteria->setLimit(1);

        $criteria->addFilter(new EqualsFilter('active', true));

        $synchronizations = $this->synchronizationsRepository->search($criteria, $context);

        return $synchronizations->first();
    }

    public function getSynchronizationById(string $synchronizationId, Context $context): ?StylaPagesSynchronization
    {
        try {
            return $this->synchronizationsRepository
                ->search(new Criteria([$synchronizationId]), $context)
                ->first();
        } catch (\Throwable $exception) {
            $this->logger->error(
                sprintf('Exception happened during synchronization fetch. Message: %s', $exception->getMessage()),
                [
                    'synchronizationId' => $synchronizationId,
                    'exception' => $exception,
                    'context' => $context
                ]
            );

            return null;
        }
    }

    /**
     * @param int $intervalInMinutes
     * @param Context $context
     *
     * @return bool
     * @throws \Exception
     */
    public function hasFinishedSynchronizationInInterval(int $intervalInMinutes, Context $context): bool
    {
        $comparisonDate = new \DateTime('now', new \DateTimeZone('UTC'));
        $comparisonDate->sub(new \DateInterval(sprintf('PT%sM', $intervalInMinutes)));

        $criteria = new Criteria();
        $criteria->addFilter(
            new AndFilter(
                [
                    new NotFilter(
                        NotFilter::CONNECTION_AND,
                        [
                            new EqualsFilter('status', StylaPagesSynchronization::STATUS_STUCK)
                        ]
                    ),
                    new RangeFilter(
                        'finishedAt',
                        [
                            // String used because range filter does not convert \DateTime
                            RangeFilter::GT => $comparisonDate->format(Defaults::STORAGE_DATE_TIME_FORMAT)
                        ]
                    ),
                ]
            )
        );
        $criteria->setLimit(1);
        $result = $this->synchronizationsRepository->searchIds($criteria, $context);

        return $result->getTotal() !== 0;
    }

    /**
     * @param Context $context
     *
     * @return int
     * @throws InvalidAggregationQueryException
     */
    public function getSynchronizationsCount(Context $context): int
    {
        $criteria = new Criteria();
        $criteria->addAggregation(new CountAggregation('count', 'id'));

        $result = $this->synchronizationsRepository->aggregate($criteria, $context)->get('count');
        if ($result === null) {
            throw new InvalidAggregationQueryException('Could not aggregate styla pages synchronization count');
        }

        return $result->getCount();
    }

    /**
     * Function should left at list one success synchronization for the historical purposes
     *
     * @param int $quantityOfRecordsThatMustBeLeft
     * @param Context $context
     * @throws \Throwable
     */
    public function removeOldRecords(int $quantityOfRecordsThatMustBeLeft, Context $context)
    {
        $newestSuccessSynchronizationCriteria = new Criteria();
        $newestSuccessSynchronizationCriteria->addSorting(new FieldSorting('finishedAt', 'DESC'));
        $newestSuccessSynchronizationCriteria->setLimit(1);
        $newestSuccessSynchronizationCriteria->addFilter(
            new EqualsFilter('status', StylaPagesSynchronization::STATUS_SUCCESS)
        );
        $newestSuccessSynchronizationId = $this->synchronizationsRepository
            ->searchIds($newestSuccessSynchronizationCriteria, $context)
            ->firstId();

        $entitiesToPreserveIdsFetchCriteria = new Criteria();
        $entitiesToPreserveIdsFetchCriteria->addSorting(new FieldSorting('finishedAt', 'DESC'));
        $entitiesToPreserveIdsFetchCriteria->setLimit($quantityOfRecordsThatMustBeLeft);
        $entitiesToPreserveIds = $this->synchronizationsRepository
            ->searchIds($entitiesToPreserveIdsFetchCriteria, $context)->getIds();
        if ($newestSuccessSynchronizationId) {
            $entitiesToPreserveIds[] = $newestSuccessSynchronizationId;
        }

        $entitiesToRemoveCriteria = new Criteria();
        if ($entitiesToPreserveIds) {
            $entitiesToRemoveCriteria->addFilter(
                new NandFilter(
                    [
                        new EqualsAnyFilter(
                            'id',
                            $entitiesToPreserveIds
                        )
                    ]
                )
            );
        }

        $entitiesToRemoveIds = $this->synchronizationsRepository->searchIds($entitiesToRemoveCriteria, $context);

        $deleteDataSet = array_map(function ($id) {
            return ['id' => $id];
        }, $entitiesToRemoveIds->getIds());
        $this->synchronizationsRepository->delete($deleteDataSet, $context);
    }
}
