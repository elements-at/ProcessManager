<?php

namespace ProcessManager\MonitoringItem;

/**
 * Class Listing
 *
 * @method \ProcessManager\MonitoringItem\Listing\Dao getDao()
 * @method \ProcessManager\MonitoringItem[] load()
 */

class Listing extends \Pimcore\Model\Listing\AbstractListing {

    /**
     * @var null | \Pimcore\Model\User
     */
    protected $user;

    /**
     * Tests if the given key is an valid order key to sort the results
     *
     * @todo remove the dummy-always-true rule
     * @return boolean
     */
    public function isValidOrderKey($key) {
        return true;
    }


    /**
     * @return null|\Pimcore\Model\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param null|\Pimcore\Model\User $user
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }


}
