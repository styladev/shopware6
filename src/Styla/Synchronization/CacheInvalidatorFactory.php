<?php

namespace Styla\CmsIntegration\Styla\Synchronization;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainEntity;
use Styla\CmsIntegration\Styla\Page\PageCacheInteractor;
use Symfony\Component\HttpKernel\HttpCache\StoreInterface;

class CacheInvalidatorFactory
{
    private PageCacheInteractor $pageCacheInteractor;
    private StoreInterface $httpCacheStore;
    private LoggerInterface $logger;
    private EntityRepository $domainsRepository;

    public function __construct(
        PageCacheInteractor $pageCacheInteractor,
        StoreInterface $httpCacheStore,
        LoggerInterface $logger,
        EntityRepository $domainRepository
    ) {
        $this->pageCacheInteractor = $pageCacheInteractor;
        $this->httpCacheStore = $httpCacheStore;
        $this->logger = $logger;
        $this->domainsRepository = $domainRepository;
    }

    public function create(Context $context)
    {
        $domainsList = [];

        $criteria = new Criteria();
        $result = $this->domainsRepository->search($criteria, $context);
        /** @var SalesChannelDomainEntity $salesDomain */
        foreach ($result->getElements() as $salesDomain) {
            $domain = $salesDomain->getUrl();
            $domainsList[$domain] = $domain;
        }

        return new CacheInvalidator($this->pageCacheInteractor, $this->httpCacheStore, $this->logger, $domainsList);
    }
}
