<?php

/**
 * Created by Elements.at New Media Solutions GmbH
 *
 */

namespace Elements\Bundle\ProcessManagerBundle\Model\Configuration;

use Elements\Bundle\ProcessManagerBundle\Model\Configuration;
use Pimcore\Model;

/**
 * @method \Elements\Bundle\ProcessManagerBundle\Model\Configuration\Listing\Dao getDao()
 * @method Configuration[] load()
 * @method int getTotalCount()
 */
class Listing extends Model\Listing\AbstractListing
{
    protected ?\Pimcore\Model\User $user = null;

    /**
     * @param mixed $key
     *
     * @return bool
     */
    public function isValidOrderKey($key): bool
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
    public function setUser(?\Pimcore\Model\User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
