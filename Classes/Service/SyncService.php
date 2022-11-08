<?php
declare(strict_types=1);

namespace PunktDe\SyncedFileSystemStorage\Service;

/*
 *  (c) 2022 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Log\Utility\LogEnvironment;
use Psr\Log\LoggerInterface;

class SyncService
{

    /**
     * @Flow\InjectConfiguration(package="PunktDe.SyncedFileSystemStorage", path="targets")
     * @var array
     */
    protected $syncTargetConfiguration;

    /**
     * @Flow\Inject
     * @var LoggerInterface
     */
    protected $logger;

    public function syncResourcePath(string $resourcePath): void
    {

        $resourcePath = rtrim($resourcePath, DIRECTORY_SEPARATOR);

        foreach ($this->syncTargetConfiguration as $key => $syncTargetConfiguration) {

            if (!isset($syncTargetConfiguration['user'], $syncTargetConfiguration['host']) || empty($syncTargetConfiguration['user']) || empty($syncTargetConfiguration['host'])) {
                $this->logger->error('SyncedFileSystemStorage is used, but either host or user are not given for configured target #' . $key, LogEnvironment::fromMethodName(__METHOD__));
                continue;
            }

            $command = sprintf("rsync -crlEhz %s %s@%s:%s", $resourcePath, $syncTargetConfiguration['user'], $syncTargetConfiguration['host'], $resourcePath . DIRECTORY_SEPARATOR);

            $timeStarted = microtime(true);
            $lastLineReturned = exec($command);
            $syncTime = number_format((microtime(true) - $timeStarted) * 1000, 2);

            $this->logger->info(sprintf('Synced persistent resource "%s" to host "%s". Took %s ms. Output "%s"', $resourcePath, $syncTargetConfiguration['host'], $syncTime, $lastLineReturned), LogEnvironment::fromMethodName(__METHOD__));
        }
    }
}
