<?php

/**
 * Created by Elements.at New Media Solutions GmbH
 *
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
