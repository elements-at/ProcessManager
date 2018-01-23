<?php

namespace Elements\Bundle\ProcessManagerBundle\Command;

use Elements\Bundle\ProcessManagerBundle\ElementsProcessManagerBundle;
use Elements\Bundle\ProcessManagerBundle\Maintenance;
use Pimcore\Console\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MaintenanceCommand extends AbstractCommand
{
    use \Elements\Bundle\ProcessManagerBundle\ExecutionTrait;

    protected $loggerInitialized = null;

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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $options = ElementsProcessManagerBundle::$maintenanceOptions;
        $monitoringItem = $this->initProcessManager($input->getOption('monitoring-item-id'), $options);
        $this->doUniqueExecutionCheck(null, ['command' => $this->getCommand($options)]);

        \Pimcore\Tool\Console::checkExecutingUser();
        $container = $this->getApplication()->getKernel()->getContainer();
        $renderingEngine = $container->get('templating.engine.delegating');
        $maintenance = new Maintenance($renderingEngine);
        $maintenance->execute();
    }
}
