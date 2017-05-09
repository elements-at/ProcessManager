<?php

namespace Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;

use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;

/**
 * Class Listing
 *
 * @method \Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem\Listing\Dao getDao()
 * @method MonitoringItem[] load()
 */
class Listing extends \Pimcore\Model\Listing\AbstractListing
{

    /**
     * @var null | \Pimcore\Model\User
     */
    protected $user;

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
