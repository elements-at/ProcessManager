<?php

namespace Elements\Bundle\ProcessManagerBundle\Message;

class ExecuteCommandMessage
{
    public function __construct(
        private string $command,
        private int $monitoringItemId,
        private ?string $outputFile = null
    ) {
    }

    public function getCommand(): string
    {
        return $this->command;
    }

    public function getMonitoringItemId(): int
    {
        return $this->monitoringItemId;
    }

    /**
     * @return string|null
     */
    public function getOutputFile(): ?string
    {
        return $this->outputFile;
    }
}
