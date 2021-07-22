<?php

namespace Styla\CmsIntegration\Test\DataFixtures;

use Psr\Container\ContainerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;

class LoadCmsSlotData extends AbstractTestDataFixture
{
    private array $data = [
        'styla_cms_integration.page.foo.block.foo.slot.foo' => [
            'type' => 'styla-module-content',
            'slot' => 'content',
            'config' => [
                'slotId' => [
                    'value' => '0cbce417-436d-4b2f-a70e-ba93c71782d0',
                    'source' => 'static',
                ]
            ],
            'blockId' => 'styla_cms_integration.page.foo.block.foo'
        ],
        'styla_cms_integration.page.bar.block.foo.slot.foo' => [
            'type' => 'styla-module-content',
            'slot' => 'content',
            'config' => [
                'slotId' => [
                    'value' => '1cbce417-436d-4b2f-a70e-ba93c71782d0',
                    'source' => 'static',
                ]
            ],
            'blockId' => 'styla_cms_integration.page.bar.block.foo'
        ],
        'styla_cms_integration.page.qux.block.foo.slot.foo' => [
            'type' => 'styla-module-content',
            'slot' => 'content',
            'config' => [
                'slotId' => [
                    'value' => '3cbce417-436d-4b2f-a70e-ba93c71782d0',
                    'source' => 'static',
                ]
            ],
            'blockId' => 'styla_cms_integration.page.qux.block.foo'
        ],
        'styla_cms_integration.page.corge.block.foo.slot.foo' => [
            'type' => 'styla-module-content',
            'slot' => 'content',
            'config' => [
                'slotId' => [
                    'value' => '3cbce417-436d-4b2f-a70e-ba93c71782d0',
                    'source' => 'static',
                ]
            ],
            'blockId' => 'styla_cms_integration.page.corge.block.foo'
        ],
        'styla_cms_integration.page.quux.block.foo.slot.foo' => [
            'type' => 'styla-module-content',
            'slot' => 'content',
            'config' => [
                'slotId' => [
                    'value' => '4cbce417-436d-4b2f-a70e-ba93c71782d0',
                    'source' => 'static',
                ]
            ],
            'blockId' => 'styla_cms_integration.page.quux.block.foo'
        ],
        'styla_cms_integration.page.quuz.block.foo.slot.foo' => [
            'type' => 'text',
            'slot' => 'content',
            'config' => [
                'content' => [
                    'value' => 'Quuz slot content',
                    'source' => 'static',
                ]
            ],
            'blockId' => 'styla_cms_integration.page.quuz.block.foo'
        ],
        'styla_cms_integration.landing_page.foo.block.foo.slot.foo' => [
            'type' => 'styla-module-content',
            'slot' => 'content',
            'config' => [
                'slotId' => [
                    'value' => '2cbce417-436d-4b2f-a70e-ba93c71782d0',
                    'source' => 'static',
                ]
            ],
            'blockId' => 'styla_cms_integration.landing_page.foo.block.foo'
        ]
    ];

    public function execute(ContainerInterface $container, ReferencesRegistry $referencesRegistry)
    {
        /** @var EntityRepositoryInterface $cmsPageRepository */
        $cmsPageRepository = $container->get('cms_slot.repository');

        foreach ($this->data as $reference => $record) {
            $this->resolveReferencesAsIdsIfExists($referencesRegistry, $record, ['blockId']);

            $entity = $this->createEntity($cmsPageRepository, $record);
            $referencesRegistry->setReference($reference, $entity);
        }
    }

    public function getDependenciesList(): array
    {
        return [new LoadCmsBlockData()];
    }
}
