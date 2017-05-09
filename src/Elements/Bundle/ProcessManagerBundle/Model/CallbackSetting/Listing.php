<?php

namespace Elements\Bundle\ProcessManagerBundle\Model\CallbackSetting;

use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;
use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem\Listing\Dao;

/**
 * Class Listing
 *
 * @method Dao getDao()
 * @method MonitoringItem[] load()
 */
class Listing extends \Pimcore\Model\Listing\AbstractListing
{

    /**
     * Tests if the given key is an valid order key to sort the results
     *
     * @todo remove the dummy-always-true rule
     * @param $key
     * @return bool
     */
    public function isValidOrderKey($key)
    {
        return true;
    }

}
