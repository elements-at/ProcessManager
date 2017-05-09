<?php

namespace Elements\Bundle\ProcessManagerBundle;

use Elements\Bundle\DataLoggerBundle\Model\Configuration;
use Pimcore\Cache;
use Pimcore\Db;


class Migrator
{

    const MAPPING = array(

        '\ProcessManager\Executor\ExportToolkit' => '\Elements\Bundle\ProcessManagerBundle\Executor\ExportToolkit',
        '\ProcessManager\Executor\ClassMethod' => '\Elements\Bundle\ProcessManagerBundle\Executor\ClassMethod',
        '\ProcessManager\Executor\CliCommand' => '\Elements\Bundle\ProcessManagerBundle\Executor\CliCommand',
        '\ProcessManager\Executor\Phing' => '\Elements\Bundle\ProcessManagerBundle\Executor\Phing',
        '\ProcessManager\Executor\PimcoreCommand' => '\Elements\Bundle\ProcessManagerBundle\Executor\PimcoreCommand',

        'ProcessManager\Executor\PimcoreCommand' => '\Elements\Bundle\ProcessManagerBundle\Executor\PimcoreCommand',
        'ProcessManager\Executor\ExportToolkit' => '\Elements\Bundle\ProcessManagerBundle\Executor\ExportToolkit',

        '\ProcessManager\Executor\Logger\Application' => '\Elements\Bundle\ProcessManagerBundle\Executor\Logger\Application',
        '\ProcessManager\Executor\Logger\Console' => '\Elements\Bundle\ProcessManagerBundle\Executor\Logger\Console',
        '\ProcessManager\Executor\Logger\File' => '\Elements\Bundle\ProcessManagerBundle\Executor\Logger\File',

        '\ProcessManager\Executor\Action\Download' => '\Elements\Bundle\ProcessManagerBundle\Executor\Action\Download'
    );


    public function __construct()
    {

    }

    public function run()
    {

        $mapping = self::MAPPING;
        $db = Db::get();

        $tableName = ElementsProcessManagerBundle::TABLE_NAME_CONFIGURATION;
        $list = $db->fetchAll("select * from ".$tableName);

        foreach ($list as $item) {
            $executorClass = $item["executorClass"];
            if (isset($mapping[$executorClass])) {
                echo("map " . $executorClass . " to " . $mapping[$executorClass] . "\n");
                $executorClass = $mapping[$executorClass];
            } else if (!in_array($executorClass, $mapping)) {
                echo("do not have mapping for " . $executorClass . "\n");
            }

            $item["executorClass"] = $executorClass;

            $executorSettings = $item["executorSettings"];
            $executorSettings = json_decode($executorSettings, true);
            if ($executorSettings) {
                $loggers = $executorSettings["loggers"];
                if ($loggers) {
                    $newLoggers = array();
                    if (is_array($loggers)) {

                        foreach ($loggers as $logger) {
                            $class = $logger["class"];
                            if (isset($mapping[$class])) {
                                echo("map " . $class . " to " . $mapping[$class] . "\n");
                                $class = $mapping[$class];
                                $logger["class"] = $class;
                            } else if (!in_array($class, $mapping)) {
                                echo("do not have mapping for " . $class . "\n");
                            }

                            $newLoggers[] = $logger;
                        }
                    }

                    $executorSettings["loggers"] = $newLoggers;
                }

                $actions = $executorSettings["actions"];
                if ($actions) {
                    $newActions = array();
                    if (is_array($actions)) {

                        foreach ($actions as $action) {
                            $class = $action["class"];
                            if (isset($mapping[$class])) {
                                echo("map " . $class . " to " . $mapping[$class] . "\n");
                                $class = $mapping[$class];
                                $action["class"] = $class;
                            } else if (!in_array($class, $mapping)) {
                                echo("do not have mapping for " . $class . "\n");
                            }

                            $newActions[] = $action;
                        }
                    }

                    $executorSettings["actions"] = $newActions;
                }

                $executorSettings = json_encode($executorSettings);
                $item["executorSettings"] = $executorSettings;


            }

            $db->update($tableName, $item, array("id" => $item["id"]));
        }

        unset($tableName);
        unset($list);
        unset($item);
        unset($executorClass);

        $tableName = ElementsProcessManagerBundle::TABLE_NAME_MONITORING_ITEM;
        $list = $db->fetchAll("select * from " . $tableName);

        foreach ($list as $item) {
            $loggers = $item["loggers"];
            if ($loggers) {
                $loggers = json_decode($loggers, true);
                $newLoggers = array();
                if (is_array($loggers)) {

                    foreach ($loggers as $logger) {
                        $class = $logger["class"];
                        if (isset($mapping[$class])) {
                            echo("map " . $class . " to " . $mapping[$class] . "\n");
                            $class = $mapping[$class];
                            $logger["class"] = $class;
                        } else if (!in_array($class, $mapping)) {
                            echo("do not have mapping for " . $class . "\n");
                        }

                        $newLoggers[] = $logger;
                    }
                }
                $newLoggers = json_encode($newLoggers);
                $item["loggers"] = $newLoggers;
            }

            $db->update($tableName, $item, array("id" => $item["id"]));
        }

        Cache::clearAll();

    }
}
