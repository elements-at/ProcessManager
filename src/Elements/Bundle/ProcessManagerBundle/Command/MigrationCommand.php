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

namespace Elements\Bundle\ProcessManagerBundle\Command;

use Elements\Bundle\ProcessManagerBundle\Migrator;
use Pimcore\Console\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrationCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('process-manager:migrate')
            ->setDescription('Process Manager - migrate pimcore v4 database to v5');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $migrator = new Migrator();
        $migrator->run();
        $output->writeln('Done...');
    }
}
