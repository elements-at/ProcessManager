<?php

namespace Elements\Bundle\ProcessManagerBundle\Migrations;

use Elements\Bundle\ProcessManagerBundle\ElementsProcessManagerBundle;
use Doctrine\DBAL\Schema\Schema;
use Pimcore\Migrations\Migration\AbstractPimcoreMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version00000022 extends AbstractPimcoreMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql(
            'ALTER TABLE '. ElementsProcessManagerBundle::TABLE_NAME_MONITORING_ITEM.'
            CHANGE `currentStep` `currentStep` smallint unsigned NULL,
            CHANGE `totalSteps` `totalSteps` smallint unsigned NULL'
        );
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql(
            'ALTER TABLE ' . ElementsProcessManagerBundle::TABLE_NAME_MONITORING_ITEM . '
            CHANGE `currentStep` `currentStep` tinyint(3) unsigned NULL,
            CHANGE `totalSteps` `totalSteps` tinyint(3) unsigned NULL'
        );
    }
}
