<?php

namespace Styla\CmsIntegration\UrlProvider;

use Shopware\Core\Content\Sitemap\Provider\AbstractUrlProvider;
use Shopware\Core\Content\Sitemap\Struct\Url;
use Shopware\Core\Content\Sitemap\Struct\UrlResult;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\AndFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Styla\CmsIntegration\Configuration\ConfigurationInterface;
use Styla\CmsIntegration\Entity\StylaPage\StylaPage;

class StylaPagesUrlProvider extends AbstractUrlProvider
{
    private EntityRepositoryInterface $stylaPagesRepository;
    private ConfigurationInterface $configuration;

    public function __construct(EntityRepositoryInterface $stylaPagesRepository, ConfigurationInterface $configuration)
    {
        $this->stylaPagesRepository = $stylaPagesRepository;
        $this->configuration = $configuration;
    }


    public function getDecorated(): AbstractUrlProvider
    {
        throw new DecorationPatternException(self::class);
    }

    public function getName(): string
    {
        return 'styla_cms';
    }

    public function getUrls(SalesChannelContext $context, int $limit, ?int $offset = null): UrlResult
    {
        if ($context->getSalesChannel()->getTypeId() !== Defaults::SALES_CHANNEL_TYPE_STOREFRONT) {
            return new UrlResult([], null);
        }

        $pages = $this->getPages($context);

        $urls = [];
        foreach ($pages as $page) {
            $url = new Url();
            $url->setLoc($page->getPath());
            $url->setLastmod($page->getUpdatedAt() ?? $page->getCreatedAt());
            $url->setChangefreq('daily');
            $url->setResource(StylaPage::class);
            $url->setIdentifier($page->getId());

            $urls[] = $url;
        }

        return new UrlResult($urls, null);
    }

    /**
     * @param SalesChannelContext $context
     *
     * @return array|StylaPage[]
     */
    private function getPages(SalesChannelContext $context): array
    {
        $accountName = $this->configuration->getAccountNameByLanguage($context->getSalesChannel()->getLanguageId());

        $criteria = new Criteria();
        $criteria->addFilter(
            new AndFilter(
                [
                    new EqualsFilter('accountName', $accountName)
                ]
            )
        );

        return $this->stylaPagesRepository->search($criteria, $context->getContext())->getElements();
    }
}
