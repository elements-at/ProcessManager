<?php

declare(strict_types=1);

/**
 * Created by Elements.at New Media Solutions GmbH
 *
 */

namespace Elements\Bundle\ProcessManagerBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Elements\Bundle\ProcessManagerBundle\ElementsProcessManagerBundle;
use Pimcore\Migrations\BundleAwareMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230321092750 extends BundleAwareMigration
{
    protected function getBundleName(): string
    {
        return ElementsProcessManagerBundle::BUNDLE_NAME;
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'ALTER TABLE bundle_process_manager_monitoring_item ADD `messengerPending` TINYINT(4) NOT NULL DEFAULT "0" after `published`'
        );

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
