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

namespace Elements\Bundle\ProcessManagerBundle\Executor\Action;

use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class Download extends AbstractAction
{
    public $name = 'download';
    public $extJsClass = 'pimcore.plugin.processmanager.executor.action.download';

    /**
     * @param $monitoringItem MonitoringItem
     * @param $actionData
     *
     * @return bool
     */
    protected function downloadFileExists($monitoringItem, $actionData){
        if ($actionData['filepath']) {
            $file = PIMCORE_PROJECT_ROOT.$actionData['filepath'];
        } else {
            $file = $monitoringItem->getLogFile();
        }
        return is_readable($file);
    }

    /**
     * @param $monitoringItem MonitoringItem
     * @param $actionData
     *
     * @return string
     */
    public function getGridActionHtml($monitoringItem, $actionData)
    {
        if ($monitoringItem->getStatus() == $monitoringItem::STATUS_FINISHED) {

            $downloadFileExists = $this->downloadFileExists($monitoringItem,$actionData);
            if ($downloadFileExists) {
                return '<a href="#" onClick="processmanagerPlugin.download('.$monitoringItem->getId(
                    ).',\''.$actionData['accessKey'].'\');" class="pimcore_icon_download process_manager_icon_download" alt="Download" title="Download">&nbsp;</a>&nbsp;';
            } else {
                return $this->trans('plugin_pm_download_file_doesnt_exist');
            }
        }
    }

    /** Performs the action
     *
     * @param MonitoringItem $monitoringItem
     * @param array $actionData
     *
     * @return BinaryFileResponse
     *
     * @throws \Exception
     */
    public function execute($monitoringItem, $actionData)
    {
        $file = PIMCORE_PROJECT_ROOT.$actionData['filepath'];
        if (is_readable($file)) {
            $response = new BinaryFileResponse($file);
            $response->headers->set('Content-Type', finfo_file(finfo_open(FILEINFO_MIME_TYPE), $file), true);
            $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, basename($file));

            return $response;
        } else {
            throw new \Exception('Download file "'.$file.'" not present');
        }
    }

    /**
     * @param $monitoringItem MonitoringItem
     * @param $actionData array
     */
    public function preMonitoringItemDeletion($monitoringItem, $actionData)
    {
        if ($actionData['deleteWithMonitoringItem']) {
            $file = \PIMCORE_PROJECT_ROOT.$actionData['filepath'];
            if (is_readable($file)) {
                unlink($file);
            }
        }
    }

    public function toJson(MonitoringItem $monitoringItem, $actionData)
    {
        $data = parent::toJson($monitoringItem, $actionData);
        if ($monitoringItem->getStatus() == $monitoringItem::STATUS_FINISHED) {
            $data['fileExists'] = $this->downloadFileExists($monitoringItem,$actionData);
        }else {
            $data['fileExists'] = false;
        }
        return $data;
    }
}
