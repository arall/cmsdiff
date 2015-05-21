<?php

namespace Arall\CMSDiff\Downloader;

interface Provider
{
    /**
     * Get repository releases.
     *
     * @return array
     */
    public function getReleases();

    /**
     * Download release.
     *
     * @param string $name
     * @param string $path
     *
     * @return string
     */
    public function downloadRelease($name, $path);
}
