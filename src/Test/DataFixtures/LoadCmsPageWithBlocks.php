<?php

namespace Styla\CmsIntegration\Test\DataFixtures;

use Psr\Container\ContainerInterface;

/**
 * Meta fixture that supposed to link all existing fixtures together
 */
class LoadCmsPageWithBlocks extends AbstractTestDataFixture
{
    public function getDependenciesList(): array
    {
        return [
            new LoadCmsPageData(),
            new LoadCmsBlockData(),
            new LoadCmsSectionData(),
            new LoadCmsSlotData()
        ];
    }

    public function execute(ContainerInterface $container, ReferencesRegistry $referencesRegistry)
    {
    }
}
