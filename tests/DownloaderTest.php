<?php

use Arall\CMSDiff\Downloader\Providers\GitHub;
use Arall\CMSDiff\Downloader\Downloader;

class DownloaderTest extends PHPUnit_Framework_TestCase
{
    private $downloader;
    private $path;

    protected function setUp()
    {
        $this->path = __DIR__.'/../data/test/';

        $provider = new GitHub('anchorcms', 'anchor-cms');
        $this->downloader = new Downloader($provider, $this->path);
    }

    public function tearDown()
    {
        if (file_exists($this->path)) {
            shell_exec('rm '.$this->path.' -R');
        }
    }

    public function testDownload()
    {
        $release = '0.9.3';
        $output = $this->downloader->download($release);
        $this->assertTrue($output);
        $this->assertFileExists($this->path.$release.'/index.php');
    }
}
