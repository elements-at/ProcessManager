<?php

namespace Elements\Bundle\ProcessManagerBundle\Migrations;

use Elements\Bundle\ProcessManagerBundle\ElementsProcessManagerBundle;
use Doctrine\DBAL\Schema\Schema;
use Pimcore\Migrations\Migration\AbstractPimcoreMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version00000015 extends AbstractPimcoreMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $table = $schema->getTable(ElementsProcessManagerBundle::TABLE_NAME_MONITORING_ITEM);
        if ($table->hasColumn('deleteAfterHours') == false) {
            $this->addSql("ALTER TABLE " . ElementsProcessManagerBundle::TABLE_NAME_MONITORING_ITEM . " add deleteAfterHours INT UNSIGNED default null");
            $this->addSql("ALTER TABLE " . ElementsProcessManagerBundle::TABLE_NAME_MONITORING_ITEM . " ADD INDEX `deleteAfterHours` (`deleteAfterHours`)");
        }

        \Pimcore\Cache::clearTags(['system', 'resource']);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $table = $schema->getTable(ElementsProcessManagerBundle::TABLE_NAME_MONITORING_ITEM);
        $table->dropColumn('deleteAfterHours');
    }
}
