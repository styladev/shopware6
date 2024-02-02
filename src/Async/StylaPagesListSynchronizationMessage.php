<?php

namespace Styla\CmsIntegration\Async;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\MessageQueue\AsyncMessageInterface;

class StylaPagesListSynchronizationMessage implements AsyncMessageInterface
{
    private string $stylaSynchronizationId;
    private Context $context;

    public function __construct(string $stylaSynchronizationId, Context $context)
    {
        $this->stylaSynchronizationId = $stylaSynchronizationId;
        $this->context = $context;
    }

    public function getStylaSynchronizationId(): string
    {
        return $this->stylaSynchronizationId;
    }

    public function getContext(): Context
    {
        return $this->context;
    }
}
