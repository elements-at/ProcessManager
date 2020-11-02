<?php

namespace Elements\Bundle\ProcessManagerBundle\Migrations;

use Elements\Bundle\ProcessManagerBundle\ElementsProcessManagerBundle;
use Doctrine\DBAL\Schema\Schema;
use Pimcore\Migrations\Migration\AbstractPimcoreMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version00000021 extends AbstractPimcoreMigration
{
    public function doesSqlMigrations(): bool
    {
        return false;
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $configFile = \Pimcore\Config::locateConfigFile('plugin-process-manager.php');
        $config = ElementsProcessManagerBundle::getConfig();

        $hasOpenItem = $hasJsEvent = false;
        foreach($config['executorActionClasses'] as $i => $e){
            if($e['class'] == '\Elements\Bundle\ProcessManagerBundle\Executor\Action\OpenItem'){
                $hasOpenItem = true;
            }
            if($e['class'] == '\Elements\Bundle\ProcessManagerBundle\Executor\Action\JsEvent'){
                $hasJsEvent = true;
            }
        }
        if($hasOpenItem == false){
            $config['executorActionClasses'][] = [
                'class' =>  '\Elements\Bundle\ProcessManagerBundle\Executor\Action\OpenItem'
            ];
        }
        if($hasJsEvent == false){
            $config['executorActionClasses'][] = [
                'class' =>  '\Elements\Bundle\ProcessManagerBundle\Executor\Action\JsEvent'
            ];
        }
        \Pimcore\File::putPhpFile($configFile, to_php_data_file_format($config));
        \Elements\Bundle\ProcessManagerBundle\Installer::updateTranslations();
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
    }
}
