<?php

/**
 * Created by Elements.at New Media Solutions GmbH
 *
 */

namespace Elements\Bundle\ProcessManagerBundle\Service;

use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;
use Symfony\Component\HttpFoundation\Request;

class UploadManger
{
    public function saveUploads(Request $request, MonitoringItem $monitoringItem): void
    {
        $files = $request->files->all();

        $callbackSettings = $monitoringItem->getCallbackSettings();
        $hasUploads = false;
        /**
         * @var \Symfony\Component\HttpFoundation\File\UploadedFile $upload
         */
        foreach ($files as $key => $upload) {
            if ($callbackSettings[$key] ?? null) {
                $hasUploads = true;
                if ($monitoringItem->getId() === 0) {
                    $monitoringItem->save();
                }

                $secureFileName = $key . '.dat';
                $uploadDir = self::getUploadDir($monitoringItem->getId());
                $target = $upload->move($uploadDir, $secureFileName);
                $callbackSettings[$key] = [
                    'file' => $target->getRealPath(),
                    'originalName' => $upload->getClientOriginalName(),
                ];
            }
        }
        if ($hasUploads) {
            $monitoringItem->setCallbackSettings($callbackSettings)->save();
        }
    }

    public static function getUploadDir(int $id): string
    {
        return \PIMCORE_SYSTEM_TEMP_DIRECTORY . '/process-manager-uploads/' . $id;
    }
}
