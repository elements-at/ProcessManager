<?php

/**
 * Created by Elements.at New Media Solutions GmbH
 *
 */

namespace Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;

use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;

/**
 * Class Listing
 *
 * @method \Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem\Listing\Dao getDao()
 * @method MonitoringItem[] load()
 * @method int getTotalCount()
 */
class Listing extends \Pimcore\Model\Listing\AbstractListing
{
    protected ?\Pimcore\Model\User $user = null;

    /**
     * Tests if the given key is a valid order key to sort the results
     *
     * @todo remove the dummy-always-true rule
     *
     * @param mixed $key
     *
     * @return bool
     */
    public function isValidOrderKey(mixed $key): bool
    {
        return true;
    }

    public function getUser(): ?\Pimcore\Model\User
    {
        return $this->user;
    }

    /**
     * @return $this
     */
    public function setUser(?\Pimcore\Model\User $user)
    {
        $this->user = $user;

        return $this;
    }
}
