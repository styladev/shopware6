<?php declare(strict_types=1);

namespace Styla\CmsIntegration\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1624022140 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1624022140;
    }

    public function update(Connection $connection): void
    {
        $this->addIndexesToStylaCmsPageTable($connection);
        $this->addIndexesToStylaPageSynchronizationPageTable($connection);
    }

    private function addIndexesToStylaCmsPageTable(Connection $connection)
    {
        $sql = <<<SQL
        ALTER TABLE `styla_cms_page`
            ADD INDEX `styla_cms_page_name_idx` (`name` ASC),
            ADD INDEX `styla_cms_page_path_idx` (`path` ASC),
            ADD INDEX `styla_cms_page_acc_name_idx` (`account_name` ASC),
            ADD INDEX `styla_cms_page_created_at_idx` (`created_at` ASC),
            ADD INDEX `styla_cms_page_updated_at_idx` (`updated_at` ASC),
            ADD INDEX `styla_cms_page_path_acc_name_idx` (`path` ASC, `account_name` ASC);
SQL;
        $connection->executeStatement($sql);
    }

    private function addIndexesToStylaPageSynchronizationPageTable(Connection $connection)
    {
        $sql = <<<SQL
            ALTER TABLE `styla_cms_pages_synchronization`
                ADD INDEX `styla_cms_page_sync_name_idx` (`active` ASC),
                ADD INDEX `styla_cms_page_sync_status_fin_dt_idx` (`status` ASC, `finished_at` ASC),
                ADD INDEX `styla_cms_page_sync_fin_dt_idx` (`finished_at` ASC),
                ADD INDEX `styla_cms_page_sync_status_idx` (`status` ASC);
SQL;
        $connection->executeStatement($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
