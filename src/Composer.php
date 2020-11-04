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

namespace Elements\Bundle\ProcessManagerBundle;

use Composer\Script\Event;

class Composer extends \Pimcore\Composer
{
    const BUNDLE_NAME = 'ElementsProcessManagerBundle';

    public static function executeMigrationsUp(Event $event)
    {
        $consoleDir = static::getConsoleDir($event, 'pimcore migrations');

        if (null === $consoleDir) {
            return;
        }

        $currentVersion = null;

        try {
            $process = static::executeCommand($event, $consoleDir,
                'pimcore:migrations:status -b '.static::BUNDLE_NAME.' -o current_version', 30, false);
            $currentVersion = trim($process->getOutput());
        } catch (\Throwable $e) {
            $event->getIO()->write('<comment>Unable to retrieve current version</comment>');
        }

        try {
            $process = static::executeCommand($event, $consoleDir,
                'pimcore:migrations:status -b '.static::BUNDLE_NAME.' -o number_new_migrations', 30, false);
            $newVersions = trim($process->getOutput());
        } catch (\Throwable $e) {
            $event->getIO()->write('<comment>Unable to retrieve current migration version</comment>');
        }

        if (!empty($newVersions) && $newVersions > 0) {
            static::executeCommand($event, $consoleDir, 'pimcore:migrations:migrate -b '.static::BUNDLE_NAME.' -n');
        } else {
            $event->getIO()->write('<info>No migrations to execute. Current version is "'.$currentVersion.'"</info>', true);
        }
    }
}
