<?php

namespace Arall\CMSDiff\Downloader\Providers;

use Arall\CMSDiff\Downloader\Provider;
use Curl\Curl;
use Exception;

class GitHub implements Provider
{
    /**
     * GitHub API URL.
     */
    const API_URL = 'https://api.github.com/';

    /**
     * GitHub URL.
     */
    const URL = 'https://github.com/';

    /**
     * Repository owner name.
     *
     * @var string
     */
    private $owner;

    /**
     * Repository name.
     *
     * @var string
     */
    private $repo;

    /**
     * @param string $owner Repository owner name.
     * @param string $repo  Repository name
     */
    public function __construct($owner, $repo)
    {
        $this->owner = $owner;
        $this->repo = $repo;
    }

    /**
     * API Call.
     *
     * @param string $path
     *
     * @throws Exception HTTP Error
     *
     * @return string
     */
    private static function apiCall($path)
    {
        $curl = new Curl();
        $curl->get(self::API_URL.$path);
        $curl->close();

        if ($curl->error) {
            throw new Exception('HTTP Error: '.$curl->error_code.': '.$curl->error_message);
        }

        return $curl->response;
    }

    /**
     * Get repository releases.
     *
     * @return array
     */
    public function getReleases()
    {
        $tags = array();
        $page = 1;

        do {
            $result = $this->apiCall('repos/'.$this->owner.'/'.$this->repo.'/tags?page='.$page);
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
     * Download release.
     *
     * @param string $name
     * @param string $path
     *
     * @return string
     */
    public function downloadRelease($name, $path)
    {
        $fileName = $name.'.zip';
        $url = self::URL.$this->owner.'/'.$this->repo.'/archive/'.$fileName;
        $filePath = $path.$fileName;

        // Try to download
        if (file_put_contents($filePath, file_get_contents($url))) {
            return $filePath;
        }

        throw new Exception('Download error');
    }
}
