#! /usr/bin/env php

<?php

use Symfony\Component\Console\Application;

// Composer
if (!file_exists('vendor/autoload.php')) {
    die('Composer dependency manager is needed: https://getcomposer.org/');
}
require 'vendor/autoload.php';

$app = new Application('CMS Diff', '1.0');

$app->add(new Arall\CMSDiff\Commands\RepositoryDownload());
$app->add(new Arall\CMSDiff\Commands\RepositoryMap());
$app->add(new Arall\CMSDiff\Commands\WebsiteMatch());

$app->run();
