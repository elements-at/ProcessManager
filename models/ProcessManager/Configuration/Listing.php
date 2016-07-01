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

    public function isValidOrderKey($key)
    {
        return true;
    }
}
