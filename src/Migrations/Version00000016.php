<?php

namespace Elements\Bundle\ProcessManagerBundle\Migrations;

use Elements\Bundle\ProcessManagerBundle\ElementsProcessManagerBundle;
use Doctrine\DBAL\Schema\Schema;
use Pimcore\Migrations\Migration\AbstractPimcoreMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version00000016 extends AbstractPimcoreMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        \Pimcore\Model\User\Permission\Definition::create('plugin_pm_permission_delete_monitoring_item');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
    }
}
