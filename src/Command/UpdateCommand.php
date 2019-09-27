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
            ->setDescription('Executes the update command');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $monitoringItem = $this->initProcessManager(null, ['autoCreate' => true, 'name' => $this->getName()]);

        $monitoringItem->getLogger()->debug('Start update');
        Updater::getInstance()->execute();

        $monitoringItem->getLogger()->debug('Finished update');
    }
}
