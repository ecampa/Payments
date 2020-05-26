<?php
namespace Payments\AccountUpdater\Model\Report;

interface DownloaderInterface
{
    /**
     * @param string|null $reportDate
     *
     * @return string Path to report file
     * @throws \Exception
     */
    public function download($reportDate = null);
}
