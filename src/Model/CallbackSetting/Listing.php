<?php

/**
 * Created by Elements.at New Media Solutions GmbH
 *
 */

namespace Elements\Bundle\ProcessManagerBundle\Model\CallbackSetting;

use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;
use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem\Listing\Dao;
use Pimcore\Model\Listing\AbstractListing;

/**
 * @method Dao getDao()
 * @method MonitoringItem[] load()
 * @method int getTotalCount()
 */
class Listing extends AbstractListing
{
    /**
     * Tests if the given key is a valid order key to sort the results
     *
     * @param mixed $key
     *
     * @return bool
     *
     * @todo remove the dummy-always-true rule
     *
     */
    public function isValidOrderKey(mixed $key): bool
    {
        return true;
    }
}
