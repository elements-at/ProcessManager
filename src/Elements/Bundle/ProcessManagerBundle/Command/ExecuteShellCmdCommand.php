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
use Pimcore\Console\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExecuteShellCmdCommand extends AbstractCommand
{
    use \Elements\Bundle\ProcessManagerBundle\ExecutionTrait;

    protected function configure()
    {
        $this
            ->setName('process-manager:execute-shell-cmd')
            ->setDescription('Updates the monitoring item when a shell command finished or failed')
            ->addOption(
                'monitoring-item-id', null,
                InputOption::VALUE_REQUIRED,
                'Contains the monitoring item if executed via the Pimcore backend'
            );
    }

    protected $loggerInitialized = null;

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initProcessManager($input->getOption('monitoring-item-id'));

        $monitoringItem = ElementsProcessManagerBundle::getMonitoringItem();
        $logFile = $monitoringItem->getLogFile();
        $cmd = $monitoringItem->getCommand().' > ' . $logFile;
        exec($cmd, $output, $result);
        if ($result === 0) {
            $monitoringItem->setStatus($monitoringItem::STATUS_FINISHED)->setMessage('Command executed')->save();
        } else {
            $monitoringItem->setStatus($monitoringItem::STATUS_FAILED)->setMessage('FAILED: ' . print_r($result, true))->save();
            throw new \Exception('Execution of command failed: ' . $cmd);
        }
    }
}
