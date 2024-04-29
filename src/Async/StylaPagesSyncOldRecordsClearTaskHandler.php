<?php

namespace Styla\CmsIntegration\Async;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Styla\CmsIntegration\Entity\StylaIntegration\StylaSynchronizationDalHelper;

class StylaPagesSyncOldRecordsClearTaskHandler extends ScheduledTaskHandler
{
    private const START_CLEAR_AFTER_SYNCHRONIZATIONS_QUANTITY_REACH = 30000;
    private const QUANTITY_OF_NEWEST_RECORDS_LEFT_AFTER_CLEAR = 2000;

    private StylaSynchronizationDalHelper $stylaSynchronizationDalHelper;
    private LoggerInterface $logger;

    public function __construct(
        EntityRepository $scheduledTaskRepository,
        StylaSynchronizationDalHelper $stylaSynchronizationDalHelper,
        LoggerInterface $logger
    ) {
        parent::__construct($scheduledTaskRepository);
        $this->stylaSynchronizationDalHelper = $stylaSynchronizationDalHelper;
        $this->logger = $logger;
    }

    public static function getHandledMessages(): iterable
    {
        return [StylaPagesSyncOldRecordsClearTask::class];
    }

    public function run(): void
    {
        try {
            $context = Context::createDefaultContext();
            $count = $this->stylaSynchronizationDalHelper->getSynchronizationsCount($context);
            if ($count > self::START_CLEAR_AFTER_SYNCHRONIZATIONS_QUANTITY_REACH) {
                $this->logger->warning(
                    sprintf(
                        'Synchronization entities quantity exceed maximum allowed amount (%s), ' .
                        'attempting to remove outdated records',
                        self::START_CLEAR_AFTER_SYNCHRONIZATIONS_QUANTITY_REACH
                    ),
                );
                $this->stylaSynchronizationDalHelper->removeOldRecords(
                    self::QUANTITY_OF_NEWEST_RECORDS_LEFT_AFTER_CLEAR,
                    $context
                );
            }
        } catch (\Throwable $throwable) {
            $this->logger->error(
                sprintf('Synchronization clear failed, reason: %s', $throwable->getMessage()),
                [
                    'exception' => $throwable
                ]
            );
        }
    }
}
