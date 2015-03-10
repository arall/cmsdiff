<?php

namespace Arall\CMSDiff\GitHub;

use Curl\Curl;

class Repository
{

    /**
     * GitHub API URL
     *
     * @var string
     */
    private $apiUrl = 'https://api.github.com/';

    /**
     * Owner
     *
     * @var string
     */
    public $owner;

    /**
     * Repo
     *
     * @var string
     */
    public $repo;

    /**
	 * Construct
     *
     * @param  string    $name
     * @throws Exception Invalid repository
	 */
    public function __construct($name)
    {
        $tmp = explode('/', $name);
        if (count($tmp) != 2) {
            throw new \InvalidArgumentException('Invalid repository name: '.$name);
        }

        $this->owner = $tmp[0];

        $this->repo = $tmp[1];
    }

    /**
     * Get tags
     *
     * @return array
     */
    public function getTags()
    {
        $tags = array();
        $page = 1;

        do {
            $result = $this->apiCall('repos/' . $this->owner . '/' . $this->repo . '/tags?page=' . $page);
            if (!empty($result)) {
                $tags = array_merge($tags, $result);
                $page++;
            } else {
                break;
            }
        } while (true);

        return $tags;
    }

    /**
     * Download a release
     *
     * @param  string $tagName
     * @param  string $path
     * @return bool
     */
    public function downloadRelease($tagName, $path)
    {
        // Path exist?
        if (!is_dir($path)) {
            mkdir($path, 0700, true);
        }

        // Cache path
        $cachePath = $path . DIRECTORY_SEPARATOR . '.cache';
        if (!is_dir($cachePath)) {
            mkdir($cachePath, 0700, true);
        }

        // Prepar download URL
        $fileName = $tagName.'.zip';
        $url = 'https://github.com/' . $this->owner . '/' . $this->repo . '/archive/' . $fileName;

        // Destination file path
        $filePath = $cachePath . DIRECTORY_SEPARATOR . $fileName;
        $folderPath = $path . DIRECTORY_SEPARATOR . $tagName;

        // Check cache
        if (!file_exists($filePath)) {
            // Download
            if (!file_put_contents($filePath, file_get_contents($url))) {
                // Download error
                throw new \Exception('Download error');

                return false;
            }
        }

        // Check if folder already exist
        if (!is_dir($folderPath)) {

            // Unzip
            try {

                // Unzip
                $zip = new \ZipArchive();
                $zip->open($filePath);
                $zip->extractTo($path);
                $zip->close();

                // Get the (unknown) unziped folder name
                $folder = $this->getLastFolder($path);

                // Rename unziped folder
                rename($path . DIRECTORY_SEPARATOR . $folder, $folderPath);

                return true;

            // Unzip Error
            } catch (\Exception $e) {
                throw new \Exception('Unzip error: '.$e->getMessage());

                return false;
            }
        }

        return true;
    }

    /**
     * API Call
     *
     * @param  string    $path
     * @throws Exception HTTP Error
     * @return
     */
    private function apiCall($path)
    {
        $curl = new Curl();
        $curl->get($this->apiUrl . $path);
        if ($curl->error) {
            $curl->close();

            throw new \Exception("HTTP Error: " . $curl->error_code . ': ' . $curl->error_message);

        }
        $response = $curl->response;
        $curl->close();

        return $response;
    }

    /**
     * Get last created folder
     *
     * @param  string $path
     * @return string
     */
    private function getLastFolder($path)
    {
        $latest_ctime = 0;
        $latest_filename = '';

        $d = dir($path);
        while (false !== ($entry = $d->read())) {
            $filepath = "{$path}/{$entry}";
            if (is_dir($filepath) && !in_array($filepath, array('.', '..')) && filectime($filepath) > $latest_ctime) {
                $latest_ctime = filectime($filepath);
                $latest_filename = $entry;
            }
        }

        return $latest_filename;
    }

}
