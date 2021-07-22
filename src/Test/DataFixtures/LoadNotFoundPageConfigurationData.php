<?php

namespace Styla\CmsIntegration\Test\DataFixtures;

use Psr\Container\ContainerInterface;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class LoadNotFoundPageConfigurationData extends AbstractTestDataFixture
{
    public function execute(ContainerInterface $container, ReferencesRegistry $referencesRegistry)
    {
        $pageId = $this->getEntityIdByReference($referencesRegistry, 'styla_cms_integration.page.quux');

        $container->get(SystemConfigService::class)->set('core.basicInformation.http404Page', $pageId);
    }

    public function getDependenciesList(): array
    {
        return [
            new LoadCmsPageWithBlocks()
        ];
    }
}
