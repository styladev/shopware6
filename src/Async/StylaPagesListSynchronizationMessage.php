<?php

namespace Styla\CmsIntegration\Async;

use Shopware\Core\Framework\Context;

class StylaPagesListSynchronizationMessage
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
