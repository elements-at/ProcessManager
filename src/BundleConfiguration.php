<?php

/**
 * Created by Elements.at New Media Solutions GmbH
 *
 */

namespace Elements\Bundle\ProcessManagerBundle;

class BundleConfiguration
{
    /**
     * @param array<mixed> $config
     */
    public function __construct(protected array $config)
    {
    }

    public function getConfigurationMigrationsNamespace(): string
    {
        return $this->config['configurationMigrationsNamespace'];
    }

    public function getConfigurationMigrationsDirectory(): string
    {
        return $this->config['configurationMigrationsDirectory'];
    }

    /**
     * @return bool
     */
    public function getDisableShortcutMenu()
    {
        return $this->config['disableShortcutMenu'];
    }

    public function getProcessTimeoutMinutes(): int
    {
        return $this->config['processTimeoutMinutes'];
    }

    /**
     * @return array<mixed>
     */
    public function getClassTypes()
    {
        $result = [];
        foreach (Enums\General::EXECUTOR_CLASS_TYPES as $type) {
            $result[$type] = $this->config[$type];
        }

        return $result;
    }

    /**
     * @return array<mixed>
     */
    public function getAdditionalScriptExecutionUsers(): array
    {
        return (array)$this->config['additionalScriptExecutionUsers'];
    }

    /**
     * @return array<mixed>
     */
    public function getReportingEmailAddresses(): array
    {
        $addresses = (array)$this->config['reportingEmailAddresses'];

        return array_filter($addresses);
    }

    public function getArchiveThresholdLogs(): int
    {
        return (int)$this->config['archiveThresholdLogs'];
    }

    /**
     * @return array<mixed>
     */
    public function getRestApiUsers(): array
    {
        return (array)$this->config['restApiUsers'];
    }
}
