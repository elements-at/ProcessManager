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
use Pimcore\Tool\Console;

class ClassMethod extends AbstractExecutor
{
    protected $name = 'classMethod';
    protected $extJsClass = 'pimcore.plugin.processmanager.executor.class.classMethod';

    /**
     * @param string[] $callbackSettings
     * @param null | MonitoringItem $monitoringItem
     *
     * @return mixed
     */
    public function getCommand($callbackSettings = [], $monitoringItem = null)
    {
        return Console::getPhpCli() . ' ' . realpath(PIMCORE_PROJECT_ROOT . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'console') . ' process-manager:class-method-executor -v';
    }
}
