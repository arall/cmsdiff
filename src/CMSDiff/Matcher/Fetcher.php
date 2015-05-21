<?php

namespace Arall\CMSDiff\Matcher;

use Curl\Curl;

class Fetcher
{
    /**
     * Website URL.
     *
     * @var string
     */
    private $url;

    /**
     * Cache map.
     *
     * @var array
     */
    private $cache = [];

    /**
     * @param string $url
     */
    public function __construct($url)
    {
        $this->url = $url;
    }

    /**
     * Get remote file hash.
     *
     * @param string $path
     *
     * @return string
     */
    public function fetch($path, $force = false)
    {
        $url = $this->url.$path;

        // Cached?
        if ($force || !isset($this->cache[$path])) {
            $curl = new Curl();
            $curl->setOpt(CURLOPT_RETURNTRANSFER,   true);
            $curl->setOpt(CURLOPT_AUTOREFERER,      true);
            $curl->setOpt(CURLOPT_FOLLOWLOCATION,   true);
            $curl->get($url);

            if ($curl->error) {
                return false;
            }

            $this->cache[$path] = md5($curl->response);
        }

        return $this->cache[$path];
    }
}
