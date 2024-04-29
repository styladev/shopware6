<?php

namespace Styla\CmsIntegration\Async;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Styla\CmsIntegration\UseCase\StylaPagesSynchronizer;

class StylaPagesListSyncScheduledTaskHandler extends ScheduledTaskHandler
{
    private StylaPagesSynchronizer $stylaPagesSynchronizer;
    private LoggerInterface $logger;

    public function __construct(
        EntityRepository $scheduledTaskRepository,
        StylaPagesSynchronizer $stylaPagesSynchronizer,
         LoggerInterface $logger
    ) {
        parent::__construct($scheduledTaskRepository);
        $this->stylaPagesSynchronizer = $stylaPagesSynchronizer;
        $this->logger = $logger;
    }

    public static function getHandledMessages(): iterable
    {
        return [StylaPagesListSyncScheduledTask::class];
    }

    public function run(): void
    {
        try {
            /**
             * Shopware scheduled tasks interval was not used because:
             * - they are not working properly on all environment
             * (continuously run independent from next schedule date because of the bug:
             * \Shopware\Core\Framework\MessageQueue\ScheduledTask\Scheduler\TaskScheduler::buildCriteriaForAllScheduledTask,
             * \DATE_ATOM fate time format is used in the filter instead of \Shopware\Core\Defaults::STORAGE_DATE_TIME_FORMAT
             * )
             * at least in MySQL 8.0.25 Community Edition
             * - in the future there might possibly be implemented advanced scheduling logic like: different schedule
             * for the different sales channels or things related to breakdown synchronization into smaller parts
             */
            $this->stylaPagesSynchronizer->scheduleAutomaticPagesSynchronizationIfNeeded(Context::createDefaultContext());
        } catch (\Throwable $throwable) {
            $this->logger->error(
                sprintf('Synchronization schedule failed, reason: %s', $throwable->getMessage()),
                [
                    'exception' => $throwable
                ]
            );
        }
    }
}
