<?php

namespace Arall;

class CMSDiff
{
    /**
     * Folder
     *
     * @var string
     */
    private $folder;

    /**
     * Allowed file extensions
     *
     * @var array
     */
    private $extensions = array('js', 'css', 'xml', 'txt', 'ini', 'md', 'html', 'htm', 'jpeg', 'jpg', 'png', 'gif', 'svg', 'woff', 'eot', 'ttf', 'less');

    /**
     * Files map
     *
     * @var array
     */
    public $map = array();

    /**
     * Unique files map
     *
     * @var array
     */
    public $uniqueMap = array();

    /**
	 * Construct
     *
     * @param  string                   $folder
     * @throws InvalidArgumentException
	 */
    public function __construct($folder)
    {
        // Folder exist?
        if (!is_dir($folder)) {
            throw new \InvalidArgumentException('Folder ' . $folder . ' not found!');
        }

        $this->folder = $folder;

        $this->scan();

        /*if (!empty($this->map)) {
            foreach ($this->map as $version => $files) {
                $this->uniqueMap[$version] = $this->compare($version);
            }
        }*/
    }

    /**
     * Scan versions folders
     *
     * @return bool
     */
    private function scan()
    {
        $scan = scandir($this->folder);
        foreach ($scan as $key => $value) {
            if (!in_array($value, array('.', '..', '.cache'))) {
                if (is_dir($this->folder . DIRECTORY_SEPARATOR . $value)) {
                    $this->map[$value] = $this->map($value);
                }
            }
        }
    }

    /**
     * Map files into map array
     *
     * @param  string $version
     * @param  string $path
     * @return array
     */
    private function map($version, $path = '')
    {
        $basePath = $this->folder . DIRECTORY_SEPARATOR . $version;
        $fullPath = $basePath . $path;

        $files = array();
        $scan = scandir($fullPath);
        foreach ($scan as $key => $value) {
            $valuePath = $path . DIRECTORY_SEPARATOR . $value;
            if (!in_array($value, array('.', '..'))) {

                // Is a directory?
                if (is_dir($fullPath . DIRECTORY_SEPARATOR . $value)) {
                    $result = $this->map($version, $valuePath);
                    if (!empty($result)) {
                        $files = array_merge($files, $result);
                    }

                // Is a file?
                } elseif (in_array(pathinfo($value, PATHINFO_EXTENSION), $this->extensions) ) {
                    $file = $valuePath;
                    $files[$file] = md5(file_get_contents($basePath . $file));
                }
            }
        }

        return $files;
    }

    /**
     * Compare version files and get unique files
     *
     * @return bool
     */
    private function compare($version)
    {
        if (isset($this->map[$version]) && !empty($this->map[$version])) {

            // All files are unique
            $uniques = $this->map[$version];

            // Loop files
            foreach ($this->map[$version] as $file => $hash) {

                // Compare hash to other versions files
                foreach ($this->map as $compareVersion => $files) {

                    // Ignore same version
                    if ($compareVersion != $version) {
                        foreach ($files as $compareHash) {

                            // File is no more unique
                            if ($hash == $compareHash) {
                                unset($uniques[$file]);
                            }
                        }
                    }
                }
            }

            return $uniques;
        }
    }
}
