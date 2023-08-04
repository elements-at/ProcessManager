<?php

/**
 * Created by Elements.at New Media Solutions GmbH
 *
 */

namespace Elements\Bundle\ProcessManagerBundle\Command;

use Elements\Bundle\ProcessManagerBundle\ElementsProcessManagerBundle;
use Elements\Bundle\ProcessManagerBundle\ExecutionTrait;
use Pimcore\Console\AbstractCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[\Symfony\Component\Console\Attribute\AsCommand('process-manager:class-method-executor', 'Initializes a class and executes a given method.')]
class ClassMethodExecutorCommand extends AbstractCommand
{
    use ExecutionTrait;

    protected function configure(): void
    {
        $this->addOption('monitoring-item-id', null, InputOption::VALUE_REQUIRED, 'Contains the monitoring item if executed via the Pimcore backend');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        static::initProcessManager($input->getOption('monitoring-item-id'));
        self::checkExecutingUser((array)ElementsProcessManagerBundle::getConfiguration()->getAdditionalScriptExecutionUsers());

        $configValues = static::getMonitoringItem()->getConfigValues();
        $class = new $configValues['executorClass']();
        $class->{$configValues['executorMethod']}();

        return Command::SUCCESS;
    }
}
