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

use Elements\Bundle\ProcessManagerBundle\Model\Configuration;
use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;
use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Pimcore\Extension\Bundle\Traits\PackageVersionTrait;
use Pimcore\Extension\Bundle\Traits\StateHelperTrait;

class ElementsProcessManagerBundle extends AbstractPimcoreBundle
{
    use ExecutionTrait;
    use PackageVersionTrait;
    use StateHelperTrait;

    public static $maintenanceOptions = [
        'autoCreate' => true,
        'name' => 'ProcessManager maintenance',
        'loggers' => [
            [
                'logLevel' => 'DEBUG',
                'class' => '\Elements\Bundle\ProcessManagerBundle\Executor\Logger\Console',
                'simpleLogFormat' => true
            ],
            [
                'logLevel' => 'DEBUG',
                'filepath' => '/var/logs/process-manager-maintenance.log',
                'class' => '\Elements\Bundle\ProcessManagerBundle\Executor\Logger\File',
                'simpleLogFormat' => true,
                'maxFileSizeMB' => 50
            ]
        ]
    ];

    protected static $_config = null;

    protected static $monitoringItem;

    const PLUGIN_NAME = 'ProcessManager';

    const TABLE_NAME_CONFIGURATION = 'plugin_process_manager_configuration';
    const TABLE_NAME_MONITORING_ITEM = 'plugin_process_manager_monitoring_item';
    const TABLE_NAME_CALLBACK_SETTING = 'plugin_process_manager_callback_setting';
    const MONITORING_ITEM_ENV_VAR = 'monitoringItemId';

    /**
     * @return array
     */
    public function getCssPaths()
    {
        return [
            '/bundles/elementsprocessmanager/css/admin.css'
        ];
    }

    /**
     * @return array
     */
    public function getJsPaths()
    {
        return [
            '/bundles/elementsprocessmanager/js/startup.js',
            '/bundles/elementsprocessmanager/js/window/detailwindow.js',
            '/bundles/elementsprocessmanager/js/helper/form.js',

            '/bundles/elementsprocessmanager/js/panel/config.js',
            '/bundles/elementsprocessmanager/js/panel/general.js',
            '/bundles/elementsprocessmanager/js/panel/monitoringItem.js',
            '/bundles/elementsprocessmanager/js/panel/callbackSetting.js',

            '/bundles/elementsprocessmanager/js/executor/class/abstractExecutor.js',
            '/bundles/elementsprocessmanager/js/executor/class/command.js',
            '/bundles/elementsprocessmanager/js/executor/class/classMethod.js',
            '/bundles/elementsprocessmanager/js/executor/class/pimcoreCommand.js',
            '/bundles/elementsprocessmanager/js/executor/class/exportToolkit.js',
            '/bundles/elementsprocessmanager/js/executor/class/phing.js',

            '/bundles/elementsprocessmanager/js/executor/action/abstractAction.js',
            '/bundles/elementsprocessmanager/js/executor/action/download.js',
            '/bundles/elementsprocessmanager/js/executor/action/openItem.js',
            '/bundles/elementsprocessmanager/js/executor/action/jsEvent.js',

            '/bundles/elementsprocessmanager/js/executor/logger/abstractLogger.js',
            '/bundles/elementsprocessmanager/js/executor/logger/file.js',
            '/bundles/elementsprocessmanager/js/executor/logger/console.js',
            '/bundles/elementsprocessmanager/js/executor/logger/application.js',
            '/bundles/elementsprocessmanager/js/executor/logger/emailSummary.js',

            '/bundles/elementsprocessmanager/js/executor/callback/abstractCallback.js',
            '/bundles/elementsprocessmanager/js/executor/callback/example.js',
            '/bundles/elementsprocessmanager/js/executor/callback/default.js',
            '/bundles/elementsprocessmanager/js/executor/callback/executionNote.js',
            '/bundles/elementsprocessmanager/js/executor/callback/phing.js',
            '/bundles/elementsprocessmanager/js/window/activeProcesses.js',


        ];
    }

