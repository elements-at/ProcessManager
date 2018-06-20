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

namespace Elements\Bundle\ProcessManagerBundle;

class SampleClassMethod
{
    use ExecutionTraitClass;

    public function execute()
    {
        $classList = new \Pimcore\Model\DataObject\ClassDefinition\Listing();
        $classes = $classList->load();

        $monitoringItem = ElementsProcessManagerBundle::getMonitoringItem();
        $monitoringItem->setTotalSteps(count($classes))->save();

        $data = [];
        foreach ($classes as $i => $class) {
            /**
             * @var $list \Pimcore\Model\DataObject\Listing
             * @var $class \Pimcore\Model\DataObject\ClassDefinition
             * @var $o \Pimcore\Model\DataObject\AbstractObject
             */
            $monitoringItem->setCurrentStep($i + 1)->setMessage('Processing Object of class '.$class->getName())->save(
            );
            $listName = '\Pimcore\Model\DataObject\\'.$class->getName().'\Listing';
            $list = new $listName();

            // $list->setCondition('o_className = "Material" ');
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

            $monitoringItem->setWorloadCompleted()->save();
            \Pimcore::collectGarbage();
        }

        $monitoringItem->setMessage('Job finished')->setCompleted();
    }
}
