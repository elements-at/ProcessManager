<?php
/**
 * Created by PhpStorm.
 * User: ckogler
 * Date: 23.06.2016
 * Time: 09:50
 */

namespace ProcessManager;

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

        $lastVersion = '1.0.0';
        $versionFile = self::getVersionFile();
        if(is_readable($versionFile)){
            $lastVersion = file_get_contents($versionFile);
        }
        $lastVersion = (int)str_replace('.','',$lastVersion);

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
            if($vNumber >= $lastVersion){
                echo "ProcessManager updater: Executing method: \"" . $method."()\" \n";
                $self->$method();
            }
            $vNumber = (string)$vNumber;
            $newVersion = $vNumber[0].'.'.$vNumber[1].'.'.$vNumber[2];
            file_put_contents($versionFile,$newVersion);
        }
    }


    protected function getVersionFile(){
        $dir = PIMCORE_WEBSITE_VAR . '/plugins/'.Plugin::PLUGIN_NAME.'/';
        if(!is_dir($dir)){
            \Pimcore\File::mkdir($dir);
        }
        return $dir.'version.txt';
    }

    public function updateVersion100(){
        $this->createPermissions();
        $this->createTables();
        $this->copyConfig();
        $this->createSampleCommandConfig();
    }

    protected function updateVersion106(){
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

    protected function createSampleCommandConfig(){
        $executor = new \ProcessManager\Executor\PimcoreCommand();
        $values = [
            'name' => 'Sample Command - ProcessManager',
            'keepVersions' => 5,
            'group' => 'test-commands',
            'command' => 'process-manager:sample-command',
            'commandOptions' => '-v',
            'active' => true,
            'description' => 'Just an example to try (loads class objects)'
        ];
        $executor->setValues($values);
        $executor->setActions([
            [
                'accessKey' => 'download',
                'filepath' => '/website/var/tmp/process-manager-example.csv',
                'class' => '\ProcessManager\Executor\Action\Download'
            ]
        ]);
        $executor->setExecutorConfig([
            "name" => "pimcoreCommand",
            "class" => "\\ProcessManager\\Executor\\PimcoreCommand",
            "config" => [],
            "extJsConfigurationClass" => "pimcore.plugin.processmanager.executor.class.pimcoreCommand"
        ]);
        $config = new Configuration();
        $config->setValues($values);
        $config->setExecutorClass($executor)->save();
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