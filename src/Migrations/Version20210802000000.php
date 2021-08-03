<?php

namespace Elements\Bundle\ProcessManagerBundle\Migrations;

use Elements\Bundle\ProcessManagerBundle\ElementsProcessManagerBundle;
use Doctrine\DBAL\Schema\Schema;
use Pimcore\Migrations\BundleAwareMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20210802000000 extends BundleAwareMigration
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
        $this->addSql('ALTER TABLE `bundle_process_manager_configuration` MODIFY COLUMN `id` varchar(190)');
        $this->addSql('ALTER TABLE `bundle_process_manager_configuration` ADD UNIQUE INDEX `id` (`id`)');
        $this->addSql('ALTER TABLE `bundle_process_manager_configuration` DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE `bundle_process_manager_configuration` ADD PRIMARY KEY (`id`)');
        $this->addSql('ALTER TABLE `bundle_process_manager_monitoring_item` MODIFY COLUMN `configurationId` varchar(190)');
        $this->addSql('ALTER TABLE `bundle_process_manager_configuration` DROP KEY `name`');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {

    }
}
