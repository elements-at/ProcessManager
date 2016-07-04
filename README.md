# ProcessManager

## Introduction

> The ProcessManager allows you to manage (define,execute...) arbitrary processes/commands in the Pimcore backend. 
You can display the execution progress of the script in the Admin interface and the user can view the detailed log information. 
In addition you can define "actions" - e.g.  a download of a file after the process has finished. Furthermore callback actions are available and the processes are monitored (you get an email if a process dies)

## Installation
```
{
    "require": {
        "pimcore-plugins/ProcessManager": "~1.0"
    },
    "repositories": [
        { "type": "composer", "url": "https://composer-packages.elements.at/" }
    ]
}
```

**Be careful, normally there's already a `require` node, so you need to add the new line at the bottom**     

Run composer update: 
`composer update`

After the installation you have a config file located in /website/config/plugin-process-manager.php

By default the processes are checked when the pimcore maintenance is executed. It is advisable to set up a extra cronjob, which monitors the script execution.

Just add the following command to your crontab (and set "executeWithMaintenance" to "false" in the config file ;-))
```
*/5 * * * * php /home/tyrolit-pim/www/pimcore/cli/console.php process-manager:maintenance
```

### Configuration
The configuration is done by the file /website/config/plugin-process-manager.php
```php
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
        //'\ProcessManager\Executor\ExportToolkit' => [], //initiates a class and calls a method
    ],
    'executorActionClasses' => [
        '\ProcessManager\Executor\Action\Download' => [], //provide a download after a job has finished
    ],
    'executorCallbackClasses' => [
        '\ProcessManager\Executor\Callback\ExecutionNote' => [], //define custom callback classes which provide a user input interface before a job is started
    ]
];
```

### Development instance
Not jet available