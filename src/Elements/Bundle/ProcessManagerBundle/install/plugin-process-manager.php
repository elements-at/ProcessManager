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
    "executorClasses" => [
        [
            "class" => "\\Elements\\Bundle\\ProcessManagerBundle\\Executor\\PimcoreCommand"
        ],
        [
            "class" => "\\Elements\\Bundle\\ProcessManagerBundle\\Executor\\CliCommand"
        ],
        [
            "class" => "\\Elements\\Bundle\\ProcessManagerBundle\\Executor\\ClassMethod"
        ]
    ],
    "executorLoggerClasses" => [
        [
            "class" => "\\ProcessManager\\Executor\\Logger\\File"

        ],
        [
            "class" => "\\ProcessManager\\Executor\\Logger\\Console"
        ],
        [
            "class" => "\\ProcessManager\\Executor\\Logger\\Application"
        ]
    ],
    "executorActionClasses" => [
        [
            "class" => "\\Elements\\Bundle\\ProcessManagerBundle\\Executor\\Action\\Download"
        ]
    ],
    "executorCallbackClasses" => [
        [
            "class" => "\\Elements\\Bundle\\ProcessManagerBundle\\Executor\\Callback\\ExecutionNote"
        ]
    ]
];
