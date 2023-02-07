<?php

namespace App\Command;

use Elements\Bundle\ProcessManagerBundle\ExecutionTrait;
use Elements\Bundle\ProcessManagerBundle\Helper;
use Elements\Bundle\ProcessManagerBundle\Model\Configuration;
use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;
use Elements\Bundle\ProcessManagerBundle\MonitoringTrait;
use Pimcore\Console\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GroupExecutionCommand extends AbstractCommand
{
    use ExecutionTrait;
    use MonitoringTrait;

    protected function configure(): void
    {
        $this->setName('app:group-execution-command')
            ->addMonitoringItemIdOption();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $monitoringItem = $this->initProcessManagerByInputOption($input);
        $commands = $childMonitoringItems = [];

        //build an array of commands which should be executed
        foreach (['sample_one', 'sample_two'] as $configId) {
            $config = Configuration::getById($configId);
            if (!$config instanceof Configuration) {
                throw new \Exception('Command with id ' . $configId . ' not found');
            }
            $commands[$configId] = $config;
        }

        $this->startWorkload(null, count($commands));
        //execute on after the other
        foreach ($commands as $command) {
            $this->updateWorkload('Executing command: ' . $command->getId());

            //start the child job
            $result = Helper::executeJob($config->getId(), [], $monitoringItem->getExecutedByUser(), [], $monitoringItem->getId());

            if ($result['success'] == false) {
                throw new \Exception("Cant' start command " . $command->getId() . ' Error: ' . $result['error']);
            }

            $monitoringItem->getLogger()->debug('Executed child command: ' . $result['executedCommand']);

            sleep(2); //give them a little time to ramp up and set the state
            while ($childMonitoringItem = MonitoringItem::getById($result["monitoringItemId"])) {
                if ($childMonitoringItem->isAlive()) {
                    $monitoringItem->getLogger()->debug('Child process state (ID: '.$childMonitoringItem->getId().'): ' . $childMonitoringItem->getStatus());
                    sleep(1); //wait until the next check
                    continue;
                }

                if (in_array($childMonitoringItem->getStatus(), [$childMonitoringItem::STATUS_FINISHED,$childMonitoringItem::STATUS_FINISHED_WITH_ERROR])) {
                    //everything ok -> continue with the next
                    break;
                } else {
                    throw new \Exception("Child process failed (ID: " . $childMonitoringItem->getId().'): ' . $childMonitoringItem->getMessage());
                }
            }
        }

        $this->completeWorkload('Finished');
        return 0;
    }
}
