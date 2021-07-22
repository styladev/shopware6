<?php declare(strict_types=1);

namespace Styla\CmsIntegration\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1621586343 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1621586343;
    }

    public function update(Connection $connection): void
    {
        $this->createStylaPageTableIfNotExist($connection);
        $this->createStylaPagesSynchronizationTableIfNotExist($connection);
    }

    public function updateDestructive(Connection $connection): void
    {
    }

    private function createStylaPageTableIfNotExist(Connection $connection)
    {
        $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS `styla_cms_page` (
                `id` BINARY(16) NOT NULL,
                `active` TINYINT(1) NULL DEFAULT '0',
                `name` VARCHAR(255) NOT NULL,
                `account_name` VARCHAR(255) NOT NULL,
                `path` VARCHAR(255) NOT NULL,
                `domain` VARCHAR(255) NULL,
                `title` VARCHAR(255) NULL,
                `seo_title` VARCHAR(255) NULL,
                `position` INT(11) NULL,
                `created_at` DATETIME(3) NOT NULL,
                `styla_updated_at` DATETIME(3) NULL,
                `updated_at` DATETIME(3) NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $connection->executeStatement($sql);
    }

    private function createStylaPagesSynchronizationTableIfNotExist(Connection $connection)
    {
        $sql = <<<SQL
        CREATE TABLE IF NOT EXISTS `styla_cms_pages_synchronization` (
            `id` BINARY(16) NOT NULL,
            `active` TINYINT(1) NULL DEFAULT '0',
            `status` VARCHAR(255) NOT NULL,
            `started_at` DATETIME(3) NULL,
            `finished_at` DATETIME(3) NULL,
            `created_at` DATETIME(3) NOT NULL,
            `updated_at` DATETIME(3) NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);
    }
}
