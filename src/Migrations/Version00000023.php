<?php

namespace Elements\Bundle\ProcessManagerBundle\Migrations;

use Elements\Bundle\ProcessManagerBundle\ElementsProcessManagerBundle;
use Doctrine\DBAL\Schema\Schema;
use Pimcore\Migrations\Migration\AbstractPimcoreMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version00000023 extends AbstractPimcoreMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE '. ElementsProcessManagerBundle::TABLE_NAME_CONFIGURATION.' MODIFY COLUMN `id` varchar(190)');
        $this->addSql('ALTER TABLE '. ElementsProcessManagerBundle::TABLE_NAME_CONFIGURATION.' ADD UNIQUE INDEX `id` (`id`)');
        $this->addSql('ALTER TABLE '. ElementsProcessManagerBundle::TABLE_NAME_CONFIGURATION.' DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE '. ElementsProcessManagerBundle::TABLE_NAME_CONFIGURATION.' ADD PRIMARY KEY (`id`)');
        $this->addSql('ALTER TABLE '. ElementsProcessManagerBundle::TABLE_NAME_MONITORING_ITEM.' MODIFY COLUMN `configurationId` varchar(190)');
        $this->addSql('ALTER TABLE '. ElementsProcessManagerBundle::TABLE_NAME_CONFIGURATION.' DROP KEY `name`');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {

    }
}
