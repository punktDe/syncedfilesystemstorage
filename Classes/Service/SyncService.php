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
    #[Flow\InjectConfiguration(path: 'targets', package: 'PunktDe.SyncedFileSystemStorage')]
    protected array $syncTargetConfiguration;

    #[Flow\Inject]
    protected LoggerInterface $logger;

    public function syncResourcePath(string $sourcePath): void
    {

        $sourcePath = rtrim($sourcePath, DIRECTORY_SEPARATOR);
        $targetPath = realpath($sourcePath);

        foreach ($this->syncTargetConfiguration as $key => $syncTargetConfiguration) {

            if (!empty(trim($syncTargetConfiguration['rootDirectory'] ?? ''))) {
                if (str_starts_with($targetPath, $syncTargetConfiguration['rootDirectory'])) {
                    $targetPath = substr($targetPath, strlen($syncTargetConfiguration['rootDirectory']));
                } else {
                    $this->logger->warning(sprintf('Root directory was set to "%s", but this path does not match the target path "%s"', $syncTargetConfiguration['rootDirectory'], $targetPath));
                }
            }

            if (!isset($syncTargetConfiguration['user'], $syncTargetConfiguration['host']) || empty($syncTargetConfiguration['user']) || empty($syncTargetConfiguration['host'])) {
                $this->logger->error('SyncedFileSystemStorage is used, but either host or user are not given for configured target #' . $key, LogEnvironment::fromMethodName(__METHOD__));
                continue;
            }

            $command = sprintf("rsync -crlEhz --delete %s %s@%s:%s", $sourcePath . DIRECTORY_SEPARATOR, $syncTargetConfiguration['user'], $syncTargetConfiguration['host'], $targetPath);

            $timeStarted = microtime(true);
            $lastLineReturned = exec($command);
            $syncTime = number_format((microtime(true) - $timeStarted) * 1000, 2);

            $this->logger->info(sprintf('Synced persistent resource "%s" to host "%s". Took %s ms. Output "%s"', $sourcePath, $syncTargetConfiguration['host'], $syncTime, $lastLineReturned), LogEnvironment::fromMethodName(__METHOD__));
        }
    }
}
