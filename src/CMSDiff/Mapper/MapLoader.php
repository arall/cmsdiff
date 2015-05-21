<?php

namespace Arall\CMSDiff\Mapper;

use InvalidArgumentException;

class MapLoader
{
    /**
     * Load product JSON Map.
     *
     * @param path $path JSON Data file path
     */
    public static function loadFile($path)
    {
        $data = self::uncompress($path);

        $data = self::parse($data);

        return $data;
    }

    /**
     * Uncompress a map file.
     *
     * @param string $path
     *
     * @return string
     */
    private static function uncompress($path)
    {
        if (! file_exists($path)) {
            throw new InvalidArgumentException('File does not exist');
        }

        $data = '';
        $gzo = gzopen($path, 'r');
        while ($line = gzgets($gzo, 1024)) {
            $data .= $line;
        }
        gzclose($gzo);

        return $data;
    }

    /**
     * Parse uncompressed data to array.
     *
     * @param string $data
     *
     * @throws InvalidArgumentException
     *
     * @return array
     */
    private static function parse($data)
    {
        $data = json_decode($data, true);
        if (! is_array($data)) {
            throw new InvalidArgumentException('Invalid data to parse');
        }

        return $data;
    }

    /**
     * Generate JSON file.
     *
     * @param array  $data
     * @param string $output
     *
     * @return bool
     */
    public function save($data, $output)
    {
        $gzo = gzopen($output, 'w');
        gzwrite($gzo, json_encode($data));

        return gzclose($gzo);
    }
}
