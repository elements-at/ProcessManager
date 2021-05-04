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

use Pimcore\Console\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Elements\Bundle\ProcessManagerBundle\ElementsProcessManagerBundle;

class ClassMethodExecutorCommand extends AbstractCommand
{
    use \Elements\Bundle\ProcessManagerBundle\ExecutionTrait;

    protected function configure()
    {
        $this
            ->setName('process-manager:class-method-executor')
            ->setDescription('Initializes a class and executes a given method.')
            ->addOption(
                'monitoring-item-id', null,
                InputOption::VALUE_REQUIRED,
                'Contains the monitoring item if executed via the Pimcore backend'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initProcessManager($input->getOption('monitoring-item-id'));
        self::checkExecutingUser((array)ElementsProcessManagerBundle::getConfiguration()->getAdditionalScriptExecutionUsers());

        $configValues = $this->getMonitoringItem()->getConfigValues();
        $class = new $configValues['executorClass']();
        $class->{$configValues['executorMethod']}();
        return 0;
    }
}
