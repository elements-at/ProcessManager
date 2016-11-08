<?php

namespace ProcessManager;

use Pimcore\API\Plugin as PluginLib;
use Pimcore\Log\Maintenance;

class Plugin extends PluginLib\AbstractPlugin implements PluginLib\PluginInterface
{
    use ExecutionTrait;

    const VERSION = 3;

    public static $maintenanceOptions = [
        'autoCreate' => true,
        'name' => 'ProcessManager maintenance',
        'loggers' => [
            [
                "logLevel" => "DEBUG",
                "class" => '\ProcessManager\Executor\Logger\Console'
            ],
            [
                "logLevel" => "DEBUG",
                "filepath" => '/website/var/log/process-manager-maintenance.log',
                'class' => '\ProcessManager\Executor\Logger\File'
            ]
        ]
    ];

    protected static $_config = null;

    protected static $monitoringItem;

    const PLUGIN_NAME = 'ProcessManager';

    const TABLE_NAME_CONFIGURATION = 'plugin_process_manager_configuration';
    const TABLE_NAME_MONITORING_ITEM = 'plugin_process_manager_monitoring_item';
    const TABLE_NAME_CALLBACK_SETTING = 'plugin_process_manager_callback_setting';

    public function init(){
        parent::init();
        \Pimcore::getEventManager()->attach('system.console.init', function (\Zend_EventManager_Event $e) {
            /**
             * @var $application \Symfony\Component\Console\Application
             */
            $application = $e->getTarget();
            /**
             * Deactivate the nice formatted Exception because the script would exit with a status code
             * and we don't know if the command was successfully or not because the error_get_last() doesn't cotain
             * the correct error
             */
            if(strpos(implode(' ',(array)$_SERVER["argv"]), "monitoring-item-id")) {
                $application->setCatchExceptions(false);
            }

            $application->add(new \ProcessManager\Console\Command\ClassMethodExecutorCommand());
            $application->add(new \ProcessManager\Console\Command\SampleCommand());
            $application->add(new \ProcessManager\Console\Command\MaintenanceCommand());
            $application->add(new \ProcessManager\Console\Command\ExecuteShellCmdCommand());
        });
    }

    /**
     * @return \ProcessManager\MonitoringItem
     */
    public static function getMonitoringItem()
    {
        return self::$monitoringItem;
    }

    public static function maintenance(){
        $config = self::getConfig();
        if($config['general']['executeWithMaintenance']){
            self::initProcessManager(null,self::$maintenanceOptions);
            $maintenance = new \ProcessManager\Maintenance();
            $maintenance->execute();
        }
    }

    /**
     * @param mixed $monitoringItem
     */
    public static function setMonitoringItem($monitoringItem)
    {
        self::$monitoringItem = $monitoringItem;
    }

    public static function install()
    {
        Updater::getInstance()->execute();
    }

    public static function uninstall()
    {
        // implement your own logic here
        return true;
    }

    public static function isInstalled()
    {
        $config = self::getConfig();
        return !empty($config) && is_readable(Updater::getVersionFile());
    }

    public static function getLogDir(){
        $dir = PIMCORE_WEBSITE_VAR . '/log/process-manager/';
        if(!is_dir($dir)){
            \Pimcore\File::mkdir($dir);
        }
        return $dir;
    }

    public static function needsReloadAfterInstall(){
        return true;
    }

    public static function getTranslationFile($language){
        if (is_file(PIMCORE_PLUGINS_PATH . "/" . self::PLUGIN_NAME . "/texts/" . $language . ".csv")) {
            return "/" . self::PLUGIN_NAME . "/texts/" . $language . ".csv";
        } else {
            return "/" . self::PLUGIN_NAME . "/texts/en.csv";
        }
    }

    public static function getConfig()
    {
        if (is_null(self::$_config)) {
            self::$_config = include \Pimcore\Config::locateConfigFile("plugin-process-manager.php");
        }
        return self::$_config;
    }

    public static function shutdownHandler($arguments){
        /**
         * @var $monitoringItem \ProcessManager\MonitoringItem
         */
        if($monitoringItem = \ProcessManager\Plugin::getMonitoringItem()){

            $error = error_get_last();
            if(in_array($error['type'],[E_WARNING,E_DEPRECATED,E_STRICT,E_NOTICE])){
                if($config = Configuration::getById($monitoringItem->getConfigurationId())){
                    $versions = $config->getKeepVersions();
                    if(is_numeric($versions)){
                        $list = new MonitoringItem\Listing();
                        $list->setOrder('DESC')->setOrderKey('id')->setOffset((int)$versions)->setLimit(100000000000); //a limit has to defined otherwise the offset wont work
                        $list->setCondition('status ="finished" AND configurationId=? AND IFNULL(pid,0) != ? ',[$config->getId(),$monitoringItem->getPid()]);

                        $items = $list->load();
                        foreach($items as $item){
                            $item->delete();
                        }
                    }
                }
                if(!$monitoringItem->getMessage()){
                    $monitoringItem->setMessage('finished');
                }
                $monitoringItem->setCompleted();
                $monitoringItem->setPid(null)->save();


            }else{
                $monitoringItem->setMessage('ERROR:' . print_r($error,true).$monitoringItem->getMessage());
                $monitoringItem->setPid(null)->setStatus($monitoringItem::STATUS_FAILED)->save();
            }
        }
    }

    public static function startup($arguments) {
        $monitoringItem = $arguments['monitoringItem'];
        if($monitoringItem instanceof \ProcessManager\MonitoringItem){
            $monitoringItem->resetState()->save();
            $monitoringItem->setPid(getmypid());
            $monitoringItem->setStatus($monitoringItem::STATUS_RUNNING);
            $monitoringItem->save();
        }
    }
}
