<?php

declare(strict_types=1);

namespace Elements\Bundle\ProcessManagerBundle;

use Elements\Bundle\ProcessManagerBundle\ElementsProcessManagerBundle;
use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;
use Monolog\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;

trait MonitoringTrait
{
    /**
     * For use in:
     * @see Command::configure()
     */
    protected function addMonitoringItemIdOption(): static
    {
        $this->addOption(
            'monitoring-item-id',
            null,
            InputOption::VALUE_REQUIRED,
            'Contains the monitoring item if executed via the Pimcore backend'
        );
        return $this;
    }

    /**
     * For use in:
     * @see Command::configure()
     */
    protected function addMonitoringItemParentIdOption(): static
    {
        $this->addOption(
            'monitoring-item-parent-id', null,
            InputOption::VALUE_REQUIRED,
            'Contains the parent monitoring item id. If present - it is the child process'
        );
        return $this;
    }

    protected function startSteps(
        string|\Stringable|null $message = null,
        int $total,
        int $current = 1,
        int|false $logLevel = Logger::NOTICE
    ): MonitoringItem {
        return $this->updateStep($message, $total, $current, $logLevel);
    }

    protected function updateStep(
        string|\Stringable|null $message = null,
        ?int $total = null,
        ?int $current = null,
        int|false $logLevel = Logger::NOTICE
    ): MonitoringItem {
        $monitoringItem = $this->setMessageOptionally($message, $logLevel);
        if ($total !== null) {
            $monitoringItem->setTotalSteps($total);
        }

        return $monitoringItem
            ->setCurrentStep($current ?? ($monitoringItem->getCurrentStep() + 1))
            ->save();
    }

    protected function startWorkload(
        string|\Stringable|null $message,
        int $total,
        int $current = 0,
        int|false $logLevel = Logger::NOTICE,
        string|\Stringable|null $itemType = null,
    ): MonitoringItem {
        return $this->updateWorkload($message, $total, $current, $logLevel);
    }

    protected function updateWorkload(
        string|\Stringable|null $message = null,
        ?int $total = null,
        ?int $current = null,
        int|false $logLevel = Logger::NOTICE,
        string|\Stringable|null $itemType = null
    ): MonitoringItem {
        $monitoringItem = $this->setMessageOptionally($message, $logLevel, $itemType);
        if ($total !== null) {
            $monitoringItem->setTotalWorkload($total);
        }

        return $monitoringItem
            ->setCurrentWorkload($current ?? ($monitoringItem->getCurrentWorkload() + 1))
            ->save();
    }

    protected function completeWorkload(
        string|\Stringable|null $message = null,
        int|false $logLevel = Logger::NOTICE
    ): MonitoringItem {
        return $this->setMessageOptionally($message, $logLevel)
            ->setWorkloadCompleted()
            ->save();
    }

    protected function setMessageOptionally(
        string|\Stringable|null $message = null,
        int|false $logLevel = Logger::NOTICE,
        string|\Stringable|null $itemType = null
    ): MonitoringItem {
        $monitoringItem = $this->getMonitoringItemInstance();
        if ($message !== null) {
            $monitoringItem->setMessage($message, $logLevel);
        } elseif ($itemType !== null) {
            $monitoringItem->setDefaultProcessMessage($itemType, $logLevel);
        }
        return $monitoringItem;
    }

    protected function getMonitoringItemInstance(): MonitoringItem
    {
        return ElementsProcessManagerBundle::getMonitoringItem();
    }
}
