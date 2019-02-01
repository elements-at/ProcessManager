<?php

namespace Elements\Bundle\ProcessManagerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $tree = new TreeBuilder();
        $root = $tree->root("elements_process_manager");

        $root
            ->children()
                ->arrayNode("shortCutMenu")
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode("enabled")->isRequired()->defaultValue(true)->end()
                    ->end()
                ->end()
            ->end();

        return $tree;
    }
}