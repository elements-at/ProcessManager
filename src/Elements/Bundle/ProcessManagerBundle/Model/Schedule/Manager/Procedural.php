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

namespace Elements\Bundle\ProcessManagerBundle\Model\Schedule\Manager;

use Elements\Bundle\ProcessManagerBundle\ElementsProcessManagerBundle;

class Procedural extends \Pimcore\Model\Schedule\Manager\Procedural
{
    public function run()
    {
        $monitoringItem = ElementsProcessManagerBundle::getMonitoringItem();
        $logger = $monitoringItem->getLogger();

        $this->setLastExecution();

        $totalSteps = count($this->jobs);
        $monitoringItem->setTotalSteps($totalSteps)->save();
        $i = 0;
        foreach ($this->jobs as $job) {
            $i++;
            $job->lock();

            $monitoringItem->setCurrentStep($i)->setMessage('Executing job with ID: ' . $job->getId())->resetWorkload()->save();
            try {
                $job->execute();
                $monitoringItem->setMessage('Finished job with ID: ' . $job->getId())->save();
            } catch (\Exception $e) {
                $monitoringItem->setMessage('Failed to execute job: ' . $job->getId())->save();
                $logger->emergency('Failed to execute job with id: ' . $job->getId());
                $logger->error($e);
            }
            $job->unlock();
            $monitoringItem->setWorkloadCompleted()->save();
        }
    }
}
