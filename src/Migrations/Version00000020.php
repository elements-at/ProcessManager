<?php

namespace Elements\Bundle\ProcessManagerBundle\Migrations;

use Elements\Bundle\ProcessManagerBundle\ElementsProcessManagerBundle;
use Doctrine\DBAL\Schema\Schema;
use Pimcore\Migrations\Migration\AbstractPimcoreMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version00000020 extends AbstractPimcoreMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql("UPDATE " . ElementsProcessManagerBundle::TABLE_NAME_MONITORING_ITEM . " SET published=0 where executedByUser > 0");
        \Elements\Bundle\ProcessManagerBundle\Installer::updateTranslations(true);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
    }
}
