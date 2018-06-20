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

use Pimcore\Cache\Runtime;

trait ExecutionTraitClass
{
    use ExecutionTrait;

    /**
     * @return \Monolog\Logger
     */
    public function getLogger()
    {
        if (Runtime::isRegistered('process_manager_logger')) {
            return Runtime::get('process_manager_logger');
        }
    }
}
