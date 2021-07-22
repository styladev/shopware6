<?php declare(strict_types=1);

namespace Styla\CmsIntegration\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1624621739 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1624621739;
    }

    public function update(Connection $connection): void
    {
    }

    public function updateDestructive(Connection $connection): void
    {
        $sql = <<<SQL
        ALTER TABLE `styla_cms_page` DROP COLUMN active;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
        ALTER TABLE `styla_cms_pages_synchronization`
            RENAME INDEX `styla_cms_page_sync_name_idx` TO `styla_cms_page_sync_active_idx`;
SQL;
        $connection->executeStatement($sql);
    }
}
