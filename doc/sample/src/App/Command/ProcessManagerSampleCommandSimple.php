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
use Elements\Bundle\ProcessManagerBundle\MetaDataFile;
use Elements\Bundle\ProcessManagerBundle\MonitoringTrait;
use Pimcore\Console\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use \Elements\Bundle\ProcessManagerBundle\Executor\Action;

class ProcessManagerSampleCommandSimple extends AbstractCommand
{
    use ExecutionTrait;
    use MonitoringTrait;

    protected function configure(): void
    {
        $this
            ->setName('process-manager:sample-command-simple')
            ->setDescription('Just an example - using the ProcessManager - simple version')
            ->addMonitoringItemIdOption();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $monitoringItem = $this->initProcessManagerByInputOption($input);

        $monitoringItem->getLogger()->debug('Callback settings: ' . print_r($monitoringItem->getCallbackSettings(),true));
        $metDataFileObject = MetaDataFile::getById('sample-id');

        $start = \Carbon\Carbon::now();
        if ($ts = $metDataFileObject->getData()['lastRun'] ?? null) {
            $lastRun = \Carbon\Carbon::createFromTimestamp($ts);
        } else {
            $lastRun = \Carbon\Carbon::now();
        }

        //query api with last successfully execution time...

        $workload = ['one','two','three','four'];
        $this->startWorkload('Starting process', count($workload));

        foreach ($workload as $item) {
            $monitoringItem->getLogger()->debug('Detailed log info for ' . $item);
            $this->updateWorkload('Processing ' . $item);
            sleep(3);
        }

        $this->completeWorkload();

        //adding some actions programmatically
        $downloadAction = new Action\Download();
        $downloadAction
            ->setAccessKey('myIcon')
            ->setLabel('Download Icon')
            ->setFilePath('/public/bundles/elementsprocessmanager/img/sprite-open-item-action.png')
            ->setDeleteWithMonitoringItem(false);

        $monitoringItem->setActions([
            $downloadAction
        ]);

        $monitoringItem->getLogger()->debug('Last Run: ' . $lastRun->format(\Carbon\Carbon::DEFAULT_TO_STRING_FORMAT));
        $metDataFileObject->setData(['lastRun' => $start->getTimestamp()])->save();

        $monitoringItem->setMessage('Job finished')->setCompleted();
        return 0;
    }
}
