<?php

namespace Styla\CmsIntegration\Async;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class StylaPagesListSyncScheduledTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'styla_cms_integration_pages_list_synchronization';
    }

    /**
     * Should be the same as the value in the default configuration
     *
     * @return int
     */
    public static function getDefaultInterval(): int
    {
        return 60;
    }
}
