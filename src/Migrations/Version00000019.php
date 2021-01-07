<?php

namespace Elements\Bundle\ProcessManagerBundle\Migrations;

use Elements\Bundle\ProcessManagerBundle\ElementsProcessManagerBundle;
use Doctrine\DBAL\Schema\Schema;
use Pimcore\Migrations\Migration\AbstractPimcoreMigration;

/**
 * Update translations
 */
class Version00000019 extends AbstractPimcoreMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // Removed per https://github.com/elements-at/ProcessManager/pull/78
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
    }
}
