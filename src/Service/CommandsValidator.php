<?php

/**
 * Created by Elements.at New Media Solutions GmbH
 *
 */

namespace Elements\Bundle\ProcessManagerBundle\Service;

use Elements\Bundle\ProcessManagerBundle\ExecutionTrait;
use Pimcore\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LazyCommand;

class CommandsValidator
{
    protected string $strategy;

    /**
     * @var array<string>
     */
    protected array $whiteList = [];

    /**
     * @var array<string>
     */
    protected array $blackList = [];

    /**
     * @param string $strategy
     * @param array<string> $whiteList
     * @param array<string> $blackList
     */
    public function __construct(string $strategy = 'default', array $whiteList = [], array $blackList = [])
    {
        $this->setStrategy($strategy);
        $this->setWhiteList($whiteList);
        $this->setBlackList($blackList);
    }

    /**
     * @return array<mixed>
     */
    public function getValidCommands(): array
    {

        $application = new Application(\Pimcore::getKernel());
        $commands = $this->{'getCommands' . ucfirst($this->getStrategy())}($application->all());

        ksort($commands);

        return $commands;
    }

    /**
     * @param array<mixed> $commands
     *
     * @return array<mixed>
     */
    protected function getCommandsAll(array $commands): array
    {
        return $commands;
    }

    /**
     * @param array<mixed> $commands
     *
     * @return array<mixed>
     */
    protected function getCommandsDefault(array $commands): array
    {
        $validCommands = [];

        /**
         * @var Command $command
         */
        foreach ($commands as $name => $command) {
            if (in_array($name, $this->getBlackList())) {
                continue;
            }

            if (in_array($name, $this->getWhiteList())) {
                $validCommands[$name] = $command;

                continue;
            }

            $useTrait = in_array(ExecutionTrait::class, $this->classUsesTraits($command));
            if ($useTrait) {
                $validCommands[$name] = $command;
            }
        }

        return $validCommands;
    }

    /**
     * @return array<string>
     */
    protected function classUsesTraits(LazyCommand | Command $class, bool $autoload = true): array
    {
        if ($class instanceof LazyCommand) {
            $class = $class->getCommand();
        }
        $traits = [];

        // Get traits of all parent classes
        do {
            $traits = array_merge(class_uses($class, $autoload), $traits);
            // @phpstan-ignore-next-line
        } while ($class = get_parent_class($class));

        // Get traits of all parent traits
        $traitsToSearch = $traits;
        while ($traitsToSearch !== []) {
            $newTraits = class_uses(array_pop($traitsToSearch), $autoload);
            $traits = array_merge($newTraits, $traits);
            $traitsToSearch = array_merge($newTraits, $traitsToSearch);
        }

        foreach (array_keys($traits) as $trait) {
            $traits = array_merge(class_uses($trait, $autoload), $traits);
        }

        return array_unique($traits);
    }

    public function getStrategy(): string
    {
        return $this->strategy;
    }

    public function setStrategy(string $strategy): static
    {
        $this->strategy = $strategy;

        return $this;
    }

    /**
     * @return array<string>
     */
    public function getWhiteList(): array
    {
        return $this->whiteList;
    }

    /**
     * @param array<string> $whiteList
     */
    public function setWhiteList(array $whiteList): static
    {
        $this->whiteList = $whiteList;

        return $this;
    }

    /**
     * @return array<string>
     */
    public function getBlackList(): array
    {
        return $this->blackList;
    }

    /**
     * @param array<string> $blackList
     */
    public function setBlackList(array $blackList): static
    {
        $this->blackList = $blackList;

        return $this;
    }
}