    /**
     * If the bundle has an installation routine, an installer is responsible of handling installation related tasks
     *
     * @return InstallerInterface|null
     */
    public function getInstaller()
    {
        return $this->container->get(Installer::class);
    }

    public static function shutdownHandler($arguments)
    {
        /**
         * @var $monitoringItem MonitoringItem
         */
        if ($monitoringItem = self::getMonitoringItem()) {
            $error = error_get_last();
            Helper::executeMonitoringItemLoggerShutdown($monitoringItem);

            if (in_array($error['type'], [E_WARNING, E_DEPRECATED, E_STRICT, E_NOTICE])) {
                if ($config = Configuration::getById($monitoringItem->getConfigurationId())) {
                    $versions = $config->getKeepVersions();
                    if (is_numeric($versions)) {
                        $list = new MonitoringItem\Listing();
                        $list->setOrder('DESC')->setOrderKey('id')->setOffset((int)$versions)->setLimit(100000000000); //a limit has to defined otherwise the offset wont work
                        $list->setCondition('status ="finished" AND configurationId=? AND IFNULL(pid,0) != ? AND parentId IS NULL ', [$config->getId(), $monitoringItem->getPid()]);

                        $items = $list->load();
                        foreach ($items as $item) {
                            $item->delete();
                        }
                    }
                }
                if (!$monitoringItem->getMessage()) {
                    $monitoringItem->setMessage('finished');
                }
                $monitoringItem->setCompleted();
                $monitoringItem->setPid(null)->save();
            } else {
                $monitoringItem->setMessage('ERROR:' . print_r($error, true) . $monitoringItem->getMessage());
                $monitoringItem->setPid(null)->setStatus($monitoringItem::STATUS_FAILED)->save();
            }
        }
    }

    public static function startup($arguments)
    {
        $monitoringItem = $arguments['monitoringItem'];
        if ($monitoringItem instanceof MonitoringItem) {
            $monitoringItem->resetState()->save();
            $monitoringItem->setPid(getmypid());
            $monitoringItem->setStatus($monitoringItem::STATUS_RUNNING);
            $monitoringItem->save();
        }
    }

    public static function getConfig()
    {
        if (is_null(self::$_config)) {
            $configFile = \Pimcore\Config::locateConfigFile('plugin-process-manager.php');
            self::$_config = include $configFile;
        }

        return self::$_config;
    }

    public static function getLogDir()
    {
        $dir = PIMCORE_PRIVATE_VAR . '/logs/process-manager/';
        if (!is_dir($dir)) {
            \Pimcore\File::mkdir($dir);
        }

        return $dir;
    }

    public function getDescription()
    {
        return 'Process Manager';
    }

    /**
     * @param mixed $monitoringItem
     */
    public static function setMonitoringItem($monitoringItem)
    {
        self::$monitoringItem = $monitoringItem;
    }

    /**
     * @param bool $createDummyObjectIfRequired
     *
     * @return MonitoringItem
     */
    public static function getMonitoringItem($createDummyObjectIfRequired = true)
    {
        if ($createDummyObjectIfRequired && !self::$monitoringItem) {
            if(getenv(self::MONITORING_ITEM_ENV_VAR)){
                self::$monitoringItem = MonitoringItem::getById(getenv(self::MONITORING_ITEM_ENV_VAR));
                self::$monitoringItem->setStatus(MonitoringItem::STATUS_RUNNING)->save();
            }else{
                self::$monitoringItem = new MonitoringItem();
                self::$monitoringItem->setIsDummy(true);
            }
        }

        return self::$monitoringItem;
    }

    public static function getPluginWebsitePath()
    {
        $path = PIMCORE_PRIVATE_VAR . '/bundles/elementsprocessmanager/';
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        return $path;
    }

    protected function getComposerPackageName(): string
    {
        return 'elements/process-manager-bundle';
    }

    public function getNiceName()
    {
        return self::PLUGIN_NAME;
    }

}
