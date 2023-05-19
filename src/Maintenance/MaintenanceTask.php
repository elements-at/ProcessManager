<?php

namespace Elements\Bundle\ProcessManagerBundle\Maintenance;

use Elements\Bundle\ProcessManagerBundle\ElementsProcessManagerBundle;
use Elements\Bundle\ProcessManagerBundle\Maintenance;
use Pimcore\Maintenance\TaskInterface;
use Twig\Environment;

class MaintenanceTask implements TaskInterface
{
    /**
     * @var Environment
     */
    protected $renderingEngine;

    /**
     * SystemEventsListener constructor.
     */
    public function __construct(Environment $renderingEngine)
    {
        $this->renderingEngine = $renderingEngine;
    }

    public function execute()
    {
        if (!ElementsProcessManagerBundle::isInstalled()) {
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
