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

use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;

class Phing extends AbstractExecutor
{
    protected $name = 'phing';
    protected $useMonitoringItem = false;
    protected $isShellCommand = true;
    protected $extJsClass = 'pimcore.plugin.processmanager.executor.phing';

    /**
     * @param string[] $callbackSettings
     * @param null | MonitoringItem $monitoringItem
     *
     * @return mixed
     */
    public function getCommand($callbackSettings = [], $monitoringItem = null)
    {
        $dir = PIMCORE_PROJECT_ROOT . '/protected/deployment-scripts/';

        $command = '/usr/bin/phing -buildfile ' . $dir . 'dev-server.xml ';
        $localOptions = $remoteOptions = [];
        foreach ($callbackSettings as $key => $value) {
            if (strpos($key, 'local') === 0) {
                $phingKey = '-D' . lcfirst(substr($key, 5, strlen($key)));
                $localOptions[] = $phingKey . '="' . $value . '"';
            }
            if (strpos($key, 'remote') === 0) {
                $phingKey = '-D' . lcfirst(substr($key, 6, strlen($key)));
                $remoteOptions[] = $phingKey . '="' . $value . '"';
            }
        }

        $command .= implode(' ', $localOptions);
        if ($callbackSettings['localDoRemoteInstallation'] == 'on') {
            $command .= ' -DremoteOptions="' . implode(' ', $remoteOptions) . '" ';
        }

        return $command;
    }
}
