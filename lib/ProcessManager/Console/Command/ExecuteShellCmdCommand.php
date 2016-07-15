<?php
/**
 * Created by PhpStorm.
 * User: ckogler
 * Date: 23.06.2016
 * Time: 17:01
 */

namespace ProcessManager\Console\Command;

use Pimcore\Console\AbstractCommand;
use ProcessManager\ExecutionTrait;
use ProcessManager\Maintenance;
use ProcessManager\MonitoringItem;
use ProcessManager\Plugin;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use \Pimcore\Model\Object;


class ExecuteShellCmdCommand extends AbstractCommand
{

    use ExecutionTrait;

    protected function configure()
    {
        $this
            ->setName('process-manager:execute-shell-cmd')
            ->setDescription("Updates the monitoring item when a shell command finished or failed")
            ->addOption(
                'monitoring-item-id', null,
                InputOption::VALUE_REQUIRED,
                "Contains the monitoring item if executed via the Pimcore backend"
            );
    }

    protected $loggerInitialized = null;

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initProcessManager($input->getOption('monitoring-item-id'));

        $monitoringItem = Plugin::getMonitoringItem();
        $logFile = $monitoringItem->getLogFile();
        $cmd = $monitoringItem->getCommand().' > ' . $logFile;
        exec($cmd,$output,$result);
        if($result === 0){
            $monitoringItem->setStatus($monitoringItem::STATUS_FINISHED)->setMessage('Command executed')->save();
        }else{
            $monitoringItem->setStatus($monitoringItem::STATUS_FAILED)->setMessage('FAILED: ' . print_r($result,true))->save();
            throw new \Exception("Execution of command failed: " . $cmd);
        }
    }

}

