<?php

namespace Styla\CmsIntegration\Test\DataFixtures;

use Psr\Container\ContainerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;

class LoadLandingPageData extends AbstractTestDataFixture
{
    private array $data = [
        'styla_cms_integration.landing_page.foo' => [
            'active' => true,
            'cmsPageId' => 'styla_cms_integration.page.baz',
            'name' => 'Foo landing page',
            'url' => 'landing-page-foo',
            'salesChannels' => [
                'styla_cms_integration.sales_channel.storefront'
            ],
        ]
    ];

    public function execute(ContainerInterface $container, ReferencesRegistry $referencesRegistry)
    {
        /** @var EntityRepository $landingPagesRepository */
        $landingPagesRepository = $container->get('landing_page.repository');
        foreach ($this->data as $reference => $record) {
            $this->resolveReferencesAsIdsIfExists($referencesRegistry, $record, ['cmsPageId', 'salesChannels']);
            $entity = $this->createEntity($landingPagesRepository, $record);
            $referencesRegistry->setReference($reference, $entity);
        }
    }

    public function getDependenciesList(): array
    {
        return [new RegisterDefaultSalesChannel()];
    }
}
