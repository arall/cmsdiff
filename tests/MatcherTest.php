<?php

use Arall\CMSDiff\Matcher\Matcher;
use Arall\CMSDiff\Matcher\Fetcher;
use Arall\CMSDiff\Mapper\Map;

class MatcherTest extends PHPUnit_Framework_TestCase
{
    private $matcher;

    protected function setUp()
    {
        $mapPath = __DIR__.'/Mock/repo.json.gz';
        $fetcher = new Fetcher('http://google.com/');
        $map = new Map($mapPath);
        $this->matcher = new Matcher($fetcher, $map);
    }

    public function testMatcher()
    {
        // TODO
        $this->assertTrue(true);
    }
}
