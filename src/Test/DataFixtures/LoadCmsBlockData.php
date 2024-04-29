<?php

namespace Styla\CmsIntegration\Test\DataFixtures;

use Psr\Container\ContainerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;

class LoadCmsBlockData extends AbstractTestDataFixture
{
    private array $data = [
        'styla_cms_integration.page.foo.block.foo' => [
            'type' => 'styla-module-content',
            'position' => 0,
            'backgroundMediaMode' => 'cover',
            'sectionId' => 'styla_cms_integration.page.foo.section.foo'
        ],
        'styla_cms_integration.page.bar.block.foo' => [
            'type' => 'styla-module-content',
            'position' => 0,
            'backgroundMediaMode' => 'cover',
            'sectionId' => 'styla_cms_integration.page.bar.section.foo'
        ],
        'styla_cms_integration.page.qux.block.foo' => [
            'type' => 'styla-module-content',
            'position' => 0,
            'backgroundMediaMode' => 'cover',
            'sectionId' => 'styla_cms_integration.page.qux.section.foo'
        ],
        'styla_cms_integration.page.quux.block.foo' => [
            'type' => 'styla-module-content',
            'position' => 0,
            'backgroundMediaMode' => 'cover',
            'sectionId' => 'styla_cms_integration.page.quux.section.foo'
        ],
        'styla_cms_integration.page.quuz.block.foo' => [
            'type' => 'styla-module-content',
            'position' => 0,
            'backgroundMediaMode' => 'cover',
            'sectionId' => 'styla_cms_integration.page.quuz.section.foo'
        ],
        'styla_cms_integration.page.corge.block.foo' => [
            'type' => 'text',
            'position' => 0,
            'backgroundMediaMode' => 'cover',
            'sectionId' => 'styla_cms_integration.page.corge.section.foo'
        ],
        'styla_cms_integration.landing_page.foo.block.foo' => [
            'type' => 'styla-module-content',
            'position' => 0,
            'backgroundMediaMode' => 'cover',
            'sectionId' => 'styla_cms_integration.landing_page.foo.section.foo'
        ],
    ];

    public function execute(ContainerInterface $container, ReferencesRegistry $referencesRegistry)
    {
        /** @var EntityRepository $cmsBlockRepository */
        $cmsBlockRepository = $container->get('cms_block.repository');

        foreach ($this->data as $reference => $record) {
            $this->resolveReferencesAsIdsIfExists($referencesRegistry, $record, ['sectionId']);
            $entity = $this->createEntity($cmsBlockRepository, $record);
            $referencesRegistry->setReference($reference, $entity);
        }
    }

    public function getDependenciesList(): array
    {
        return [new LoadCmsSectionData()];
    }
}
