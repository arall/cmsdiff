<?php

namespace Arall\CMSDiff\Downloader;

use ZipArchive;
use Exception;

class Downloader
{
    /**
     * Source provider.
     *
     * @var Arall\CMSDiff\Downloader\Provider
     */
    private $provider;

    /**
     * Owner.
     *
     * @var string
     */
    public $owner;

    /**
     * Repo.
     *
     * @var string
     */
    public $repo;

    /**
     * Destination path.
     *
     * @var string
     */
    private $path;

    /**
     * Cache path.
     *
     * @var string
     */
    private $cachePath;

    /**
     * @param Arall\CMSDiff\Downloader\Provider $provider
     * @param string                            $path
     */
    public function __construct(Provider $provider, $path)
    {
        // Provider
        $this->provider = $provider;

        // Paths
        $this->path = $path;
        $this->cachePath = $this->path.'/.cache/';

        // Make dir, including .cache subdir
        $this->mkdir($this->cachePath);
    }

    /**
     * Download a release.
     *
     * @param string $releaseName
     *
     * @return bool
     */
    public function download($releaseName)
    {
        // Download
        $file = $this->provider->downloadRelease($releaseName, $this->cachePath);

        // Unzip
        return $this->unzip($file, $this->path.'/'.$releaseName);
    }

    /**
     * Make a dir if doesn't exist.
     *
     * @return bool
     */
    private function mkdir($path)
    {
        if (!is_dir($path)) {
            return mkdir($path, 0700, true);
        }

        return true;
    }

    /**
     * Unzip a release.
     *
     * @param string $file
     * @param string $output
     *
     * @return bool
     */
    private function unzip($file, $output)
    {
        try {

            // Unzip
            $zip = new ZipArchive();
            $zip->open($file);
            $zip->extractTo($this->path);
            $zip->close();

            // Get the (unknown) unziped folder name
            $folder = $this->getLastFolder();

            // Rename unziped folder
            rename($this->path.'/'.$folder, $output);

            return true;

        // Unzip Error
        } catch (Exception $e) {
            throw new Exception('Unzip error: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Get last created folder.
     *
     * @return string
     */
    private function getLastFolder()
    {
        $latest_ctime = 0;
        $latest_filename = '';

        $d = dir($this->path);
        while (false !== ($entry = $d->read())) {
            $filepath = $this->path.'/'.$entry;
            if (is_dir($filepath) && !in_array($entry, array('.', '..', '.cache')) && filectime($filepath) > $latest_ctime) {
                $latest_ctime = filectime($filepath);
                $latest_filename = $entry;
            }
        }

        return $latest_filename;
    }
}
