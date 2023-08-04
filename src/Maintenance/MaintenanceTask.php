<?php

/**
 * Created by Elements.at New Media Solutions GmbH
 *
 */

namespace Elements\Bundle\ProcessManagerBundle\Maintenance;

use Elements\Bundle\ProcessManagerBundle\ElementsProcessManagerBundle;
use Elements\Bundle\ProcessManagerBundle\Installer;
use Elements\Bundle\ProcessManagerBundle\Maintenance;
use Pimcore\Maintenance\TaskInterface;
use Twig\Environment;

class MaintenanceTask implements TaskInterface
{
    protected \Twig\Environment $renderingEngine;

    /**
     * SystemEventsListener constructor.
     */
    public function __construct(Environment $renderingEngine, private readonly Installer $installer)
    {
        $this->renderingEngine = $renderingEngine;
    }

    public function execute(): void
    {
        if (!$this->installer->isInstalled()) {
            return;
        }

        $config = ElementsProcessManagerBundle::getConfiguration();
        if ($config['general']['executeWithMaintenance']) {
            ElementsProcessManagerBundle::initProcessManager(
                null,
                ElementsProcessManagerBundle::getMaintenanceOptions()
            );
            $maintenance = new Maintenance($this->renderingEngine);
            $maintenance->execute();
        }
    }
}
