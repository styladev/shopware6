<?php

namespace Styla\CmsIntegration\Styla\Synchronization;

use Psr\Log\LoggerInterface;
use Styla\CmsIntegration\Entity\StylaPage\StylaPage;
use Styla\CmsIntegration\Entity\StylaPage\StylaPageCollection;
use Styla\CmsIntegration\Styla\Page\PageCacheInteractor;
use Symfony\Component\HttpKernel\HttpCache\StoreInterface;

class CacheInvalidator
{
    private PageCacheInteractor $pageCacheInteractor;
    private StoreInterface $httpCacheStore;
    private LoggerInterface $logger;
    private array $domainsList;

    private array $pagePathToCacheInvalidateList;
    private array $pageIdsToCacheInvalidateList;

    public function __construct(
        PageCacheInteractor $pageCacheInteractor,
        StoreInterface $httpCacheStore,
        LoggerInterface $logger,
        array $domainsList
    ) {
        $this->pageCacheInteractor = $pageCacheInteractor;
        $this->httpCacheStore = $httpCacheStore;
        $this->logger = $logger;
        $this->domainsList = $domainsList;

        $this->clearState();
    }

    public function addPagesForCacheInvalidation(StylaPageCollection $collection)
    {
        foreach ($collection as $page) {
            $this->addPageForCacheInvalidation($page);
        }
    }

    public function addPageForCacheInvalidation(StylaPage $page)
    {
        $this->addPageForDetailsCacheInvalidation($page);
        $this->addPageForHttpCacheInvalidation($page);
    }

    private function addPageForDetailsCacheInvalidation(StylaPage $page)
    {
        $id = $page->getId();
        $this->pageIdsToCacheInvalidateList[$id] = $id;
    }

    public function addPageForHttpCacheInvalidation(StylaPage $page)
    {
        $path = $page->getPath();
        $this->pagePathToCacheInvalidateList[$path] = $path;
    }

    public function invalidateCaches()
    {
        if ($this->pageIdsToCacheInvalidateList) {
            $this->pageCacheInteractor->removeCacheEntryByPageIds($this->pageIdsToCacheInvalidateList);
        }
        $this->invalidateHttpCacheByPagePaths();
        $this->clearState();
    }

    private function invalidateHttpCacheByPagePaths()
    {
        try {
            foreach ($this->domainsList as $domain) {
                foreach ($this->pagePathToCacheInvalidateList as $pagePath) {
                    $pageUri = sprintf(
                        'http://%s/%s',
                        rtrim($domain, '/\\'),
                        ltrim($pagePath, '/\\')
                    );
                    $this->httpCacheStore->purge($pageUri);
                }
            }
        } catch (\Throwable $exception) {
            $this->logger->error(
                'Could not clear http cache for pages',
                [
                    'exception' => $exception,
                    'pages' => $this->pagePathToCacheInvalidateList,
                    'domains' => $this->domainsList
                ]
            );
        }
    }

    public function clearState()
    {
        $this->pageIdsToCacheInvalidateList = [];
        $this->pagePathToCacheInvalidateList = [];
    }
}
