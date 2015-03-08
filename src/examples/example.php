<?php

require_once __DIR__.'/../../vendor/autoload.php';

$path = isset($argv[1]) ? $argv[1] : dirname(__FILE__) . '/cms';

$cmsdiff = new Arall\CMSDiff($path);

//print_r($cmsdiff->map);

print_r($cmsdiff->uniqueMap);
