<?php

use ProcessManager\Configuration;
use ProcessManager\MonitoringItem;
use ProcessManager\Plugin;

class ProcessManager_IndexController extends \Pimcore\Controller\Action\Admin
{

    public function getPluginConfigAction(){
        $this->checkPermission('plugin_pm_permission_view');
        $data = [
            'executorClass' => [],
            'executorActionClasses' => [],
        ];


        $pluginConfig = Plugin::getConfig();

        foreach((array)$pluginConfig['executorClasses'] as $class => $config){
            if(\Pimcore\Tool::classExists($class)){
                $o = new $class($config);
                if($o instanceof \ProcessManager\Executor\AbstractExecutor){
                    $data['executorClass'][$o->getName()]['name'] = $o->getName();
                    $data['executorClass'][$o->getName()]['class'] = '\\'.get_class($o);
                    $data['executorClass'][$o->getName()]['config'] = $o->getConfig();
                    $data['executorClass'][$o->getName()]['extJsConfigurationClass'] = $o->getExtJsConfigurationClass();
                }
            }
        }

        foreach((array)$pluginConfig['executorActionClasses'] as $class => $config){
            if(\Pimcore\Tool::classExists($class)){
                $o = new $class($config);
                if($o instanceof \ProcessManager\Executor\Action\AbstractAction){
                    $data['executorActionClasses'][$o->getName()]['name'] = $o->getName();
                    $data['executorActionClasses'][$o->getName()]['class'] = '\\'.get_class($o);
                    $data['executorActionClasses'][$o->getName()]['config'] = $o->getConfig();
                    $data['executorActionClasses'][$o->getName()]['extJsClass'] = $o->getExtJsClass();
                }
            }
        }

        foreach((array)$pluginConfig['executorCallbackClasses'] as $class => $config){
            if(\Pimcore\Tool::classExists($class)){
                $o = new $class($config);
                if($o instanceof \ProcessManager\Executor\Callback\AbstractCallback){
                    $data['executorCallbackClasses'][$o->getName()]['name'] = $o->getName();
                    $data['executorCallbackClasses'][$o->getName()]['class'] = '\\'.get_class($o);
                    $data['executorCallbackClasses'][$o->getName()]['config'] = $o->getConfig();
                    $data['executorCallbackClasses'][$o->getName()]['extJsClass'] = $o->getExtJsClass();
                }
            }
        }

        foreach((array)$pluginConfig['executorLoggerClasses'] as $class => $config){
            if(\Pimcore\Tool::classExists($class)){
                $o = new $class($config);
                if($o instanceof \ProcessManager\Executor\Logger\AbstractLogger){
                    $data['executorLoggerClasses'][$o->getName()]['name'] = $o->getName();
                    $data['executorLoggerClasses'][$o->getName()]['class'] = '\\'.get_class($o);
                    $data['executorLoggerClasses'][$o->getName()]['config'] = $o->getConfig();
                    $data['executorLoggerClasses'][$o->getName()]['extJsClass'] = $o->getExtJsClass();
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
}
