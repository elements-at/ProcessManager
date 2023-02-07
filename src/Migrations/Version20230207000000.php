<?php

namespace Elements\Bundle\ProcessManagerBundle\Migrations;

use Elements\Bundle\ProcessManagerBundle\ElementsProcessManagerBundle;
use Doctrine\DBAL\Schema\Schema;
use Pimcore\Migrations\BundleAwareMigration;

class Version20230207000000 extends BundleAwareMigration
{
    protected function getBundleName(): string
    {
        return ElementsProcessManagerBundle::BUNDLE_NAME;
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        $configurationTable = $schema->getTable('bundle_process_manager_configuration');

        if (!$configurationTable->hasColumn('restrictToPermissions')) {
            $this->addSql(
                'ALTER TABLE `bundle_process_manager_configuration` ADD `restrictToPermissions` MEDIUMTEXT DEFAULT ""'
            );
            \Pimcore\Cache::clearTags(['system', 'resource']);
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
