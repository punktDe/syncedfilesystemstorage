<?php
declare(strict_types=1);

namespace PunktDe\SyncedFileSystemStorage\ResourceManagement\Storage;

/*
 *  (c) 2022-2025 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\ResourceManagement\PersistentResource;
use Neos\Flow\ResourceManagement\Storage\WritableFileSystemStorage;
use Neos\Utility\Files;
use PunktDe\SyncedFileSystemStorage\Service\SyncService;

class SyncedFileSystemStorage extends WritableFileSystemStorage
{

    #[Flow\Inject]
    protected SyncService $syncService;

    public function importResource($source, $collectionName)
    {
        $resource = parent::importResource($source, $collectionName);

        $this->syncService->syncResourcePath($this->getStoragePathAndFilenameByHash($resource->getSha1()));

        return $resource;
    }

    public function deleteResource(PersistentResource $resource)
    {
        $resourcePath = $this->getStoragePathAndFilenameByHash($resource->getSha1());

        $deleteResult = parent::deleteResource($resource);

        $relativePathFromResourceRoot = trim(str_replace($this->path, '', $resourcePath), DIRECTORY_SEPARATOR);
        $pathParts = explode(DIRECTORY_SEPARATOR, $relativePathFromResourceRoot);

        $syncPath = $this->path;

        foreach ($pathParts as $pathPart) {
            $previousSyncPath = $syncPath;
            $syncPath = Files::concatenatePaths([$syncPath, $pathPart]);

            if (!file_exists($syncPath)) {
                $this->syncService->syncResourcePath($previousSyncPath);
            }

            break;
        }

        return $deleteResult;
    }

    public function importResourceFromContent($content, $collectionName)
    {
        $resource = parent::importResourceFromContent($content, $collectionName);

        $this->syncService->syncResourcePath($this->getStoragePathAndFilenameByHash($resource->getSha1()));

        return $resource;
    }
}
