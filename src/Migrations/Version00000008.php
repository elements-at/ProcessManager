<?php

namespace Elements\Bundle\ProcessManagerBundle\Migrations;

use Elements\Bundle\ProcessManagerBundle\ElementsProcessManagerBundle;
use Doctrine\DBAL\Schema\Schema;
use Pimcore\Migrations\Migration\AbstractPimcoreMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version00000008 extends AbstractPimcoreMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql("ALTER TABLE " . ElementsProcessManagerBundle::TABLE_NAME_MONITORING_ITEM . " MODIFY `message` VARCHAR(1000);");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
    }
}
