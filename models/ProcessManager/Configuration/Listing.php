<?php
/**
 * Created by PhpStorm.
 * User: ckogler
 * Date: 21.06.2016
 * Time: 14:57
 */
namespace ProcessManager\Configuration;


use Pimcore\Model;

/**
 * Class Listing
 *
 * @method \ProcessManager\Configuration\Listing\Dao getDao()
 * @method \ProcessManager\Configuration[] load()
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
