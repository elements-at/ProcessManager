<?php

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
        $output->writeln("Done...");
    }
}

