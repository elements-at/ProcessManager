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
        $db = \Pimcore\Db::get();
        $db->query('DELETE FROM translations_admin WHERE `key` IN ("plugin_pm_permission_view","plugin_pm_permission_configure","plugin_pm_permission_delete_monitoring_item","plugin_pm_permission_execute")');

        \Pimcore\Cache::clearTags(['translator', 'translate']);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
    }
}
