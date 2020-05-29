<?php

namespace Elements\Bundle\ProcessManagerBundle\Migrations;

use Elements\Bundle\ProcessManagerBundle\ElementsProcessManagerBundle;
use Doctrine\DBAL\Schema\Schema;
use Pimcore\Migrations\Migration\AbstractPimcoreMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version00000014 extends AbstractPimcoreMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $table = $schema->getTable(ElementsProcessManagerBundle::TABLE_NAME_MONITORING_ITEM);
        if ($table->hasColumn('parentId') == false) {
            $this->addSql("ALTER TABLE " . ElementsProcessManagerBundle::TABLE_NAME_MONITORING_ITEM . " ADD COLUMN `parentId` INT NULL DEFAULT NULL AFTER `id`;");
        }

        \Pimcore\Cache::clearTags(['system', 'resource']);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->writeMessage("No automatic downgrading due to possible data loss.");
    }
}
