<?php

namespace Styla\CmsIntegration\Styla\Synchronization;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Adapter\Cache\CacheInvalidator as ShopwareCacheInvalidator;
use Styla\CmsIntegration\Entity\StylaPage\StylaPage;
use Styla\CmsIntegration\Entity\StylaPage\StylaPageCollection;
use Styla\CmsIntegration\Styla\Page\PageCacheInteractor;

/**
 * Collects changed Styla pages during a synchronization and invalidates
 * - the cached Styla page details (object cache) and
 * - the Shopware HTTP cache entries of the rendered Styla pages (cache tag per page, see self::httpCacheTag()).
 */
class CacheInvalidator
{
    public const HTTP_CACHE_TAG_PREFIX = 'styla-page-';

    private PageCacheInteractor $pageCacheInteractor;
    private ShopwareCacheInvalidator $shopwareCacheInvalidator;
    private LoggerInterface $logger;
    /** @var array<string, string> */
    private array $pageIdsToDetailsCacheInvalidateList;
    /** @var array<string, string> */
    private array $pageIdsToHttpCacheInvalidateList;

    public function __construct(
        PageCacheInteractor $pageCacheInteractor,
        ShopwareCacheInvalidator $shopwareCacheInvalidator,
        LoggerInterface $logger
    ) {
        $this->pageCacheInteractor = $pageCacheInteractor;
        $this->shopwareCacheInvalidator = $shopwareCacheInvalidator;
        $this->logger = $logger;
        $this->clearState();
    }

    public static function httpCacheTag(string $pageId): string
    {
        return self::HTTP_CACHE_TAG_PREFIX . $pageId;
    }

    public function addPagesForCacheInvalidation(StylaPageCollection $collection)
    {
        foreach ($collection as $page) {
            $this->addPageForCacheInvalidation($page);
        }
    }

    public function addPageForCacheInvalidation(StylaPage $page)
    {
        $id = $page->getId();
        $this->pageIdsToDetailsCacheInvalidateList[$id] = $id;
        $this->addPageForHttpCacheInvalidation($page);
    }

    public function addPageForHttpCacheInvalidation(StylaPage $page)
    {
        $id = $page->getId();
        $this->pageIdsToHttpCacheInvalidateList[$id] = $id;
    }

    public function invalidateCaches()
    {
        if ($this->pageIdsToDetailsCacheInvalidateList) {
            $this->pageCacheInteractor->removeCacheEntryByPageIds($this->pageIdsToDetailsCacheInvalidateList);
        }
        $this->invalidateHttpCache();
        $this->clearState();
    }

    private function invalidateHttpCache(): void
    {
        if (!$this->pageIdsToHttpCacheInvalidateList) {
            return;
        }

        try {
            $this->shopwareCacheInvalidator->invalidate(
                array_values(array_map([self::class, 'httpCacheTag'], $this->pageIdsToHttpCacheInvalidateList))
            );
        } catch (\Throwable $exception) {
            $this->logger->error(
                'Could not invalidate http cache for styla pages',
                [
                    'exception' => $exception,
                    'pageIds' => array_values($this->pageIdsToHttpCacheInvalidateList),
                ]
            );
        }
    }

    public function clearState()
    {
        $this->pageIdsToDetailsCacheInvalidateList = [];
        $this->pageIdsToHttpCacheInvalidateList = [];
    }
}
