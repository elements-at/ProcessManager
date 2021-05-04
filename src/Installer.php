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

use Elements\Bundle\ProcessManagerBundle\Migrations\Version20210428000000;
use Pimcore\Extension\Bundle\Installer\SettingsStoreAwareInstaller;
use Pimcore\Model\Translation\Admin;
use Pimcore\Model\User\Permission\Definition;
use Elements\Bundle\ProcessManagerBundle\Enums;

class Installer extends SettingsStoreAwareInstaller
{


    protected array $permissions = [
        Enums\Permissions::VIEW,
        Enums\Permissions::CONFIGURE,
        Enums\Permissions::EXECUTE,
    ];

    public function install()
    {
        $this->createPermissions();
        $this->createTables();
        parent::install();
    }

    protected function createPermissions()
    {
        foreach ($this->permissions as $permissionKey) {
            Definition::create($permissionKey);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function needsReloadAfterInstall()
    {
        return true;
    }

    /**
     * @return \Pimcore\Db\Connection|\Pimcore\Db\ConnectionInterface
     */
    protected function getDb(){
        return \Pimcore\Db::get();
    }

    protected function createTables()
    {
        $db = $this->getDb();

        $db->query(
            "CREATE TABLE IF NOT EXISTS `" . ElementsProcessManagerBundle::TABLE_NAME_CONFIGURATION . "` (
            `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `creationDate` INT(10) UNSIGNED NOT NULL DEFAULT '0',
            `modificationDate` INT(10) UNSIGNED NOT NULL DEFAULT '0',
            `name` VARCHAR(255) NOT NULL,
            `group` VARCHAR(50) NULL DEFAULT NULL,
            `description` VARCHAR(255) NULL DEFAULT NULL,
            `executorClass` VARCHAR(500) NOT NULL,
            `cronJob` TEXT NULL,
            `lastCronJobExecution` INT(10) UNSIGNED NULL DEFAULT NULL,
            `active` TINYINT(4) NULL DEFAULT '1',
            `keepVersions` INT(10) UNSIGNED NULL DEFAULT NULL,
            `executorSettings` TEXT NULL,
            `restrictToRoles` VARCHAR(100) NOT NULL DEFAULT '',
            `action` TEXT NULL,
            PRIMARY KEY (`id`),
            UNIQUE INDEX `name` (`name`)
        )
        ENGINE=InnoDB DEFAULT CHARSET=utf8
        ");

        $db->query(
            "CREATE TABLE IF NOT EXISTS `" . ElementsProcessManagerBundle::TABLE_NAME_MONITORING_ITEM . "` (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `parentId` INT(11) NULL DEFAULT NULL,
            `creationDate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
            `modificationDate` INT(11) UNSIGNED NOT NULL DEFAULT '0',
            `reportedDate` INT(10) UNSIGNED NULL DEFAULT NULL,
            `currentStep` SMALLINT(5) UNSIGNED NULL DEFAULT NULL,
            `totalSteps` SMALLINT(5) UNSIGNED NULL DEFAULT NULL,
            `totalWorkload` INT(10) UNSIGNED NULL DEFAULT NULL,
            `currentWorkload` INT(10) UNSIGNED NULL DEFAULT NULL,
            `name` VARCHAR(255) NULL DEFAULT NULL,
            `command` LONGTEXT NULL,
            `status` VARCHAR(20) NULL DEFAULT NULL,
            `updated` INT(11) NULL DEFAULT NULL,
            `message` VARCHAR(1000) NULL DEFAULT NULL,
            `configurationId` INT(11) NULL DEFAULT NULL,
            `pid` INT(11) NULL DEFAULT NULL,
            `callbackSettings` LONGTEXT NULL,
            `executedByUser` INT(11) NULL DEFAULT '0',
            `actions` TEXT NULL,
            `loggers` TEXT NULL,
            `metaData` LONGTEXT NULL,
            `published` TINYINT(4) NOT NULL DEFAULT '1',
            `group` VARCHAR(50) NULL DEFAULT NULL,
            `hasCriticalError` TINYINT(4) NOT NULL DEFAULT '0',
            `deleteAfterHours` INT(10) UNSIGNED NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            INDEX `updated` (`updated`),
            INDEX `status` (`status`),
            INDEX `deleteAfterHours` (`deleteAfterHours`)
            )
           ENGINE=InnoDB DEFAULT CHARSET=utf8"
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
            ENGINE=InnoDB DEFAULT CHARSET=utf8;"
        );
    }

    public function uninstall()
    {
        $tables = [
            ElementsProcessManagerBundle::TABLE_NAME_CONFIGURATION,
            ElementsProcessManagerBundle::TABLE_NAME_MONITORING_ITEM,
            ElementsProcessManagerBundle::TABLE_NAME_CALLBACK_SETTING
        ];
        foreach ($tables as $table) {
            $this->getDb()->query("DROP TABLE IF EXISTS " . $table);
        }

        foreach ($this->permissions as $permissionKey) {
            $this->getDb()->query("DELETE FROM users_permission_definitions WHERE " . $this->getDb()->quoteIdentifier("key")." = :permission",["permission" => $permissionKey]);
        }

        parent::uninstall();
    }

    public function getLastMigrationVersionClassName(): ?string
    {
        return Version20210428000000::class;
    }
}
