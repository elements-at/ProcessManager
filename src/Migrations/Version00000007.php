<?php

namespace Elements\Bundle\ProcessManagerBundle\Migrations;

use Elements\Bundle\ProcessManagerBundle\ElementsProcessManagerBundle;
use Doctrine\DBAL\Schema\Schema;
use Pimcore\Migrations\Migration\AbstractPimcoreMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version00000007 extends AbstractPimcoreMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        //        $configFile = \Pimcore\Config::locateConfigFile("plugin-process-manager.php");
        //        $config = ElementsProcessManagerBundle::getConfig();
        //
        //        foreach ([
        //                     'executorClasses',
        //                     'executorLoggerClasses',
        //                     'executorActionClasses',
        //                     'executorCallbackClasses',
        //                 ] as $classType) {
        //            $tmp = [];
        //            foreach ($config[$classType] as $key => $value) {
        //                $value['class'] = $key;
        //                $tmp[] = $value;
        //            }
        //            $config[$classType] = $tmp;
        //        }
        //        \Pimcore\File::putPhpFile($configFile, to_php_data_file_format($config));
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
    }
}
