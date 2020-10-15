<?php

namespace Elements\Bundle\ProcessManagerBundle\Migrations;

use Elements\Bundle\ProcessManagerBundle\ElementsProcessManagerBundle;
use Doctrine\DBAL\Schema\Schema;
use Pimcore\Migrations\Migration\AbstractPimcoreMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version00000018 extends AbstractPimcoreMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {

        $this->addSql("ALTER TABLE " . ElementsProcessManagerBundle::TABLE_NAME_CONFIGURATION . " MODIFY `cronJob` TEXT;");
        \Pimcore\Cache::clearTags(['system', 'resource']);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
    }
}
