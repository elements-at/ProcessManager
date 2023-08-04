<?php

/**
 * Created by Elements.at New Media Solutions GmbH
 *
 */

namespace Elements\Bundle\ProcessManagerBundle\Executor\Action;

use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class Download extends AbstractAction
{
    public string $name = 'download';

    public string $extJsClass = 'pimcore.plugin.processmanager.executor.action.download';

    /**
     * @var string
     */
    public string $accessKey = '';

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

    public function getDeleteWithMonitoringItem(): bool
    {
        return $this->deleteWithMonitoringItem;
    }

    /**
     * @param bool $deleteWithMonitoringItem
     *
     * @return $this
     */
    public function setDeleteWithMonitoringItem($deleteWithMonitoringItem)
    {
        $this->deleteWithMonitoringItem = $deleteWithMonitoringItem;

        return $this;
    }

    public function getAccessKey(): string
    {
        return $this->accessKey;
    }

    /**
     * @param string $accessKey
     *
     * @return $this
     */
    public function setAccessKey(string $accessKey)
    {
        $this->accessKey = $accessKey;

        return $this;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param string $label
     *
     * @return $this
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    /**
     * @param string $filePath
     *
     * @return $this
     */
    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;

        return $this;
    }

    public function isAbsoluteFilePath(): bool
    {
        return $this->isAbsoluteFilePath;
    }

    public function setIsAbsoluteFilePath(bool $isAbsoluteFilePath): Download
    {
        $this->isAbsoluteFilePath = $isAbsoluteFilePath;

        return $this;
    }

    /**
     * @param array<mixed> $actionData
     *
     * @return string
     */
    protected function buildFilePath(array $actionData): string
    {
        $filePath = $actionData['filepath'];
        $isAbsoluteFilePath = $actionData['isAbsoluteFilePath'] ?? $this->isAbsoluteFilePath();

        return $isAbsoluteFilePath ? $filePath : PIMCORE_PROJECT_ROOT.$filePath;
    }

    /**
     * @param $monitoringItem MonitoringItem
     * @param array<mixed> $actionData
     *
     * @return bool
     */
    protected function downloadFileExists(MonitoringItem $monitoringItem, array $actionData): bool
    {
        $file = $actionData['filepath'] ? $this->buildFilePath($actionData) : $monitoringItem->getLogFile();

        return is_readable($file);
    }

    /**
     * @param $monitoringItem MonitoringItem
     * @param array<mixed> $actionData
     *
     * @return string
     */
    public function getGridActionHtml(MonitoringItem $monitoringItem, array $actionData): string
    {
        if (in_array($monitoringItem->getStatus(), $actionData['executeAtStates'])) {

            $downloadFileExists = $this->downloadFileExists($monitoringItem, $actionData);
            if ($downloadFileExists) {
                return '<a href="#" onClick="processmanagerPlugin.download('.$monitoringItem->getId(
                ).',\''.$actionData['accessKey'].'\');" class="pimcore_icon_download process_manager_icon_download" alt="Download" title="Download">&nbsp;</a>&nbsp;';
            } else {
                return $this->trans('plugin_pm_download_file_doesnt_exist');
            }
        }

        return '';
    }

    /** Performs the action
     *
     * @param MonitoringItem $monitoringItem
     * @param array<mixed> $actionData
     *
     * @return BinaryFileResponse
     *
     * @throws \Exception
     */
    public function execute(MonitoringItem $monitoringItem, array $actionData)
    {
        $file = $this->buildFilePath($actionData);
        if (is_readable($file)) {
            $response = new BinaryFileResponse($file);
            $response->headers->set('Content-Type', finfo_file(finfo_open(FILEINFO_MIME_TYPE), $file), true);
            $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, basename((string) $file));

            return $response;
        } else {
            throw new \Exception('Download file "'.$file.'" not present');
        }
    }

    /**
     * @param MonitoringItem $monitoringItem
     * @param array<mixed> $actionData
     *
     * @return void
     */
    public function preMonitoringItemDeletion(MonitoringItem $monitoringItem, array $actionData): void
    {
        if ($actionData['deleteWithMonitoringItem'] == true || $actionData['deleteWithMonitoringItem'] == 'on') {
            $file = $this->buildFilePath($actionData);
            if (is_readable($file) && is_file($file)) {
                unlink($file);
            }
        }
    }

    /**
     * @param MonitoringItem $monitoringItem
     * @param array<mixed> $actionData
     *
     * @return array<string, mixed>
     */
    public function toJson(MonitoringItem $monitoringItem, array $actionData): array
    {
        $data = parent::toJson($monitoringItem, $actionData);
        if (in_array($monitoringItem->getStatus(), $actionData['executeAtStates'])) {
            $data['fileExists'] = $this->downloadFileExists($monitoringItem, $actionData);
        } else {
            $data['fileExists'] = false;
        }

        return $data;
    }

    /**
     * @return array<mixed>
     */
    public function getStorageData(): array
    {
        return [
            'accessKey' => $this->getAccessKey(),
            'label' => $this->getLabel(),
            'filepath' => $this->getFilePath(),
            'deleteWithMonitoringItem' => $this->getDeleteWithMonitoringItem(),
            'isAbsoluteFilePath' => $this->isAbsoluteFilePath(),
            'executeAtStates' => $this->getExecuteAtStates(),
            'class' => self::class,
        ];
    }
}
