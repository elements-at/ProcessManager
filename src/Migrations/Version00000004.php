<?php

namespace Elements\Bundle\ProcessManagerBundle\Migrations;

use Elements\Bundle\ProcessManagerBundle\ElementsProcessManagerBundle;
use Doctrine\DBAL\Schema\Schema;
use Pimcore\Db;
use Pimcore\Migrations\Migration\AbstractPimcoreMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version00000004 extends AbstractPimcoreMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $configTable = $schema->getTable(ElementsProcessManagerBundle::TABLE_NAME_CONFIGURATION);
        if (!$configTable->hasColumn('executorSettings')) {
            $this->addSql("ALTER TABLE " . ElementsProcessManagerBundle::TABLE_NAME_CONFIGURATION. " ADD COLUMN `executorSettings` TEXT;");
        }

        $monitoringTable = $schema->getTable(ElementsProcessManagerBundle::TABLE_NAME_MONITORING_ITEM);
        if (!$monitoringTable->hasColumn('loggers')) {
            $this->addSql("ALTER TABLE " . ElementsProcessManagerBundle::TABLE_NAME_MONITORING_ITEM. " ADD COLUMN `loggers` TEXT;");
        }

        if (!$monitoringTable->hasColumn('processManagerConfig')) {
            $this->addSql("ALTER TABLE " . ElementsProcessManagerBundle::TABLE_NAME_MONITORING_ITEM . " DROP COLUMN `processManagerConfig`;");
        }

        \Pimcore\Cache::clearTags(['system', 'resource']);

        $db = Db::get();
        $entries = $db->fetchAll("SELECT * FROM " . ElementsProcessManagerBundle::TABLE_NAME_CONFIGURATION);
        foreach ($entries as $entry) {
            $executorClass = \Pimcore\Tool\Serialize::unserialize($entry['executorClass']);
            if ($executorClass instanceof AbstractExecutor) {
                $loggers = [
                    [
                        'logLevel' => 'NOTICE',
                        'simpleLogFormat' => 'on',
                        'class' => '\ProcessManager\Executor\Logger\Application',
                    ],
                    [
                        'logLevel' => 'DEBUG',
                        'simpleLogFormat' => 'on',
                        'class' => '\ProcessManager\Executor\Logger\Console',
                    ],
                    [
                        'logLevel' => 'DEBUG',
                        'filepath' => '',
                        'simpleLogFormat' => 'on',
                        'class' => '\ProcessManager\Executor\Logger\File',
                    ],
                ];
                $data = [
                    'values' => (array)$executorClass->getValues(),
                    'actions' => (array)$executorClass->getActions(),
                    'loggers' => $loggers,
                ];

                $entry['executorClass'] = get_class($executorClass);
                $entry['executorSettings'] = json_encode($data);
                $db->update(
                    ElementsProcessManagerBundle::TABLE_NAME_CONFIGURATION,
                    $entry,
                    ['id' => $entry['id']]
                );
            }
        }
        $this->addSql("ALTER TABLE " . ElementsProcessManagerBundle::TABLE_NAME_CONFIGURATION . " CHANGE COLUMN `executorClass` executorClass VARCHAR(500) not null");
        $this->addSql("DELETE FROM " . ElementsProcessManagerBundle::TABLE_NAME_MONITORING_ITEM);

        /*$configFile = \Pimcore\Config::locateConfigFile('plugin-process-manager.php');
        $config = ElementsProcessManagerBundle::getConfig();
        $config['executorLoggerClasses'] = [
            '\ProcessManager\Executor\Logger\File' => [],
            '\ProcessManager\Executor\Logger\Console' => [],
            '\ProcessManager\Executor\Logger\Application' => [],
        ];
        \Pimcore\File::putPhpFile($configFile, to_php_data_file_format($config));*/
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
    }
}
