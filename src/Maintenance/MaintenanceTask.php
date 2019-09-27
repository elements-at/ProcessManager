<?php


namespace Elements\Bundle\ProcessManagerBundle\Maintenance;


use Elements\Bundle\ProcessManagerBundle\ElementsProcessManagerBundle;
use Pimcore\Maintenance\TaskInterface;
use Symfony\Component\Templating\EngineInterface;

class MaintenanceTask implements TaskInterface
{
    /**
     * @var EngineInterface
     */
    protected $renderingEngine;

    /**
     * SystemEventsListener constructor.
     *
     * @param EngineInterface $renderingEngine
     */
    public function __construct(EngineInterface $renderingEngine)
    {
        $this->renderingEngine = $renderingEngine;
    }

    public function execute()
    {
        if (!ElementsProcessManagerBundle::isInstalled()) {
            return;
        }

        $config = ElementsProcessManagerBundle::getConfig();
        if ($config['general']['executeWithMaintenance']) {
            ElementsProcessManagerBundle::initProcessManager(
                null,
                ElementsProcessManagerBundle::$maintenanceOptions
            );
            $maintenance = new \Elements\Bundle\ProcessManagerBundle\Maintenance($this->renderingEngine);
            $maintenance->execute();
        }
    }

}
