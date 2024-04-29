<?php declare(strict_types=1);

namespace Styla\CmsIntegration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;

class StylaCmsIntegration extends Plugin
{
    public function deactivate(DeactivateContext $deactivateContext): void
    {
        $this->removeUsedStylaBlocksAndElementsFromThePages();
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        if ($uninstallContext->keepUserData()) {
            return;
        }

        /** @var Connection $connection */
        $connection = $this->container->get(Connection::class);

        $dropPagesTableSQL = 'DROP TABLE IF EXISTS `styla_cms_page`';
        $connection->executeStatement($dropPagesTableSQL);

        $dropPagesSynchronizationTableSQL = 'DROP TABLE IF EXISTS `styla_cms_pages_synchronization`';
        $connection->executeStatement($dropPagesSynchronizationTableSQL);
    }

    private function removeUsedStylaBlocksAndElementsFromThePages()
    {
        /** @var Connection $connection */
        $connection = $this->container->get(Connection::class);

        $removeStylaBlocks = 'DELETE FROM `cms_block` where type=:stylaBlockType';
        $connection->executeStatement($removeStylaBlocks, ['stylaBlockType' => 'styla-module-content']);

        $removeStylaElements = 'DELETE FROM `cms_slot` where type=:stylaBlockType';
        $connection->executeStatement($removeStylaElements, ['stylaBlockType' => 'styla-module-content']);
    }
}
