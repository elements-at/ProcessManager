<?php

/**
 * Created by Elements.at New Media Solutions GmbH
 *
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
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('elements_process_manager');

        $debugEmailAddresses = [];
        $rootNode = $treeBuilder->getRootNode();
        /**
         * @phpstan-ignore-next-line
         */
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
                ->integerNode('refreshIntervalSeconds')
                    ->defaultValue(3)
                    ->min(1)
                    ->info('Refresh interal of process list in seconds')
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
                ->scalarNode('configurationMigrationsDirectory')
                    ->defaultValue('%kernel.project_dir%/src/Migrations')
                    ->info('Defines the directory where the bin/console process-manager:migrations:generate creates the migration files')
                    ->end()
                ->scalarNode('configurationMigrationsNamespace')
                    ->defaultValue("App\Migrations")
                    ->info('Namespace for the configuration migrations')
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
