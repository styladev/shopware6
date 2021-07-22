<?php

namespace Styla\CmsIntegration\Test\DataFixtures;

use Psr\Container\ContainerInterface;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class LoadMaintenancePageConfigurationData extends AbstractTestDataFixture
{
    public function execute(ContainerInterface $container, ReferencesRegistry $referencesRegistry)
    {
        $pageId = $this->getEntityIdByReference($referencesRegistry, 'styla_cms_integration.page.qux');

        $container->get(SystemConfigService::class)->set('core.basicInformation.maintenancePage', $pageId);
    }

    public function getDependenciesList(): array
    {
        return [
            new LoadCmsPageWithBlocks()
        ];
    }
}
