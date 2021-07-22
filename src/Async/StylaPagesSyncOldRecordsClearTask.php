<?php

namespace Styla\CmsIntegration\Async;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class StylaPagesSyncOldRecordsClearTask extends ScheduledTask
{
    private const EXECUTION_INTERVAL = '86400';

    public static function getTaskName(): string
    {
        return 'styla_cms_integration_synchronization_old_records_clear';
    }

    /**
     * Should be the same as the value in the default configuration
     *
     * @return int
     */
    public static function getDefaultInterval(): int
    {
        return self::EXECUTION_INTERVAL;
    }
}
