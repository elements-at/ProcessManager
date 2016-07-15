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

class SampleCommand extends AbstractCommand
{

    use ExecutionTrait;

    protected function configure()
    {
        $this
            ->setName('process-manager:sample-command')
            ->setDescription("Just an example - using the ProcessManager in a pimcore command.")
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

        $classList = new \Pimcore\Model\Object\ClassDefinition\Listing();
        $classes = $classList->load();

        $monitoringItem = $this->getMonitoringItem();
        $totalSteps = count($classes)+1;
        $monitoringItem->setTotalSteps($totalSteps)->save();

        $data = [];
        foreach ($classes as $i => $class) {
            /**
             * @var $list \Pimcore\Model\Object\Listing
             * @var $class \Pimcore\Model\Object\ClassDefinition
             * @var $o \Pimcore\Model\Object\AbstractObject
             */
            $monitoringItem->setCurrentStep($i + 1)->setMessage('Processing Object of class ' . $class->getName())->save();
            sleep(5);
            $listName = '\Pimcore\Model\Object\\' . $class->getName() . '\Listing';
            $list = new $listName();
            $total = $list->getTotalCount();
            $perLoop = 50;

            for ($i = 0; $i < (ceil($total / $perLoop)); $i++) {
                $list->setLimit($perLoop);
                $offset = $i * $perLoop;
                $list->setOffset($offset);

                $monitoringItem->setCurrentWorkload(($offset ?: 1))
                    ->setTotalWorkload($total)
                    ->setDefaultProcessMessage($class->getName())
                    ->save();
                sleep(2);

                $monitoringItem->getLogger()->info($monitoringItem->getMessage());
                $objects = $list->load();

                foreach ($objects as $o) {
                    $data[] = ['ObjectType' => $class->getName(), 'id' => $o->getId(), 'modificationDate' => $o->getModificationDate()];
                    $monitoringItem->getLogger()->info('Processing Object with id: ' . $o->getId());
                }
            }

            $monitoringItem->setWorloadCompleted()->save();
            \Pimcore::collectGarbage();
        }
        $monitoringItem->setCurrentStep($totalSteps)->setCurrentWorkload(0)->setTotalWorkload(0)->setMessage('creating csv file')->save();

        $csvFile = PIMCORE_TEMPORARY_DIRECTORY.'/process-manager-example.csv';
        $file = fopen($csvFile,"w");
        array_unshift($data,array_keys($data[0]));
        foreach ($data as $row)
        {
            fputcsv($file,$row);
        }
        fclose($file);
        $monitoringItem->setCurrentWorkload(1)->setTotalWorkload(1)->setMessage('csv file created')->save();


        $monitoringItem->setMessage('Job finished')->setCompleted();
    }

}

