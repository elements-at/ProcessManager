<?php

use ProcessManager\Configuration;
use ProcessManager\MonitoringItem;
use ProcessManager\Plugin;

class ProcessManager_IndexController extends \Pimcore\Controller\Action\Admin
{

    public function getPluginConfigAction(){
        $this->checkPermission('plugin_pm_permission_view');
        $data = [];


        $pluginConfig = Plugin::getConfig();

        $classTypeMapping = ['executorClasses' => '\ProcessManager\Executor\AbstractExecutor',
                            'executorActionClasses' => '\ProcessManager\Executor\Action\AbstractAction',
                            'executorCallbackClasses' => '\ProcessManager\Executor\Callback\AbstractCallback',
                            'executorLoggerClasses' => '\ProcessManager\Executor\Logger\AbstractLogger',
                        ];
        foreach($classTypeMapping as $classType => $abstractClassType){
            if(is_null($data[$classType])){
                $data[$classType] = [];
            }
            foreach((array)$pluginConfig[$classType] as $config){
                $class = $config['class'];
                if(\Pimcore\Tool::classExists($class)){
                    $o = new $class($config);
                    if($o instanceof $abstractClassType){
                        $data[$classType][$o->getName()]['name'] = $o->getName();
                        $data[$classType][$o->getName()]['class'] = '\\'.get_class($o);
                        $data[$classType][$o->getName()]['config'] = $o->getConfig();
                        $data[$classType][$o->getName()]['extJsClass'] = $o->getExtJsClass();
                    }
                }
            }
        }

        $pimcoreCommands = [];

        $application = new Pimcore\Console\Application();
        foreach($application->all() as $key => $command){
            $tmp = ['description' => $command->getDescription(),'options' => $command->getDefinition()->getOptions()];

            if(!in_array($key,['help','list','update'])){
                $pimcoreCommands[$key] = $tmp;
            }
        }

        ksort($pimcoreCommands);
        $data['pimcoreCommands'] = $pimcoreCommands;

        $data['roles'] = [];

        $list = new \Pimcore\Model\User\Role\Listing();
        $list->setOrder('ASC')->setOrderKey('name');
        foreach($list->load() as $role){
            $data['roles'][] = [
                'id' => $role->getId(),
                'name' => $role->getName()
            ];
        }


        $shortCutMenu = [];

        $list = new Configuration\Listing();
        $list->setUser($this->user);
        $list->setOrderKey('name');
        foreach($list->load() as $config){
            $group = $config->getGroup() ?: 'default';
            $shortCutMenu[$group][] = ['id' => $config->getId(),'name' => $config->getName(),'group' => $config->getGroup()];
        }
        $data['shortCutMenu'] = $shortCutMenu ?: false;


        $this->_helper->json($data);

    }

    public function downloadAction(){

        $monitoringItem = MonitoringItem::getById($this->getParam('id'));
        $actions = $monitoringItem->getActions();
        foreach($actions as $action){
            if($action['accessKey'] == $this->getParam('accessKey')){
                $className = $action['class'];
                /**
                 * @var $class \ProcessManager\Executor\Action\AbstractAction
                 */
                $class = new $className();
                $class->execute($monitoringItem,$action);
            }
        }
    }

    public function updatePluginAction(){
        //just for testing

        $method = 'updateVersion'. $this->getParam('version');

        \ProcessManager\Updater::getInstance()->$method();

    }
}
