<?php

namespace Arall\CMSDiff\Matcher;

use Symfony\Component\Console\Output\OutputInterface;
use Arall\CMSDiff\Mapper\Map;

class Matcher
{
    /**
     * Fetcher.
     *
     * @var Arall\CMSDiff\Matcher\Fetcher
     */
    private $fetcher;

    /**
     * Map.
     *
     * @var Arall\CMSDiff\Mapper\Map
     */
    private $map;

    /**
     * Output interface.
     *
     * @var Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    /**
     * Construct.
     *
     * @param string $url Target URL
     */
    public function __construct(Fetcher $fetcher, Map $map, $output = null)
    {
        // Fetcher
        $this->fetcher = $fetcher;

        // Map
        $this->map = $map;

        // Output interface
        $this->output = $output;
    }

    /**
     * Match files against website URL.
     *
     * @return array Possible versions
     */
    public function match()
    {
        // Until we have more than one candidate...
        while (count($this->map->getCandidates()) > 1) {
            // Get next file to match
            if ($file = $this->map->getNextFile()) {
                // Delete versions in $candidates which don't match the hash
                $hash = $this->fetcher->fetch($file);
                $this->writeln('Matching '.$file.'...'.$hash);
                $this->process($file, $hash);
            } else {
                break;
            }
        }

        return $this->map->getCandidates();
    }

    /**
     * Process a hashed file.
     *
     * @param string $file
     * @param string $hash
     *
     * @return bool
     */
    private function process($file, $hash)
    {
        $matches = $this->map->find($file, $hash);

        // Matches?
        if (!empty($matches)) {
            $this->writeln('<info>Match! '.implode(', ', $matches).'</info>');

            $this->map->setCandidates($matches);

        // File doesn't match
        } else {
            $this->map->ignoreFile($file);
        }

        return true;
    }

    /**
     * Output a line.
     *
     * @param string $string
     *
     * @return bool
     */
    private function writeln($string)
    {
        if ($this->output) {
            return $this->output->writeln($string);
        }
    }
}
