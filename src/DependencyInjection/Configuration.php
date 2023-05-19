<?php

/**
 * Pimcore
 *
 * This source file is available under following license:
 * - Pimcore Enterprise License (PEL)
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     PEL
 */

namespace Elements\Bundle\ProcessManagerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('elements_process_manager');

        // $debugEmailAddresses = \Pimcore\Config::getSystemConfiguration()["email"]["debug"]["email_addresses"];
        // $debugEmailAddresses = array_filter(preg_split('/,|;/',$debugEmailAddresses));

        $debugEmailAddresses = [];
        $rootNode = $treeBuilder->getRootNode();
        $rootNode
            ->children()
                ->integerNode('archiveThresholdLogs')
                    ->defaultValue(7)
                    ->min(0)
                    ->info('Defines how many days log entries are kept')
                    ->end()
                ->integerNode('processTimeoutMinutes')
                    ->defaultValue(15)
                    ->min(0)
                    ->info('If the MonitoringItem has not been save within X minutes it will be considered as hanging process')
                    ->end()
                ->booleanNode('disableShortcutMenu')
                    ->defaultValue(false)
                    ->info('Disable the shortcut menu on the left side in the Pimcore admin')
                    ->end()
                ->arrayNode('reportingEmailAddresses')
                    ->defaultValue($debugEmailAddresses)
                    ->scalarPrototype()->end()
                    ->info('Defines email addresses to which errors should be reported')
                    ->end()

                ->arrayNode('additionalScriptExecutionUsers')
                    ->scalarPrototype()->end()
                    ->info('Defines additional system users which are allowed to execute the php scripts')
                    ->end()
                ->arrayNode('restApiUsers')
                        ->arrayPrototype()->children()
                            ->scalarNode('username')->isRequired()->end()
                            ->scalarNode('apiKey')->isRequired()->end()

            ->end()
        ;

        return $treeBuilder;
    }
}
