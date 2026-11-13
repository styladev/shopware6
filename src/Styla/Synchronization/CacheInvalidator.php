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
 * - the Shopware HTTP cache entries of the rendered Styla pages (tag per page, see self::httpCacheTag()) and of
 *   the Shopware pages cached under their paths (tag per path, see self::httpCachePathTag()).
 */
class CacheInvalidator
{
    public const HTTP_CACHE_TAG_PREFIX = 'styla-page-';
    public const HTTP_CACHE_PATH_TAG_PREFIX = 'styla-path-';

    private PageCacheInteractor $pageCacheInteractor;
    private ShopwareCacheInvalidator $shopwareCacheInvalidator;
    private LoggerInterface $logger;
    /** @var array<string, string> */
    private array $pageIdsToDetailsCacheInvalidateList;
    /** @var array<string, string> */
    private array $pageIdsToHttpCacheInvalidateList;
    /** @var array<string, string> */
    private array $pathsToHttpCacheInvalidateList;

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

    /**
     * Tag for every storefront response by its path (see the request subscriber), so a Styla page that is new
     * or moved to a path can invalidate the Shopware page cached there before. The trailing slash is ignored,
     * like in the Styla page matching.
     */
    public static function httpCachePathTag(string $path): string
    {
        return self::HTTP_CACHE_PATH_TAG_PREFIX . md5(rtrim($path, '/'));
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
        if ($id) {
            $this->pageIdsToDetailsCacheInvalidateList[$id] = $id;
        }
        $this->addPageForHttpCacheInvalidation($page);
    }

    public function addPageForHttpCacheInvalidation(StylaPage $page)
    {
        // The Shopware page cached under this path (e.g. a category the new Styla page replaces)
        $path = $page->getPath();
        if ($path !== null) {
            $this->pathsToHttpCacheInvalidateList[$path] = $path;
        }

        // A page that is new in this synchronization has no id yet, so no Styla response is tagged with it
        $id = $page->getId();
        if ($id) {
            $this->pageIdsToHttpCacheInvalidateList[$id] = $id;
        }
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
        $tags = array_merge(
            array_map([self::class, 'httpCacheTag'], array_values($this->pageIdsToHttpCacheInvalidateList)),
            array_map([self::class, 'httpCachePathTag'], array_values($this->pathsToHttpCacheInvalidateList))
        );
        if (!$tags) {
            return;
        }

        try {
            $this->shopwareCacheInvalidator->invalidate(array_values(array_unique($tags)));
        } catch (\Throwable $exception) {
            $this->logger->error(
                'Could not invalidate http cache for styla pages',
                [
                    'exception' => $exception,
                    'pageIds' => array_values($this->pageIdsToHttpCacheInvalidateList),
                    'paths' => array_values($this->pathsToHttpCacheInvalidateList),
                ]
            );
        }
    }

    public function clearState()
    {
        $this->pageIdsToDetailsCacheInvalidateList = [];
        $this->pageIdsToHttpCacheInvalidateList = [];
        $this->pathsToHttpCacheInvalidateList = [];
    }
}
