<?php

/**
 * Created by Elements.at New Media Solutions GmbH
 *
 */

namespace Elements\Bundle\ProcessManagerBundle\Command;

use Elements\Bundle\ProcessManagerBundle\ElementsProcessManagerBundle;
use Elements\Bundle\ProcessManagerBundle\ExecutionTrait;
use Elements\Bundle\ProcessManagerBundle\Maintenance;
use Pimcore\Console\AbstractCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Twig\Environment;

#[\Symfony\Component\Console\Attribute\AsCommand('process-manager:maintenance', 'Executes regular maintenance tasks (Check Processes, execute cronjobs)')]
class MaintenanceCommand extends AbstractCommand
{
    use ExecutionTrait;

    public function __construct(private readonly Environment $templatingEngine)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('monitoring-item-id', null, InputOption::VALUE_REQUIRED, 'Contains the monitoring item if executed via the Pimcore backend');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $options = ElementsProcessManagerBundle::getMaintenanceOptions();
        $monitoringItem = static::initProcessManager($input->getOption('monitoring-item-id'), $options);
        static::doUniqueExecutionCheck(null, ['command' => static::getCommand($options)]);

        self::checkExecutingUser((array)ElementsProcessManagerBundle::getConfiguration()->getAdditionalScriptExecutionUsers());

        $maintenance = new Maintenance($this->templatingEngine);
        $maintenance->execute();

        return Command::SUCCESS;
    }
}
