<?php

/**
 * Elements.at
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) elements.at New Media Solutions GmbH (https://www.elements.at)
 *  @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace Elements\Bundle\ProcessManagerBundle;

use Doctrine\DBAL\Migrations\Version;
use Doctrine\DBAL\Schema\Schema;
use Pimcore\Extension\Bundle\Installer\MigrationInstaller;
use Pimcore\Logger;

class Installer extends MigrationInstaller
{
    public function getMigrationVersion(): string
    {
        return '00000001';
    }

    public function migrateInstall(Schema $schema, Version $version)
    {
        $this->createPermissions();
        $this->createTables();
        $this->copyConfig();

        return true;
    }

    public function migrateUninstall(Schema $schema, Version $version)
    {

    }

    public function canBeInstalled()
    {
        return !$this->isInstalled();
    }

    /**
     * {@inheritdoc}
     */
    public function needsReloadAfterInstall()
    {
        return true;
    }


    protected function createTables()
    {
        $db = \Pimcore\Db::get();
        $db->query(
            "CREATE TABLE IF NOT EXISTS  `" . ElementsProcessManagerBundle::TABLE_NAME_CONFIGURATION . "` (
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
            COLLATE='utf8_general_ci' ENGINE=InnoDB;"
        );

        $db->query(
            "CREATE TABLE IF NOT EXISTS  `" . ElementsProcessManagerBundle::TABLE_NAME_MONITORING_ITEM . "` (
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
            COLLATE='utf8_general_ci' ENGINE=InnoDB;"
        );

        $db->query(
            "CREATE TABLE IF NOT EXISTS `" . ElementsProcessManagerBundle::TABLE_NAME_CALLBACK_SETTING . "` (
                `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `creationDate` INT(10) UNSIGNED NOT NULL DEFAULT '0',
                `modificationDate` INT(10) UNSIGNED NOT NULL DEFAULT '0',
                `name` VARCHAR(255) NOT NULL,
                `description` VARCHAR(255) NULL DEFAULT NULL,
                `settings` TEXT NULL,
                `type` VARCHAR(255) NOT NULL,
                PRIMARY KEY (`id`)
            )
            COLLATE='utf8_general_ci' ENGINE=InnoDB;"
        );
    }

    protected function createPermissions()
    {
        foreach ([
                    'plugin_pm_permission_view',
                    'plugin_pm_permission_configure',
                    'plugin_pm_permission_execute',
                 ] as $permissionKey) {
            \Pimcore\Model\User\Permission\Definition::create($permissionKey);
        }
    }

    protected function copyConfig()
    {
        //TODO: Check path 
        $configFile = dirname(__FILE__) . '/Resources/install/plugin-process-manager.php';
        if (!is_dir(PIMCORE_CUSTOM_CONFIGURATION_DIRECTORY)) {
            \Pimcore\File::mkdir(PIMCORE_CUSTOM_CONFIGURATION_DIRECTORY);
        }

        $destFile = PIMCORE_CONFIGURATION_DIRECTORY.'/plugin-process-manager.php';

        copy($configFile, $destFile);
    }
}
