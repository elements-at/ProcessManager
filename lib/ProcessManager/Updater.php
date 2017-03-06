<?php
/**
 * Created by PhpStorm.
 * User: ckogler
 * Date: 23.06.2016
 * Time: 09:50
 */

namespace ProcessManager;

use Pimcore\Model\Asset\Image\Thumbnail\Config;

class Updater {

    protected static $_instance;

    protected function __construct(){}
    protected function __clone(){}

    /**
     * @return Updater
     */
    public static function getInstance(){
        if(!self::$_instance){
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function execute(){

        $monitoringItem = Plugin::getMonitoringItem(php_sapi_name() === 'cli' ? true : false);

        $lastVersion = 0;
        $versionFile = self::getVersionFile();
        if(is_readable($versionFile)){
            $lastVersion = file_get_contents($versionFile);
        }
        $lastVersion = (int)$lastVersion;

        if($monitoringItem){
            $monitoringItem->getLogger()->notice('Current Version:' . $lastVersion);
        }

        $methods = [];
        $self = new self();
        foreach (get_class_methods(get_class($self)) as $method) {
            if (stripos($method, 'updateVersion') !== false && $method != __FUNCTION__) {
                $methods[] = $method;
            }
        }

        sort($methods);
        foreach($methods as $method){
            $vNumber = (int)str_replace('updateVersion','',$method);
            if($vNumber > $lastVersion){
                if($monitoringItem){
                    $monitoringItem->getLogger()->notice('Updating to version: ' . $vNumber.' | executin method: ' . $method.'()');
                }
                $self->$method();
                file_put_contents($versionFile,(int)$vNumber);

                if($monitoringItem){
                    $monitoringItem->getLogger()->notice('Update to version: ' . $vNumber.' successfully.');
                }
            }
        }
    }


    public static function getVersionFile(){
        $dir = PIMCORE_WEBSITE_VAR . '/plugins/'.Plugin::PLUGIN_NAME.'/';
        if(!is_dir($dir)){
            \Pimcore\File::mkdir($dir);
        }
        return $dir.'version.txt';
    }

    public function updateVersion1(){
        $this->createPermissions();
        $this->createTables();
        $this->copyConfig();
    }

    protected function updateVersion2(){
        $db = \Pimcore\Db::get();
        $db->query("CREATE TABLE IF NOT EXISTS `".Plugin::TABLE_NAME_CALLBACK_SETTING."` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`creationDate` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`modificationDate` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`name` VARCHAR(255) NOT NULL,
	`description` VARCHAR(255) NULL DEFAULT NULL,
	`settings` TEXT NULL,
	`type` VARCHAR(255) NOT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
");

    }
    protected function updateVersion3(){
        $db = \Pimcore\Db::get();
        try{
            $db->query("ALTER TABLE ".Plugin::TABLE_NAME_MONITORING_ITEM." ADD COLUMN actions TEXT");
        }catch(\Exception $e){}
        \Pimcore\Cache::clearTags(["system", "resource"]);
    }

    protected function updateVersion4(){
        $db = \Pimcore\Db::get();

        $configColumns = array_keys($db->describeTable(Plugin::TABLE_NAME_CONFIGURATION));
        if(!in_array('executorSettings',$configColumns)){
            $db->query("ALTER TABLE ".Plugin::TABLE_NAME_CONFIGURATION." ADD COLUMN `executorSettings` TEXT");
        }

        $monitoringItemColumns = $configColumns = array_keys($db->describeTable(Plugin::TABLE_NAME_MONITORING_ITEM));

        if(!in_array('loggers',$monitoringItemColumns)){
            $db->query("ALTER TABLE ".Plugin::TABLE_NAME_MONITORING_ITEM." ADD COLUMN `loggers` TEXT ");
        }

        if(in_array('processManagerConfig',$monitoringItemColumns)){
            $db->query("ALTER TABLE ".Plugin::TABLE_NAME_MONITORING_ITEM." DROP COLUMN `processManagerConfig`");
        }

        \Pimcore\Cache::clearTags(["system", "resource"]);


        $entries = $db->fetchAll('SELECT * FROM ' .  Plugin::TABLE_NAME_CONFIGURATION);
        foreach($entries as $entry) {
            $executorClass = \Pimcore\Tool\Serialize::unserialize($entry['executorClass']);
            if($executorClass instanceof \ProcessManager\Executor\AbstractExecutor){
                $loggers = [
                    [
                        'logLevel' => 'NOTICE',
                        'simpleLogFormat' => 'on',
                        'class' => '\ProcessManager\Executor\Logger\Application'
                    ],
                    [
                        'logLevel' => 'DEBUG',
                        'simpleLogFormat' => 'on',
                        'class' => '\ProcessManager\Executor\Logger\Console'
                    ],
                    [
                        'logLevel' => 'DEBUG',
                        'filepath' => '',
                        'simpleLogFormat' => 'on',
                        'class' => '\ProcessManager\Executor\Logger\File'
                    ],
                ];
                $data = [
                    'values' => (array)$executorClass->getValues(),
                    'actions' => (array)$executorClass->getActions(),
                    'loggers' => $loggers,
                ];

                $entry['executorClass'] = get_class($executorClass);
                $entry['executorSettings'] = json_encode($data);
                $db->update(Plugin::TABLE_NAME_CONFIGURATION,$entry,"id=".$entry['id']);
            }
        }
        $db->query("ALTER TABLE ".Plugin::TABLE_NAME_CONFIGURATION." CHANGE COLUMN `executorClass` executorClass VARCHAR(500) not null");
        $db->query("DELETE FROM " . Plugin::TABLE_NAME_MONITORING_ITEM);


        $configFile = \Pimcore\Config::locateConfigFile("plugin-process-manager.php");
        $config = Plugin::getConfig();
        $config['executorLoggerClasses'] = [
            '\ProcessManager\Executor\Logger\File' => [],
            '\ProcessManager\Executor\Logger\Console' => [],
            '\ProcessManager\Executor\Logger\Application' => [],
        ];
        \Pimcore\File::putPhpFile($configFile, to_php_data_file_format($config));
    }

    public function updateVersion5(){
        $db = \Pimcore\Db::get();
        $metadata = $db->describeTable(Plugin::TABLE_NAME_CONFIGURATION);
        if(!in_array('restrictToRoles',array_keys($metadata))){
            $db->query("ALTER TABLE ".Plugin::TABLE_NAME_CONFIGURATION." ADD COLUMN `restrictToRoles` VARCHAR(100) not null default ''");
        }
        \Pimcore\Cache::clearTags(["system", "resource"]);
    }


    protected function updateVersion6(){
        $db = \Pimcore\Db::get();
        try {
            $db->query("ALTER TABLE ".Plugin::TABLE_NAME_CONFIGURATION." ADD UNIQUE INDEX `name` (`name`)");
        }catch (\Exception $e){
            echo "Can't add Unique key to column 'name' in '" . Plugin::TABLE_NAME_CONFIGURATION."'. Please add them manually!";
        }
    }

    public function updateVersion7(){
        $configFile = \Pimcore\Config::locateConfigFile("plugin-process-manager.php");
        $config = Plugin::getConfig();

        foreach(['executorClasses','executorLoggerClasses','executorActionClasses','executorCallbackClasses'] as $classType){
            $tmp = [];
            foreach($config[$classType] as $key => $value){
                $value['class'] = $key;
                $tmp[] = $value;
            }
            $config[$classType] = $tmp;
        }
        \Pimcore\File::putPhpFile($configFile, to_php_data_file_format($config));
    }


    protected function copyConfig(){
        $configFile = PIMCORE_DOCUMENT_ROOT.'/plugins/ProcessManager/install/plugin-process-manager.php';
        if(!is_dir(PIMCORE_CUSTOM_CONFIGURATION_DIRECTORY)){
            \Pimcore\File::mkdir(PIMCORE_CUSTOM_CONFIGURATION_DIRECTORY);
        }
        copy($configFile,PIMCORE_CUSTOM_CONFIGURATION_DIRECTORY.'/plugin-process-manager.php');
    }


    protected function createTables(){
        $db = \Pimcore\Db::get();
        $db->query("CREATE TABLE IF NOT EXISTS  `".Plugin::TABLE_NAME_CONFIGURATION."` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`creationDate` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`modificationDate` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`name` VARCHAR(255) NOT NULL,
	`group` VARCHAR(50) NULL DEFAULT NULL,
	`description` VARCHAR(255) NULL DEFAULT NULL,
	`executorClass` LONGTEXT NOT NULL,
	`cronJob` VARCHAR(20) NULL DEFAULT NULL,
	`lastCronJobExecution` INT(10) UNSIGNED NULL DEFAULT NULL,
	`active` TINYINT(4) NULL DEFAULT '1',
	`keepVersions` CHAR(1) NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;
");


        $db->query("CREATE TABLE IF NOT EXISTS  `". Plugin::TABLE_NAME_MONITORING_ITEM ."` (
	`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`creationDate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
	`modificationDate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
	`reportedDate` INT(10) UNSIGNED NULL DEFAULT NULL,
	`currentStep` TINYINT(3) UNSIGNED NULL DEFAULT NULL,
	`totalSteps` TINYINT(3) UNSIGNED NULL DEFAULT NULL,
	`totalWorkload` INT(10) UNSIGNED NULL DEFAULT NULL,
	`currentWorkload` INT(10) UNSIGNED NULL DEFAULT NULL,
	`name` VARCHAR(255) NULL DEFAULT NULL,
	`processManagerConfig` TEXT NULL,
	`command` LONGTEXT NULL,
	`status` VARCHAR(20) NULL DEFAULT NULL,
	`updated` INT(11) NULL DEFAULT NULL,
	`message` LONGTEXT NULL,
	`configurationId` INT(11) NULL DEFAULT NULL,
	`pid` INT(11) NULL DEFAULT NULL,
	`callbackSettings` LONGTEXT NULL,
	`executedByUser` INT(11) NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	INDEX `updated` (`updated`),
	INDEX `status` (`status`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;
");

    }
    protected function createPermissions(){
        foreach(array('plugin_pm_permission_view','plugin_pm_permission_configure','plugin_pm_permission_execute') as $permissionKey){
            \Pimcore\Model\User\Permission\Definition::create($permissionKey);
        }
    }




}