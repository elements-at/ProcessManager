<?php

namespace ProcessManager\CallbackSetting;

/**
 * Class Listing
 *
 * @method \ProcessManager\MonitoringItem\Listing\Dao getDao()
 * @method \ProcessManager\MonitoringItem[] load()
 */

class Listing extends \Pimcore\Model\Listing\AbstractListing {

    /**
     * Tests if the given key is an valid order key to sort the results
     *
     * @todo remove the dummy-always-true rule
     * @return boolean
     */
    public function isValidOrderKey($key) {
        return true;
    }

}
