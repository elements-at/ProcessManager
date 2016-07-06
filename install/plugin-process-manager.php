<?php
return [
    'general' => [
        "archive_treshold_logs" => 7, //keep monitoring items for x Days
        'executeWithMaintenance' => true //do execute with maintenance (deactivate if you set up a separate cronjob)
    ],
    'email' => [
        'recipients' => ['my-sys-admin@example.com'], //gets a reporting e-mail when a process is dead
    ],
    'executorClasses' => [
        '\ProcessManager\Executor\PimcoreCommand' => [
            // 'commandWhiteList' => ['thumbnails:videos','maintenance'] //only allow certain commands to be executed
        ],
        '\ProcessManager\Executor\CliCommand' => [], //allow Custom cli commands
        '\ProcessManager\Executor\ClassMethod' => [], //initiates a class and calls a method
    ],
    'executorActionClasses' => [
        '\ProcessManager\Executor\Action\Download' => [], //provide a download after a job has finished
    ],
    'executorCallbackClasses' => [
        '\ProcessManager\Executor\Callback\ExecutionNote' => [], //define custom callback classes which provide a user input interface before a job is started
    ]
];