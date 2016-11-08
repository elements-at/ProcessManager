<?php
/**
 * Created by PhpStorm.
 * User: ckogler
 * Date: 24.06.2016
 * Time: 13:35
 */

namespace ProcessManager\Executor\Action;

class Download extends AbstractAction {

    public $name = 'download';
    public $extJsClass = 'pimcore.plugin.processmanager.executor.action.download';

    /**
     * @param $monitoringItem \ProcessManager\MonitoringItem
     * @return string
     */
    public function getGridActionHtml($monitoringItem,$actionData){
        if($monitoringItem->getStatus() == $monitoringItem::STATUS_FINISHED){
            if($actionData['filepath']){
                $file = \PIMCORE_DOCUMENT_ROOT.$actionData['filepath'];
            }else{
                $file = $monitoringItem->getLogFile();
            }
            if(is_readable($file)){
                return '<a href="#" onClick="processmanagerPlugin.download('.$monitoringItem->getId().',\''.$actionData['accessKey'].'\');" class="pimcore_icon_download process_manager_icon_download" alt="Download"><img src="/pimcore/static6/img/flat-color-icons/download.svg" alt="Download" height="16"/></a>';
            }else{
                return 'Download file not present';
            }
        }
    }


    /**
     * Perfoms the action
     *
     * @param $monitoringItem \ProcessManager\MonitoringItem
     * @param $actionData array
     * @return mixed
     */
    public function execute($monitoringItem,$actionData){

        $file = PIMCORE_DOCUMENT_ROOT . $actionData['filepath'];
        if(is_readable($file)){
            header("Content-Type: " . finfo_file(finfo_open(FILEINFO_MIME_TYPE),$file), true);
            header('Content-Disposition: attachment; filename="' . basename($file) . '"');
            header("Content-Length: " . filesize($file), true); while (@ob_end_flush()) ;
            flush();
            readfile($file);
            exit;
        }else{
            throw new \Exception('Download file "'.$file.'" not present');
        }
    }

    /**
     * @param $monitoringItem \ProcessManager\MonitoringItem
     * @param $actionData array
     */
    public function preMonitoringItemDeletion($monitoringItem,$actionData){
        if($actionData['deleteWithMonitoringItem']){
            $file = \PIMCORE_DOCUMENT_ROOT.$actionData['filepath'];
            if(is_readable($file)){
                unlink($file);
            }
        }
    }

}