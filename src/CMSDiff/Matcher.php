<?php

namespace Arall\CMSDiff;

use Curl\Curl;
use Symfony\Component\Console\Output\OutputInterface;

class Matcher
{
    /**
     * Output interface.
     *
     * @var Symfony\Component\Console\Output\OutputInterface
     */
    private $output;

    /**
     * Website URL.
     *
     * @var string
     */
    private $url;

    /**
     * Loaded JSON data.
     *
     * @var array
     */
    private $data = array();

    /**
     * Candidate versions.
     *
     * @var array
     */
    private $candidates = array();

    /**
     * Unmatched ignored files.
     *
     * @var array
     */
    private $ignored = array();

    /**
     * Website file hashes (cached).
     *
     * @var array
     */
    public $content = array();

    /**
     * Construct.
     *
     * @param string $url      Target URL
     * @param string $jsonPath JSON Data path (file or dir)
     */
    public function __construct($url, $jsonPath, OutputInterface $output)
    {
        $this->url = $url;

        // Path
        if (is_dir($jsonPath)) {
            $d = dir($jsonPath);
            while (false !== ($entry = $d->read())) {
                $filepath = "{$jsonPath}/{$entry}";
                if (!is_dir($filepath)) {
                    $this->loadProduct($filepath);
                }
            }
        // File
        } else {
            $this->loadProduct($jsonPath);
        }
        $this->output = $output;

        // Load possible versions
        $this->candidates = array_keys($this->data);
    }

    /**
     * Load product JSON to data.
     *
     * @param path $path JSON Data file path
     */
    private function loadProduct($path)
    {
        $json = '';
        $gzo = gzopen($path, 'r');
        while ($line = gzgets($gzo, 1024)) {
            $json .= $line;
        }
        gzclose($gzo);
        $data = json_decode($json, true);
        if (is_array($data)) {
            $this->data = array_merge($this->data, $data);
        }
    }

    /**
     * Match files against website URL.
     *
     * @return array Possible versions
     */
    public function match()
    {
        while (count($this->candidates) > 1) {
            if ($file = $this->getNextFile()) {
                // Delete versions in $candidates which don't match the hash
                $hash = $this->getFileHash($file);
                $this->output->writeln('Matching '.$file.'...'.$hash);
                $this->discard($file, $hash);
            } else {
                break;
            }
        }

        return $this->candidates;
    }

    /**
     * Delete unmatched candidates.
     *
     * @param string $file
     * @param string $hash
     *
     * @return bool
     */
    private function discard($file, $hash)
    {
        $matches = array();
        foreach ($this->candidates as $version) {
            if (isset($this->data[$version][$file])) {
                if ($this->data[$version][$file] == $hash) {
                    $this->output->writeln('<info>Match '.$version.'</info>');
                    $matches[] = $version;
                    continue;
                }
            }
        }
        if (empty($matches)) {
            $this->output->writeln('Ignoring file...');
            $this->ignored[] = $file;
        } else {
            $this->candidates = $matches;
        }

        return true;
    }

    /**
     * Get next file.
     *
     * @return string
     */
    private function getNextFile()
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

    /**
     * Get remote file hash.
     *
     * @param string $path
     *
     * @return string
     */
    private function getFileHash($path, $force = false)
    {
        $url = $this->url.$path;

        // Non existing content?
        if ($force || !isset($this->content[$path])) {
            $curl = new Curl();
            $curl->setOpt(CURLOPT_RETURNTRANSFER,   true);
            $curl->setOpt(CURLOPT_AUTOREFERER,      true);
            $curl->setOpt(CURLOPT_FOLLOWLOCATION,   true);
            $curl->get($url);

            if ($curl->error) {
                return false;
            }

            $this->content[$path] = md5($curl->response);
        }

        return $this->content[$path];
    }
}
