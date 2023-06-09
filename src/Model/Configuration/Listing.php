<?php

/**
 * Elements.at
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) elements.at New Media Solutions GmbH (https://www.elements.at)
 *  @license    http://www.pimcore.org/license     GPLv3 and PEL
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
    /**
     * @var null | \Pimcore\Model\User
     */
    protected $user;

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
