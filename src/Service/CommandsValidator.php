<?php

namespace Elements\Bundle\ProcessManagerBundle\Service;

use Elements\Bundle\ProcessManagerBundle\ExecutionTrait;

class CommandsValidator
{
    protected string $strategy;

    protected array $whiteList = [];
    protected array $blackList = [];

    public function __construct(string $strategy = "default", array $whiteList = [], array $blackList = [])
    {
        $this->setStrategy($strategy);
        $this->setWhiteList($whiteList);
        $this->setBlackList($blackList);
    }


    public function getValidCommands()
    {

        $application = new \Pimcore\Console\Application(\Pimcore::getKernel());
        $commands = $this->{"getCommands" . ucfirst($this->getStrategy())}($application->all());

        ksort($commands);
        return $commands;
    }

    protected function getCommandsAll($commands){
        return $commands;
    }

    protected function getCommandsDefault($commands)
    {
        $validCommands = [];

        /**
         * @var \Symfony\Component\Console\Command\Command
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

    protected function classUsesTraits($class, $autoload = true)
    {
        $traits = [];

        // Get traits of all parent classes
        do {
            $traits = array_merge(class_uses($class, $autoload), $traits);
        } while ($class = get_parent_class($class));

        // Get traits of all parent traits
        $traitsToSearch = $traits;
        while (!empty($traitsToSearch)) {
            $newTraits = class_uses(array_pop($traitsToSearch), $autoload);
            $traits = array_merge($newTraits, $traits);
            $traitsToSearch = array_merge($newTraits, $traitsToSearch);
        };

        foreach ($traits as $trait => $same) {
            $traits = array_merge(class_uses($trait, $autoload), $traits);
        }

        return array_unique($traits);
    }

    /**
     * @return string
     */
    public function getStrategy(): string
    {
        return $this->strategy;
    }

    /**
     * @param string $strategy
     * @return $this
     */
    public function setStrategy($strategy)
    {
        $this->strategy = $strategy;
        return $this;
    }

    /**
     * @return array
     */
    public function getWhiteList(): array
    {
        return $this->whiteList;
    }

    /**
     * @param array $whiteList
     * @return $this
     */
    public function setWhiteList($whiteList)
    {
        $this->whiteList = $whiteList;
        return $this;
    }

    /**
     * @return array
     */
    public function getBlackList(): array
    {
        return $this->blackList;
    }

    /**
     * @param array $blackList
     * @return $this
     */
    public function setBlackList($blackList)
    {
        $this->blackList = $blackList;
        return $this;
    }
}
