<?php

namespace Arall\CMSDiff\GitHub;

use ZipArchive;
use Exception;

class Downloader
{
    /**
     * GitHub URL.
     */
    const URL = 'https://github.com/';

    /**
     * Repository dependency.
     *
     * @var Arall\CMSDiff\GitHub\Repository
     */
    private $repository;

    /**
     * Destination path.
     *
     * @var string
     */
    private $path;

    /**
     * @param Arall\CMSDiff\GitHub\Repository $repository
     * @param string                          $path
     */
    public function __construct(Repository $repository, $path)
    {
        $this->repository = $repository;
        $this->path = $path;
        $this->cachePath = $this->path.'/.cache/';

        // Make dir, including .cache subdir
        $this->mkdir($this->cachePath);
    }

    /**
     * Download a release.
     *
     * @param string $tag
     *
     * @return bool
     */
    public function download($tag)
    {
        // Prepare the download URL
        $fileName = $tag.'.zip';
        $url = self::URL.$this->repository->owner.'/'.$this->repository->repo.'/archive/'.$fileName;

        // Destination file path
        $filePath = $this->cachePath.$fileName;
        $folderPath = $this->path.'/'.$tag;

        // Check cache
        if (!file_exists($filePath)) {
            // Download
            if (!file_put_contents($filePath, file_get_contents($url))) {
                // Download error
                throw new Exception('Download error');

                return false;
            }
        }

        // Check if folder already exist
        if (!is_dir($folderPath)) {
            // Unzip
            return $this->unzip($filePath, $folderPath);
        }

        return true;
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
