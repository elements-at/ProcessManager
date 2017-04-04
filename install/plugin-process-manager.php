<?php
$systemConfig = \Pimcore\Config::getSystemConfig()->toArray();
return [
    'general' => [
        "archive_treshold_logs" => 7, //keep monitoring items for x Days
        'executeWithMaintenance' => true, //do execute with maintenance (deactivate if you set up a separate cronjob)
        "processTimeoutMinutes" => 15
    ],
    'email' => [
        'recipients' => explode(';',(string)$systemConfig['applicationlog']['mail_notification']['mail_receiver']), //gets a reporting e-mail when a process is dead
    ],
    'executorClasses' => [
        '\ProcessManager\Executor\PimcoreCommand' => [
            // 'commandWhiteList' => ['thumbnails:videos','maintenance'] //only allow certain commands to be executed
        ],
        '\ProcessManager\Executor\CliCommand' => [], //allow Custom cli commands
        '\ProcessManager\Executor\ClassMethod' => [], //initiates a class and calls a method
    ],
    'executorLoggerClasses' => [
        '\ProcessManager\Executor\Logger\File' => [], //logs messages to a file
        '\ProcessManager\Executor\Logger\Console' => [], //logs message to phpstdout -> console
        '\ProcessManager\Executor\Logger\Application' => [] //logs messages to the application logger
    ],
    'executorActionClasses' => [
        '\ProcessManager\Executor\Action\Download' => [], //provide a download after a job has finished
    ],
    'executorCallbackClasses' => [
        '\ProcessManager\Executor\Callback\ExecutionNote' => [], //define custom callback classes which provide a user input interface before a job is started
    ]
];