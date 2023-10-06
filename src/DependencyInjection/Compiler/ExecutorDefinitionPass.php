<?php

/**
 * Created by Elements.at New Media Solutions GmbH
 *
 */

namespace Elements\Bundle\ProcessManagerBundle\DependencyInjection\Compiler;

use Elements\Bundle\ProcessManagerBundle\Enums;
use Elements\Bundle\ProcessManagerBundle\Executor;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ExecutorDefinitionPass implements CompilerPassInterface
{
    final public const SERVICE_TAG = 'pimcore.datahub.fileExport.exporter.type';

    final public const VARIABLE = '$executor';

    public function process(ContainerBuilder $container): void
    {

        $config = $container->getParameter('elements_process_manager');

        foreach(Enums\General::EXECUTOR_CLASS_TYPES as $category) {
            $config[$category] = [];
            $taggedServices = $container->findTaggedServiceIds("elements.processManager.$category");
            if ($taggedServices !== []) {
                foreach (array_keys($taggedServices) as $id) {
                    $object = $container->get($id);

                    $tmp = [
                        'name' => $object->getName(),
                        'extJsClass' => $object->getExtJsClass(),
                        'class' => $object::class,
                        'config' => $object->getConfig(),
                    ];
                    if($object instanceof Executor\Callback\AbstractCallback) {
                        $tmp['jsFile'] = $object->getJsFile();
                    }
                    $config[$category][$object->getName()] = $tmp;
                }
            }
        }
        $container->setParameter('elements_process_manager', $config);

    }
}
