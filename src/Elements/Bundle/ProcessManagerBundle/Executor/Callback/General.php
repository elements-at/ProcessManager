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

namespace Elements\Bundle\ProcessManagerBundle\Executor\Callback;

/**
 * Class General
 *
 * Pass extJsClass and name in the configuration
 *
 * e.g.:
 '\ProcessManager\Executor\Callback\General' => [
 'extJsClass' => 'pimcore.plugin.myprojetct.processmanager.executor.callback.customExporter',
 'name' => 'exportEasyCatalog'
 ]
 *
 * @package ProcessManager\Executor\Callback
 */
class General extends AbstractCallback
{
    public function __construct($config)
    {
        parent::__construct($config);
        if (!$this->getExtJsClass()) {
            throw new \Exception('Please set the extJsClass');
        }
        if (!$this->getName()) {
            throw new \Exception("Please set a 'name'");
        }
    }
}
