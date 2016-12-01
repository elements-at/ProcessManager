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


class UpdateCommand extends AbstractCommand
{
    use ExecutionTrait;

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
        \ProcessManager\Updater::getInstance()->execute();

        $monitoringItem->getLogger()->debug('Finished update');
    }


}

