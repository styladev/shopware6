<?php

namespace Styla\CmsIntegration\Styla\Page;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Styla\CmsIntegration\Configuration\ConfigurationInterface;
use Styla\CmsIntegration\Entity\StylaPage\StylaPage;
use Styla\CmsIntegration\Styla\Client\DTO\PageDetails;

class PageCacheInteractor
{
    private const PAGES_CACHE_KEY_PREFIX = 'styla_cms_page_';

    private CacheItemPoolInterface $cache;
    private ConfigurationInterface $configuration;
    private LoggerInterface $logger;

    public function __construct(
        CacheItemPoolInterface $cache,
        ConfigurationInterface $configuration,
        LoggerInterface $logger
    ) {
        $this->cache = $cache;
        $this->configuration = $configuration;
        $this->logger = $logger;
    }

    public function getByPage(StylaPage $stylaPage): ?PageDetails
    {
        try {
            $key = $this->getCacheKey($stylaPage->getId());

            $cacheItem = $this->cache->getItem($key);

            return $cacheItem->isHit() ? $cacheItem->get() : null;
        } catch (\Throwable $exception) {
            $message = 'Could not get styla page item cache DTO';
            $this->logger->error($message, ['exception' => $exception]);

            return null;
        }
    }

    public function save(StylaPage $stylaPage, PageDetails $pageDetails): bool
    {
        try  {
            $key = $this->getCacheKey($stylaPage->getId());
            $cacheItem = $this->cache->getItem($key);

            $cacheItem->set($pageDetails);
            $cacheItem->expiresAfter($this->configuration->getPageDetailsCacheDuration());

            return $this->cache->save($cacheItem);
        } catch (\Throwable $exception) {
            $this->logger->warning('Could not save page details to the cache', ['exception' => $exception]);

            return false;
        }
    }

    public function removeCacheEntryByPageIds(array $pageIds): bool
    {
        try {
            $keys = array_map([$this, 'getCacheKey'], $pageIds);
            return $this->cache->deleteItems($keys);
        } catch (\Throwable $exception) {
            $this->logger->warning('Could not remove page details from the cache', ['exception' => $exception]);

            return false;
        }
    }

    private function getCacheKey(string $pageId)
    {
        return self::PAGES_CACHE_KEY_PREFIX . $pageId;
    }
}
