<?php

namespace Styla\CmsIntegration\Test\DataFixtures;

use Psr\Container\ContainerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;

class LoadCmsSectionData extends AbstractTestDataFixture
{
    private array $data = [
        'styla_cms_integration.page.foo.section.foo' => [
            'type' => 'default',
            'position' => 0,
            'pageId' => 'styla_cms_integration.page.foo'
        ],
        'styla_cms_integration.page.bar.section.foo' => [
            'type' => 'default',
            'position' => 0,
            'pageId' => 'styla_cms_integration.page.bar'
        ],
        'styla_cms_integration.page.qux.section.foo' => [
            'type' => 'default',
            'position' => 0,
            'pageId' => 'styla_cms_integration.page.qux'
        ],
        'styla_cms_integration.page.quux.section.foo' => [
            'type' => 'default',
            'position' => 0,
            'pageId' => 'styla_cms_integration.page.quux'
        ],
        'styla_cms_integration.page.quuz.section.foo' => [
            'type' => 'default',
            'position' => 0,
            'pageId' => 'styla_cms_integration.page.quuz'
        ],
        'styla_cms_integration.page.corge.section.foo' => [
            'type' => 'default',
            'position' => 0,
            'pageId' => 'styla_cms_integration.page.corge'
        ],
        'styla_cms_integration.landing_page.foo.section.foo' => [
            'type' => 'default',
            'position' => 0,
            'pageId' => 'styla_cms_integration.page.baz'
        ]
    ];

    public function execute(ContainerInterface $container, ReferencesRegistry $referencesRegistry)
    {
        /** @var EntityRepository $cmsPageRepository */
        $cmsPageRepository = $container->get('cms_section.repository');

        foreach ($this->data as $reference => $record) {
            $this->resolveReferencesAsIdsIfExists($referencesRegistry, $record, ['pageId']);

            $entity = $this->createEntity($cmsPageRepository, $record);
            $referencesRegistry->setReference($reference, $entity);
        }
    }

    public function getDependenciesList(): array
    {
        return [new LoadCmsPageData()];
    }
}
