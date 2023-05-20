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

use Elements\Bundle\ProcessManagerBundle\ElementsProcessManagerBundle;
use Elements\Bundle\ProcessManagerBundle\ExecutionTrait;
use Pimcore\Console\AbstractCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ClassMethodExecutorCommand extends AbstractCommand
{
    protected static $defaultName = 'process-manager:class-method-executor';

    protected static $defaultDescription = 'Initializes a class and executes a given method.';

    use ExecutionTrait;

    protected function configure(): void
    {
        $this->addOption('monitoring-item-id', null, InputOption::VALUE_REQUIRED, 'Contains the monitoring item if executed via the Pimcore backend');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        static::initProcessManager($input->getOption('monitoring-item-id'));
        self::checkExecutingUser((array)ElementsProcessManagerBundle::getConfiguration()->getAdditionalScriptExecutionUsers());

        $configValues = static::getMonitoringItem()->getConfigValues();
        $class = new $configValues['executorClass']();
        $class->{$configValues['executorMethod']}();

        return Command::SUCCESS;
    }
}
