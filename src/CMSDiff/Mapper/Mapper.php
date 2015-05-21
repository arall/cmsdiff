<?php

namespace Arall\CMSDiff\Mapper;

use InvalidArgumentException;

class Mapper
{
    /**
     * Path.
     *
     * @var string
     */
    private $path;

    /**
     * Allowed file extensions.
     *
     * @var array
     */
    private $extensions = array('js', 'css', 'xml', 'txt', 'ini', 'md', 'html', 'htm', 'jpeg', 'jpg', 'png', 'gif', 'svg', 'woff', 'eot', 'ttf', 'less');

    /**
     * Product name.
     *
     * @var string
     */
    private $product;

    /**
     * Files.
     *
     * @var array
     */
    private $files;

    /**
     * Construct.
     *
     * @param string $path
     * @param string $product Product name
     *
     * @throws InvalidArgumentException
     */
    public function __construct($path, $product)
    {
        // Folder exist?
        if (!is_dir($path)) {
            throw new InvalidArgumentException('Path '.$path.' not found!');
        }

        $this->path = $path;
        $this->product = $product;
    }

    /**
     * Scan versions folders in path.
     *
     * @return bool
     */
    public function scan()
    {
        $scan = scandir($this->path);
        foreach ($scan as $key => $value) {
            if (!in_array($value, array('.', '..', '.cache'))) {
                if (is_dir($this->path.DIRECTORY_SEPARATOR.$value)) {
                    $this->files[$this->product.' - '.$value] = $this->map($value);
                }
            }
        }

        return !empty($this->files);
    }

    /**
     * Map files into files array.
     *
     * @param string $version
     * @param string $path
     *
     * @return array
     */
    private function map($version, $path = '')
    {
        $basePath = $this->path.DIRECTORY_SEPARATOR.$version;
        $fullPath = $basePath.$path;

        $files = array();
        $scan = scandir($fullPath);
        foreach ($scan as $key => $value) {
            $valuePath = $path.DIRECTORY_SEPARATOR.$value;
            if (!in_array($value, array('.', '..'))) {

                // Is a directory?
                if (is_dir($fullPath.DIRECTORY_SEPARATOR.$value)) {
                    $result = $this->map($version, $valuePath);
                    if (!empty($result)) {
                        $files = array_merge($files, $result);
                    }

                // Is a file?
                } elseif (in_array(pathinfo($value, PATHINFO_EXTENSION), $this->extensions)) {
                    $file = $valuePath;
                    $files[$file] = md5(file_get_contents($basePath.$file));
                }
            }
        }

        return $files;
    }

    /**
     * Generate a Map file.
     *
     * @param string $output
     *
     * @return bool
     */
    public function save($output)
    {
        return MapLoader::save($this->files, $output);
    }
}
