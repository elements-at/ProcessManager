<?php

/**
 * Created by Elements.at New Media Solutions GmbH
 *
 */

namespace Elements\Bundle\ProcessManagerBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Elements\Bundle\ProcessManagerBundle\ElementsProcessManagerBundle;
use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;
use Pimcore\Migrations\BundleAwareMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20230425000002 extends BundleAwareMigration
{
    protected function getBundleName(): string
    {
        return ElementsProcessManagerBundle::BUNDLE_NAME;
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        $list = new MonitoringItem\Listing();
        $list->setOrder('DESC');
        $list->setOrderKey('id');
        foreach($list->load() as $item) {
            $isDirty = false;
            if ($actions = $item->getActions()) {
                foreach ($actions as $key => $action) {
                    if (!array_key_exists('executeAtStates', $action)) {
                        $actions[$key]['executeAtStates'] = ['finished'];
                        $isDirty = true;
                    }
                }
            }
            if ($isDirty) {
                $item->setActions($actions);
                $item->save();
            }
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {

    }
}
