<?php

namespace Arall\CMSDiff\Mapper;

class Map
{
    /**
     * Data.
     *
     * @var array
     */
    private $data = [];

    /**
     * Candidate versions.
     *
     * @var array
     */
    private $candidates = [];

    /**
     * Unmatched ignored files.
     *
     * @var array
     */
    private $ignored = [];

    /**
     * @param string $path
     */
    public function __construct($path = null)
    {
        // Path (multiple products)
        if (is_dir($path)) {
            $d = dir($path);
            // Load all files
            while (false !== ($entry = $d->read())) {
                $filepath = $path.'/'.$entry;
                if (!is_dir($filepath)) {
                    $this->load($filepath);
                }
            }
        // Single file
        } else {
            $this->load($path);
        }

        // All are possible candidades for now
        $this->candidates = array_keys($this->data);
    }

    /**
     * Load file to current data map.
     *
     * @param string $path
     *
     * @return bool
     */
    public function load($path)
    {
        $data = MapLoader::loadFile($path);

        return $this->data = array_merge($this->data, $data);
    }

    public function setCandidates($candidates)
    {
        return $this->candidates = $candidates;
    }

    public function ignoreFile($file)
    {
        return $this->ignored[] = $file;
    }

    public function getCandidates()
    {
        return $this->candidates;
    }

    /**
     * Find all matches of a file with a certain hash on map array.
     *
     * @param string $file
     * @param string $hash
     *
     * @return array
     */
    public function find($file, $hash)
    {
        $matches = [];
        // For each candidates...
        foreach ($this->candidates as $version) {
            // If file is mapped...
            if (isset($this->data[$version][$file])) {
                // If hash match...
                if ($this->data[$version][$file] == $hash) {
                    $matches[] = $version;
                    continue;
                }
            }
        }

        return $matches;
    }

    /**
     * Get next file.
     *
     * @return string
     */
    public function getNextFile()
    {
        $data = array();

        // Remove already discarded versions from the JSON
        foreach ($this->candidates as $version) {
            $data[$version] = $this->data[$version];
        }

        $num_versions = count($data);
        $all_files = array();
        $hash_dist = array();

        foreach ($data as $version => $files) {
            foreach ($files as $file => $hash) {
                array_push($all_files, $file);

                if (!array_key_exists($file, $hash_dist)) {
                    $hash_dist[$file] = array();
                }

                $hash_dist[$file][$hash] = 0;
                $hash_dist[$file][''] = 0;
            }
        }

        $all_files = array_unique($all_files);

        foreach ($all_files as $file) {
            foreach ($data as $version => $files) {
                $hash = '';
                if (array_key_exists($file, $files)) {
                    $hash = $files[$file];
                }

                $hash_dist[$file][$hash] += 1;
            }
        }

        $scores = array();

        foreach ($hash_dist as $file => $hashes) {
            $scores[$file] = 0;
            foreach ($hashes as $hash => $times) {
                if ($times != 0) {
                    $scores[$file] += ($num_versions / $times) - 1;
                }
            }
        }

        $cand = '';
        $max = 0;
        foreach ($scores as $file => $s) {
            if (!is_array($this->ignored) || !in_array($file, $this->ignored)) {
                if ($s > $max) {
                    $cand = $file;
                    $max = $s;
                }
            }
        }

        return $cand;
    }
}
