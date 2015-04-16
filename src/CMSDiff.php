<?php

namespace Arall;

class CMSDiff
{
    /**
     * Folder.
     *
     * @var string
     */
    private $folder;

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
    public $product;

    /**
     * Files map.
     *
     * @var array
     */
    private $map = array();

    /**
     * Construct.
     *
     * @param string $folder
     * @param string $product Product name
     *
     * @throws InvalidArgumentException
     */
    public function __construct($folder, $product)
    {
        // Folder exist?
        if (!is_dir($folder)) {
            throw new \InvalidArgumentException('Folder '.$folder.' not found!');
        }

        $this->folder = $folder;
        $this->product = $product;

        $this->scan();
    }

    /**
     * Scan versions folders.
     *
     * @return bool
     */
    private function scan()
    {
        $scan = scandir($this->folder);
        foreach ($scan as $key => $value) {
            if (!in_array($value, array('.', '..', '.cache'))) {
                if (is_dir($this->folder.DIRECTORY_SEPARATOR.$value)) {
                    $this->map[$this->product.' - '.$value] = $this->map($value);
                }
            }
        }
    }

    /**
     * Map files into map array.
     *
     * @param string $version
     * @param string $path
     *
     * @return array
     */
    private function map($version, $path = '')
    {
        $basePath = $this->folder.DIRECTORY_SEPARATOR.$version;
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
     * Generate JSON file.
     *
     * @param string $output
     *
     * @return bool
     */
    public function generateJson($output)
    {
        $gzo = gzopen($output, 'w');
        gzwrite($gzo, json_encode($this->map));

        return gzclose($gzo);
    }
}
