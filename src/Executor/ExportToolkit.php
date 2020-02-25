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

namespace Elements\Bundle\ProcessManagerBundle\Executor;

use Elements\Bundle\ExportToolkitBundle\Configuration;
use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;

class ExportToolkit extends AbstractExecutor
{
    protected $name = 'exportToolkit';
    protected $extJsClass = 'pimcore.plugin.processmanager.executor.class.exportToolkit';

    public function __construct($config = [])
    {
        parent::__construct($config);

        if (!$this->config['jobs']) {
            if (\Pimcore\Tool::classExists('Elements\\Bundle\\ExportToolkitBundle\\Configuration')) {
                $list = Configuration::getList();
                $result = [];
                /** @var $config Configuration */
                foreach ($list as $config) {
                    $result[] = $config->getName();
                }
                $this->config['jobs'] = $result;
            }
        }
    }

    /**
     * @param string[] $callbackSettings
     * @param null | MonitoringItem $monitoringItem
     *
     * @return mixed
     */
    public function getCommand($callbackSettings = [], $monitoringItem = null)
    {
        $command = \Pimcore\Tool\Console::getPhpCli() . ' ' . PIMCORE_PROJECT_ROOT . '/bin/console export-toolkit:export --config-name=' . $this->getValues()['configName'];

        return $command;
    }
}
