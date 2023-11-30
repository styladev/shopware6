<?php

namespace Styla\CmsIntegration\Test\DataFixtures;

use Psr\Container\ContainerInterface;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class RegisterDefaultSalesChannel implements TestDataFixturesInterface
{
    public function execute(ContainerInterface $container, ReferencesRegistry $referencesRegistry)
    {
        /** @var EntityRepository $salesChannelRepository */
        $salesChannelRepository = $container->get('sales_channel.repository');

        $criteria = new Criteria();

        $criteria->addFilter(new EqualsFilter('typeId', Defaults::SALES_CHANNEL_TYPE_STOREFRONT));

        $salesChannel = $salesChannelRepository->search($criteria, Context::createDefaultContext())
            ->getEntities()
            ->first();

        $referencesRegistry->setReference('styla_cms_integration.sales_channel.storefront', $salesChannel);
    }
}
