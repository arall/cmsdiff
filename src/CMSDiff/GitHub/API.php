<?php

namespace Arall\CMSDiff\GitHub;

use Curl\Curl;
use Exception;

class API
{
    /**
     * GitHub API URL.
     */
    const URL = 'https://api.github.com/';

    /**
     * API Call.
     *
     * @param string $path
     *
     * @throws Exception HTTP Error
     *
     * @return string
     */
    public static function call($path)
    {
        $curl = new Curl();
        $curl->get(self::URL.$path);
        $curl->close();

        if ($curl->error) {
            throw new Exception('HTTP Error: '.$curl->error_code.': '.$curl->error_message);
        }
        $response = $curl->response;

        return $response;
    }
}
