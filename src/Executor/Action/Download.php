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
     * @var string
     */
    public $accessKey = '';

    /**
     * @var string
     */
    public $label = '';

    /**
     * @var string
     */
    public $filePath = '';

    /**
     * @var bool
     */
    public $deleteWithMonitoringItem = false;

    /**
     * @var bool
     */
    public $isAbsoluteFilePath = false;


    /**
     * @return bool
     */
    public function getDeleteWithMonitoringItem(): bool
    {
        return $this->deleteWithMonitoringItem;
    }

    /**
     * @param bool $deleteWithMonitoringItem
     * @return $this
     */
    public function setDeleteWithMonitoringItem($deleteWithMonitoringItem)
    {
        $this->deleteWithMonitoringItem = $deleteWithMonitoringItem;
        return $this;
    }

    /**
     * @return string
     */
    public function getAccessKey(): string
    {
        return $this->accessKey;
    }

    /**
     * @param string $accessKey
     * @return $this
     */
    public function setAccessKey($accessKey)
    {
        $this->accessKey = $accessKey;
        return $this;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return $this
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return string
     */
    public function getFilePath(): string
    {
        return $this->filePath;
    }

    /**
     * @param string $filePath
     * @return $this
     */
    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAbsoluteFilePath(): bool
    {
        return $this->isAbsoluteFilePath;
    }

    /**
     * @param bool $isAbsoluteFilePath
     * @return $this
     */
    public function setIsAbsoluteFilePath(bool $isAbsoluteFilePath): Download
    {
        $this->isAbsoluteFilePath = $isAbsoluteFilePath;
        return $this;
    }

    protected function buildFilePath($actionData) {
        $filePath = $actionData['filepath'];
        $isAbsoluteFilePath = $actionData['isAbsoluteFilePath'] ?? $this->isAbsoluteFilePath();
        $file = $isAbsoluteFilePath ? $filePath : PIMCORE_PROJECT_ROOT.$filePath;
        return $file;
    }

    /**
     * @param $monitoringItem MonitoringItem
     * @param $actionData
     *
     * @return bool
     */
    protected function downloadFileExists($monitoringItem, $actionData){
        if ($actionData['filepath']) {
            $file = $this->buildFilePath($actionData);
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
        if ($monitoringItem->isFinished()) {

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
        $file = $this->buildFilePath($actionData);
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
        if ($actionData['deleteWithMonitoringItem'] == true || $actionData['deleteWithMonitoringItem'] == "on") {
            $file = $this->buildFilePath($actionData);
            if (is_readable($file) && is_file($file)) {
                unlink($file);
            }
        }
    }

    public function toJson(MonitoringItem $monitoringItem, $actionData)
    {
        $data = parent::toJson($monitoringItem, $actionData);
        if ($monitoringItem->isFinished()) {
            $data['fileExists'] = $this->downloadFileExists($monitoringItem,$actionData);
        }else {
            $data['fileExists'] = false;
        }
        return $data;
    }

    /**
     * @inheritDoc
     */
    public function getStorageData() : array
    {
        return [
            'accessKey' => $this->getAccessKey(),
            'label' => $this->getLabel(),
            'filepath' => $this->getFilePath(),
            'deleteWithMonitoringItem' => $this->getDeleteWithMonitoringItem(),
            'isAbsoluteFilePath' => $this->isAbsoluteFilePath(),
            'class' => self::class,
        ];
    }
}
