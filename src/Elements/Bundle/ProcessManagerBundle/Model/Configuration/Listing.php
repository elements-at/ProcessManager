<?php

namespace Elements\Bundle\ProcessManagerBundle\Model\Configuration;


use Elements\Bundle\ProcessManagerBundle\Model\Configuration;
use Pimcore\Model;

/**
 * Class Listing
 *
 * @method \Elements\Bundle\ProcessManagerBundle\Model\Configuration\Listing\Dao getDao()
 * @method Configuration[] load()
 */
class Listing extends Model\Listing\AbstractListing {

    /**
     * @var null | \Pimcore\Model\User
     */
    protected $user;


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
