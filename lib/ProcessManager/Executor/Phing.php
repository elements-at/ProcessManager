<?php
/**
 * Created by PhpStorm.
 * User: ckogler
 * Date: 22.06.2016
 * Time: 14:16
 */

namespace ProcessManager\Executor;

class Phing extends AbstractExecutor
{
    protected $name = 'phing';
    protected $useMonitoringItem = false;
    protected $isShellCommand = true;
    protected $extJsClass = 'pimcore.plugin.processmanager.executor.phing';

    /**
     * @param string[] $callbackSettings
     * @param null | \ProcessManager\MonitoringItem $monitoringItem
     * @return mixed
     */
    public function getCommand($callbackSettings = [], $monitoringItem = null)
    {
        $dir = PIMCORE_DOCUMENT_ROOT.'/protected/deployment-scripts/';


        $command = '/usr/bin/phing -buildfile ' . $dir.'dev-server.xml ';
        $localOptions = $remoteOptions = [];
        foreach($callbackSettings as $key => $value){
            if(strpos($key,'local') === 0){
                $phingKey = '-D' . lcfirst(substr($key,5,strlen($key)));
                $localOptions[] = $phingKey.'="'.$value.'"';
            }
            if(strpos($key,'remote') === 0){
                $phingKey = '-D' . lcfirst(substr($key,6,strlen($key)));
                $remoteOptions[] = $phingKey.'="'.$value.'"';
            }
        }

        $command .= implode(' ',$localOptions);
        if($callbackSettings['localDoRemoteInstallation'] == 'on'){
            $command .= ' -DremoteOptions="'. implode(' ',$remoteOptions).'" ';
        }
        return $command;
    }

}