<?php

namespace Elements\Bundle\ProcessManagerBundle\Migrations;

use Elements\Bundle\ProcessManagerBundle\ElementsProcessManagerBundle;
use Doctrine\DBAL\Schema\Schema;
use Pimcore\Migrations\Migration\AbstractPimcoreMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version00000006 extends AbstractPimcoreMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $table = $schema->getTable(ElementsProcessManagerBundle::TABLE_NAME_CONFIGURATION);
        if (!$table->hasIndex("name")) {
            $this->addSql("ALTER TABLE " . ElementsProcessManagerBundle::TABLE_NAME_CONFIGURATION . " ADD UNIQUE INDEX `name` (`name`)");
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
    }
}
