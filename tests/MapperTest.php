<?php

use Arall\CMSDiff\Mapper\Mapper;
use Arall\CMSDiff\Mapper\MapLoader;

class MapperTest extends PHPUnit_Framework_TestCase
{
    private $mapper;
    private $path;
    private $map;

    protected function setUp()
    {
        $this->path = __DIR__.'/../data/test/';
        mkdir($this->path);

        $this->map = $this->path.'test.json.gz';

        $this->mapper = new Mapper(__DIR__.'/Mock/repo', 'SuperCMS');
    }

    public function tearDown()
    {
        if (file_exists($this->path)) {
            shell_exec('rm '.$this->path.' -R');
        }
    }

    public function testScan()
    {
        $res = $this->mapper->scan();
        $this->assertTrue($res);
    }

    public function testSave()
    {
        $res = $this->mapper->save($this->map);
        $this->assertTrue($res);
        $this->assertFileExists($this->map);
    }

    public function testCheckSavedMap()
    {
        $this->mapper->scan();
        $this->mapper->save($this->map);

        $map = MapLoader::loadFile($this->map);
        $this->assertCount(2, $map);
        $this->assertTrue(isset($map['SuperCMS - v1']['/test.css']));
        $this->assertSame($map['SuperCMS - v1']['/test.css'], '64b3eb02f9ec6f92443ee39d7e5d0bda');
    }
}
