<?php

namespace Payments\AccountUpdater\Cron;

use Payments\Core\Model\LoggerInterface;
use Payments\AccountUpdater\Model\Config;
use Payments\AccountUpdater\Model\Report\Processor;
use Payments\AccountUpdater\Model\Report\Downloader;

class Updater
{
    /**
     * @var bool
     */
    private static $isRunning = false;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var \Payments\AccountUpdater\Model\Report\DownloaderInterface
     */
    private $reportDownloader;

    /**
     * @var Processor
     */
    private $reportProcessor;

    /**
     * @param LoggerInterface $logger
     * @param Config $config
     * @param \Payments\AccountUpdater\Model\Report\DownloaderInterface $reportDownloader
     * @param Processor $reportProcessor
     */
    public function __construct(
        \Payments\Core\Model\LoggerInterface $logger,
        \Payments\AccountUpdater\Model\Config $config,
        \Payments\AccountUpdater\Model\Report\DownloaderInterface $reportDownloader,
        \Payments\AccountUpdater\Model\Report\Processor $reportProcessor
    ) {
        $this->logger = $logger;
        $this->config = $config;
        $this->reportDownloader = $reportDownloader;
        $this->reportProcessor = $reportProcessor;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        try {
            if (! $this->config->isActive()) {
                return $this;
            }

            if (self::$isRunning) {
                throw new \Exception('the job is already running.');
            }

            self::$isRunning = true;

            $this->logger->info('scheduled job started');

            $reportFile = $this->reportDownloader->download();
            $result = $this->reportProcessor->process($reportFile);

            $this->logger->info(
                "job finished. " .
                "Updated: {$result['updated']}, deleted: {$result['deleted']}, " .
                "skipped: {$result['skipped']}, failed: {$result['failed']}"
            );
        } catch (\Exception $e) {
            $this->logger->critical('job failed: ' . $e->getMessage());
        }

        return $this;
    }
}
