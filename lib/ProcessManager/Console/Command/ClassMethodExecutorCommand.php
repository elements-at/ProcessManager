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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use \Pimcore\Model\Object;

class ClassMethodExecutorCommand extends AbstractCommand
{

    use ExecutionTrait;

    protected function configure()
    {
        $this
            ->setName('process-manager:class-method-executor')
            ->setDescription("Initializes a class and executes a given method.")
            ->addOption(
                'monitoring-item-id', null,
                InputOption::VALUE_REQUIRED,
                "Contains the monitoring item if executed via the Pimcore backend"
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initProcessManager($input->getOption('monitoring-item-id'));
        \Pimcore\Tool\Console::checkExecutingUser();

        $configValues = $this->getMonitoringItem()->getConfigValues();
        $class = new $configValues['executorClass']();
        $class->{$configValues['executorMethod']}();

    }

}

