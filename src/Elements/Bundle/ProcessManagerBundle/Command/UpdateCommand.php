<?php

namespace Elements\Bundle\ProcessManagerBundle\Command;

use Elements\Bundle\ProcessManagerBundle\Updater;
use Pimcore\Console\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class UpdateCommand extends AbstractCommand
{
    use \Elements\Bundle\ProcessManagerBundle\ExecutionTrait;

    protected function configure()
    {
        $this
            ->setName('process-manager:update')
            ->setDescription("Executes the update command");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $monitoringItem = $this->initProcessManager(null,['autoCreate' => true,'name' => $this->getName()]);

        $monitoringItem->getLogger()->debug('Start update');
        Updater::getInstance()->execute();

        $monitoringItem->getLogger()->debug('Finished update');
    }


}

