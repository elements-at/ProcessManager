<?php

/**
 * Elements.at
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) elements.at New Media Solutions GmbH (https://www.elements.at)
 *  @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace AppBundle\Command;

use Pimcore\Console\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use \Elements\Bundle\ProcessManagerBundle\Executor\Action;

class ProcessManagerSampleCommandAdvanced extends AbstractCommand
{
    use \Elements\Bundle\ProcessManagerBundle\ExecutionTrait;

    protected function configure()
    {
        $this
            ->setName('process-manager:sample-command-advanced')
            ->setDescription('Just an example - using the ProcessManager with steps and actions')
            ->addOption(
                'monitoring-item-id',
                null,
                InputOption::VALUE_REQUIRED,
                'Contains the monitoring item if executed via the Pimcore backend'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initProcessManager($input->getOption('monitoring-item-id'),['autoCreate' => true]);

        $classList = new \Pimcore\Model\DataObject\ClassDefinition\Listing();
        $classList->setLimit(1);
        $classes = $classList->load();

        $monitoringItem = $this->getMonitoringItem();
        $totalSteps = count($classes) + 1;
        $monitoringItem->setTotalSteps($totalSteps)->save();

        $data = [];
        foreach ($classes as $i => $class) {
            /**
             * @var $list \Pimcore\Model\DataObject\Listing
             * @var $class \Pimcore\Model\DataObject\ClassDefinition
             * @var $o \Pimcore\Model\DataObject\AbstractObject
             */
            $monitoringItem->setCurrentStep($i + 1)->setMessage('Processing Object of class '.$class->getName())->save();
            sleep(5);
            $listName = '\Pimcore\Model\DataObject\\'.ucfirst($class->getName()).'\Listing';
            $list = new $listName();
            $total = $list->getTotalCount();
            $perLoop = 50;

            for ($k = 0, $kMax = ceil($total / $perLoop); $k < $kMax; $k++) {
                $list->setLimit($perLoop);
                $offset = $k * $perLoop;
                $list->setOffset($offset);

                $monitoringItem->setCurrentWorkload(($offset ?: 1))
                    ->setTotalWorkload($total)
                    ->setDefaultProcessMessage($class->getName())
                    ->save();
                sleep(2);

                $monitoringItem->getLogger()->info($monitoringItem->getMessage());
                $objects = $list->load();

                foreach ($objects as $o) {
                    $data[] = [
                        'ObjectType' => $class->getName(),
                        'id' => $o->getId(),
                        'modificationDate' => $o->getModificationDate(),
                    ];
                    $monitoringItem->getLogger()->info('Processing Object with id: '.$o->getId());
                }
            }

            $monitoringItem->setWorkloadCompleted()->save();
            \Pimcore::collectGarbage();
        }
        $monitoringItem->setCurrentStep($totalSteps)->setCurrentWorkload(0)->setTotalWorkload(0)->setMessage(
            'creating csv file'
        )->save();

        $csvFile = PIMCORE_SYSTEM_TEMP_DIRECTORY . '/process-manager-example.csv';

        $file = fopen($csvFile, 'w');
        array_unshift($data, array_keys($data[0]));
        foreach ($data as $row) {
            fputcsv($file, $row);
        }
        fclose($file);
        $monitoringItem->setCurrentWorkload(1)->setTotalWorkload(1)->setMessage('csv file created')->save();


        //adding some actions programmatically
        $downloadAction = new Action\Download();
        $downloadAction
            ->setAccessKey('myIcon')
            ->setLabel('Download Icon')
            ->setFilePath('/web/bundles/elementsprocessmanager/img/sprite-open-item-action.png')
            ->setDeleteWithMonitoringItem(false);

        $openItemAction = new Action\OpenItem();
        $openItemAction
            ->setLabel('Open document')
            ->setItemId(1)
            ->setType('document');

        $monitoringItem->setActions([
            $downloadAction,
            $openItemAction
        ]);

        $monitoringItem->setMessage('Job finished')->setCompleted();
    }
}
