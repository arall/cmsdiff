#! /usr/bin/env php

<?php

use Symfony\Component\Console\Application;

require 'vendor/autoload.php';

$app = new Application('CMS Diff', '1.0');

$app->add(new Arall\CMSDiff\Commands\RepositoryDownload());
$app->add(new Arall\CMSDiff\Commands\RepositoryMap());

$app->run();
