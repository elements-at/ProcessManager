<?php

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


    public function needsReloadAfterInstall(){
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
