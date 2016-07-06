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
use ProcessManager\Plugin;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use \Pimcore\Model\Object;


class MaintenanceCommand extends AbstractCommand
{

    use ExecutionTrait;

    protected function configure()
    {
        $this
            ->setName('process-manager:maintenance')
            ->setDescription("Executes regular maintenance tasks (Check Processes, execute cronjobs)")
            ->addOption(
                'monitoring-item-id', null,
                InputOption::VALUE_REQUIRED,
                "Contains the monitoring item if executed via the Pimcore backend"
            );
    }

    protected $loggerInitialized = null;



    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $options = Plugin::$maintenanceOptions;
        $this->initProcessManager($input->getOption('monitoring-item-id'),$options);
        $this->doUniqueExecutionCheck(null,['command' => $this->getCommand($options)]);

        \Pimcore\Tool\Console::checkExecutingUser();
        $maintenance = new Maintenance();
        $maintenance->execute();
    }


}

