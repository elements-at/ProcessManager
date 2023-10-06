<?php

/**
 * Created by Elements.at New Media Solutions GmbH
 *
 */

namespace App\Command;

use Elements\Bundle\ProcessManagerBundle\ExecutionTrait;
use Elements\Bundle\ProcessManagerBundle\Helper;
use Elements\Bundle\ProcessManagerBundle\Model\Configuration;
use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;
use Pimcore\Console\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GroupExecutionCommand extends AbstractCommand
{
    use ExecutionTrait;

    protected function configure()
    {
        parent::configure();
        $this->setName('app:group-execution-command')
            ->addOption(
                'monitoring-item-id',
                null,
                InputOption::VALUE_REQUIRED,
                'Contains the monitoring item if executed via the Pimcore backend'
            );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $monitoringItem = $this->initProcessManager($input->getOption('monitoring-item-id'), ['autoCreate' => true]);
        $logger = $monitoringItem->getLogger();
        $commands = $childMonitoringItems = [];

        //build an array of commands which should be executed
        foreach (['sample_one', 'sample_two'] as $configId) {
            $config = Configuration::getById($configId);
            if (!$config instanceof Configuration) {
                throw new \Exception('Command with id ' . $configId . ' not found');
            }
            $commands[$configId] = $config;
        }

        $monitoringItem->setTotalWorkload(count($commands))->save();
        //execute on after the other
        $i = 0;
        foreach ($commands as $command) {
            $monitoringItem->setMessage('Executing command: ' . $command->getId())->setCurrentWorkload($i)->save();
            $i++;

            //start the child job
            $result = Helper::executeJob($config->getId(), [], $monitoringItem->getExecutedByUser(), [], $monitoringItem->getId());

            if($result['success'] == false) {
                throw new \Exception("Cant' start command " . $command->getId().' Error: ' . $result['error']);
            } else {
                $logger->debug('Executed child command: ' . $result['executedCommand']);

                sleep(2); //give them a little time to ramp up and set the state
                while ($childMonitoringItem = MonitoringItem::getById($result['monitoringItemId'])) {
                    if($childMonitoringItem->isAlive()) {
                        $logger->debug('Child process state (ID: '.$childMonitoringItem->getId().'): ' . $childMonitoringItem->getStatus());
                        sleep(1); //wait until the next check
                    } else {
                        if(in_array($childMonitoringItem->getStatus(), [$childMonitoringItem::STATUS_FINISHED, $childMonitoringItem::STATUS_FINISHED_WITH_ERROR])) {
                            //everything ok -> continue with the next
                            break;
                        } else {
                            throw new \Exception('Child process failed (ID: ' . $childMonitoringItem->getId().'): ' . $childMonitoringItem->getMessage());
                        }
                    }
                }

            }
        }

        $monitoringItem->setWorkloadCompleted()->setMessage('Finished')->save();

        return 0;
    }
}
