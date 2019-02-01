<?php

/**
 * Elements.at
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) elements.at New Media Solutions GmbH (https://www.elements.at)
 *  @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

$systemConfig = \Pimcore\Config::getSystemConfig()->toArray();

return [
    'general' => [
        'archive_treshold_logs' => 7, //keep monitoring items for x Days
        'executeWithMaintenance' => true, //do execute with maintenance (deactivate if you set up a separate cronjob)
        'processTimeoutMinutes' => 15, //if no update of the monitoring item is done within this amount of minutes the process is considered as "hanging"
        //'additionalScriptExecutionUsers' => ['deployer', 'vagrant'] //additional system users which are allowed to execute the scripts
        //'disableShortcutMenu' => true, //disables the shortcut menu on the left side in the admin interface
    ],
    'email' => [
        'recipients' => explode(';', (string)$systemConfig['applicationlog']['mail_notification']['mail_receiver']), //gets a reporting e-mail when a process is dead
    ],
    'executorClasses' => [
        [
            'class' => '\\Elements\\Bundle\\ProcessManagerBundle\\Executor\\PimcoreCommand'
        ],
        [
            'class' => '\\Elements\\Bundle\\ProcessManagerBundle\\Executor\\CliCommand'
        ],
        [
            'class' => '\\Elements\\Bundle\\ProcessManagerBundle\\Executor\\ClassMethod'
        ]
    ],
    'executorLoggerClasses' => [
        [
            'class' => '\\Elements\\Bundle\\ProcessManagerBundle\\Executor\\Logger\\File'

        ],
        [
            'class' => '\\Elements\\Bundle\\ProcessManagerBundle\\Executor\\Logger\\Console'
        ],
        [
            'class' => '\\Elements\\Bundle\\ProcessManagerBundle\\Executor\\Logger\\Application'
        ],
        [
            "class" => "\\Elements\\Bundle\\ProcessManagerBundle\\Executor\\Logger\\EmailSummary"
        ]
    ],
    'executorActionClasses' => [
        [
            'class' => '\\Elements\\Bundle\\ProcessManagerBundle\\Executor\\Action\\Download'
        ]
    ],
    'executorCallbackClasses' => [
        [
            'class' => '\\Elements\\Bundle\\ProcessManagerBundle\\Executor\\Callback\\ExecutionNote'
        ]
    ]
];
