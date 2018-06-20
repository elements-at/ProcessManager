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

namespace Elements\Bundle\ProcessManagerBundle;

use Pimcore\Extension\Bundle\Installer\AbstractInstaller;
use Pimcore\Logger;

class Installer extends AbstractInstaller
{
    /**
     * {@inheritdoc}
     */
    public function isInstalled()
    {
        try {
            $config = ElementsProcessManagerBundle::getConfig();
        } catch (\Exception $e) {
            Logger::error($e);
        }

        return !empty($config) && is_readable(ElementsProcessManagerBundle::getVersionFile());
    }

    public function needsReloadAfterInstall()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function canBeInstalled()
    {
        return !$this->isInstalled();
    }

    /**
     * {@inheritdoc}
     */
    public function install()
    {
        Updater::getInstance()->execute();
    }
}
