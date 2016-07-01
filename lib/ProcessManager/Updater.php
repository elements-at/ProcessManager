<?php
/**
 * Created by PhpStorm.
 * User: ckogler
 * Date: 23.06.2016
 * Time: 09:50
 */

namespace ProcessManager;

class Updater {

    public function __construct()
    {

    }

    public function install(){
        $this->createPermissions();
        $this->createTables();
        $this->copyConfig();
        $this->createSampleCommandConfig();
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
            "extJsConfigurationClass" => "pimcore.plugin.processmanager.executor.pimcoreCommand"
        ]);
        $config = new Configuration();
        $config->setValues($values);
        $config->setExecutorClass($executor)->save();
    }

    protected function copyConfig(){
        $configFile = PIMCORE_DOCUMENT_ROOT.'/plugins/ProcessManager/install/plugin-process-manager.php';
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
        foreach(array('plugin_process_manager_view','plugin_process_manager_configure','plugin_process_manager_execute') as $permissionKey){
            \Pimcore\Model\User\Permission\Definition::create($permissionKey);
        }
    }
}