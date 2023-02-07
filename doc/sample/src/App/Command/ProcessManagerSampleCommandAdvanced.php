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

namespace App\Command;

use Elements\Bundle\ProcessManagerBundle\ExecutionTrait;
use Elements\Bundle\ProcessManagerBundle\MonitoringTrait;
use Monolog\Logger;
use Pimcore\Console\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use \Elements\Bundle\ProcessManagerBundle\Executor\Action;

class ProcessManagerSampleCommandAdvanced extends AbstractCommand
{
    use ExecutionTrait;
    use MonitoringTrait;

    protected function configure(): void
    {
        $this
            ->setName('process-manager:sample-command-advanced')
            ->setDescription('Just an example - using the ProcessManager with steps and actions')
            ->addMonitoringItemIdOption();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initProcessManagerByInputOption($input);

        $classList = new \Pimcore\Model\DataObject\ClassDefinition\Listing();
        $classList->setLimit(1);
        $classes = $classList->load();

        $monitoringItem = $this->getMonitoringItem();
        $totalSteps = count($classes) + 1;

        $this->startSteps('Processing classes', $totalSteps);

        $data = [];
        foreach ($classes as $class) {
            /**
             * @var $list \Pimcore\Model\DataObject\Listing\Concrete
             * @var $class \Pimcore\Model\DataObject\ClassDefinition
             * @var $object \Pimcore\Model\DataObject\AbstractObject
             */
            $this->updateStep('Processing Object of class ' . $class->getName());
            sleep(5);

            $listName = '\Pimcore\Model\DataObject\\' . ucfirst($class->getName()) . '\Listing';
            $list = new $listName();
            $total = $list->getTotalCount();
            $perLoop = 50;

            $this->startWorkload('Processing ' . $list->getClassName() . 's', $total);

            for ($k = 0, $kMax = ceil($total / $perLoop); $k < $kMax; $k++) {
                $list->setLimit($perLoop);
                $offset = $k * $perLoop;
                $list->setOffset($offset);

                $this->updateWorkload(current: ($offset ?: 1), logLevel: Logger::INFO, itemType: $class->getName());
                sleep(2);

                $objects = $list->load();
                foreach ($objects as $object) {
                    $data[] = [
                        'ObjectType' => $class->getName(),
                        'id' => $object->getId(),
                        'modificationDate' => $object->getModificationDate(),
                    ];
                    $monitoringItem->getLogger()->info('Processing Object with id: ' . $object->getId(), ['relatedObject' => $object]);
                }
            }

            $this->completeWorkload();
            \Pimcore::collectGarbage();
        }

        $this->updateStep('creating csv file');
        $this->startWorkload(null, 1);

        $csvFile = PIMCORE_SYSTEM_TEMP_DIRECTORY . '/process-manager-example.csv';

        $file = fopen($csvFile, 'w');
        array_unshift($data, array_keys($data[0]));
        foreach ($data as $row) {
            fputcsv($file, $row);
        }
        fclose($file);
        $this->updateWorkload('csv file created');

        //adding some actions programmatically
        $downloadAction = new Action\Download();
        $downloadAction
            ->setAccessKey('myIcon')
            ->setLabel('Download Icon')
            ->setFilePath('/public/bundles/elementsprocessmanager/img/sprite-open-item-action.png')
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
        return 0;
    }
}
