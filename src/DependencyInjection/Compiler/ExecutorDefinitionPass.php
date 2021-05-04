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

namespace Elements\Bundle\ProcessManagerBundle\DependencyInjection\Compiler;

use MyCLabs\Enum\Enum;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Elements\Bundle\ProcessManagerBundle\Executor;
use Elements\Bundle\ProcessManagerBundle\Enums;
class ExecutorDefinitionPass implements CompilerPassInterface
{
    const SERVICE_TAG = 'pimcore.datahub.fileExport.exporter.type';
    const VARIABLE = '$executor';

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {

        $config = $container->getParameter('elements_process_manager');

        foreach(Enums\General::EXECUTOR_CLASS_TYPES as $category){
            $config[$category] = [];
            $taggedServices = $container->findTaggedServiceIds("elements.processManager.$category");
            if (sizeof($taggedServices)) {
                foreach ($taggedServices as $id => $tags) {
                    $object = $container->get($id);

                    $tmp = [
                        "name" => $object->getName(),
                        "extJsClass" => $object->getExtJsClass(),
                        "class" => get_class($object),
                        "config" => $object->getConfig(),
                    ];
                    if($object instanceof Executor\Callback\AbstractCallback){
                        $tmp["jsFile"] = $object->getJsFile();
                    }
                    $config[$category][$object->getName()] = $tmp;
                }
            }
        }
        $container->setParameter('elements_process_manager',$config);

    }
}
