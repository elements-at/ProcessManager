<?php

namespace Elements\Bundle\ProcessManagerBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Elements\Bundle\ProcessManagerBundle\ElementsProcessManagerBundle;
use Pimcore\Migrations\BundleAwareMigration;

class Version20230217000000 extends BundleAwareMigration
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

        $this->addSql(
            'UPDATE bundle_process_manager_configuration SET `restrictToPermissions` = "" WHERE `restrictToPermissions` IS null'
        );
        $this->addSql(
            'ALTER TABLE `bundle_process_manager_configuration` MODIFY `restrictToPermissions`  MEDIUMTEXT NOT NULL DEFAULT ""'
        );
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
