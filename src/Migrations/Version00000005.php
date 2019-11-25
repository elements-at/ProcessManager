<?php

namespace Elements\Bundle\ProcessManagerBundle\Migrations;

use Elements\Bundle\ProcessManagerBundle\ElementsProcessManagerBundle;
use Doctrine\DBAL\Schema\Schema;
use Pimcore\Migrations\Migration\AbstractPimcoreMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version00000005 extends AbstractPimcoreMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {   
        $table = $schema->getTable(ElementsProcessManagerBundle::TABLE_NAME_CONFIGURATION);
        if (!$table->hasColumn('restrictToRoles')) {
            $this->addSql("ALTER TABLE " . ElementsProcessManagerBundle::TABLE_NAME_CONFIGURATION . " ADD COLUMN `restrictToRoles` VARCHAR(100) not null default '';");
        }

        \Pimcore\Cache::clearTags(['system', 'resource']);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql("ALTER TABLE " . ElementsProcessManagerBundle::TABLE_NAME_CONFIGURATION . " DROP COLUMN `restrictToRoles`;");
    }
}
