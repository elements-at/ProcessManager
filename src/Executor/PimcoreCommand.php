<?php

/**
 * Created by Elements.at New Media Solutions GmbH
 *
 */

namespace Elements\Bundle\ProcessManagerBundle\Executor;

use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;
use Elements\Bundle\ProcessManagerBundle\Service\CommandsValidator;
use Pimcore\Tool\Console;

class PimcoreCommand extends AbstractExecutor
{
    protected string $name = 'pimcoreCommand';

    protected string $extJsClass = 'pimcore.plugin.processmanager.executor.class.pimcoreCommand';

    /**
     * @param string[] $callbackSettings
     * @param null | MonitoringItem $monitoringItem
     *
     * @return mixed
     */
    public function getCommand($callbackSettings = [], $monitoringItem = null)
    {
        $options = $this->getValues()['commandOptions'] ?? '';
        $options = str_replace('|', '', trim((string) $options));
        $command = Console::getPhpCli() . ' ' . realpath(PIMCORE_PROJECT_ROOT . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'console') . ' ' . $this->getValues()['command'];

        if ($options !== '' && $options !== '0') {
            $command .= ' ' . $options;
        }

        if($monitoringItem instanceof \Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem) {
            $commands = \Pimcore::getKernel()->getContainer()->get(CommandsValidator::class)->getValidCommands();

            if(!array_key_exists($this->getValues()['command'], $commands)) {
                throw new \Exception('Invalid command - not in valid commands');
            }
            /**
             * @var \Pimcore\Console\AbstractCommand $commandObject
             */
            $commandObject = $commands[$this->getValues()['command']];

            if($commandObject->getDefinition()->hasOption('monitoring-item-id')) {
                $command .= ' --monitoring-item-id='.$monitoringItem->getId();
            }

            if($monitoringItem->getParentId()) {
                $command .= ' --monitoring-item-parent-id='.$monitoringItem->getParentId();
            }
        }

        return $command;
    }
}
