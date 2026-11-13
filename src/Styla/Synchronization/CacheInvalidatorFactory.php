<?php

namespace Styla\CmsIntegration\Styla\Synchronization;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Adapter\Cache\CacheInvalidator as ShopwareCacheInvalidator;
use Shopware\Core\Framework\Context;
use Styla\CmsIntegration\Styla\Page\PageCacheInteractor;

class CacheInvalidatorFactory
{
    private PageCacheInteractor $pageCacheInteractor;
    private ShopwareCacheInvalidator $shopwareCacheInvalidator;
    private LoggerInterface $logger;

    public function __construct(
        PageCacheInteractor $pageCacheInteractor,
        ShopwareCacheInvalidator $shopwareCacheInvalidator,
        LoggerInterface $logger
    ) {
        $this->pageCacheInteractor = $pageCacheInteractor;
        $this->shopwareCacheInvalidator = $shopwareCacheInvalidator;
        $this->logger = $logger;
    }

    public function create(Context $context)
    {
        return new CacheInvalidator($this->pageCacheInteractor, $this->shopwareCacheInvalidator, $this->logger);
    }
}
