<?php

$autoload = '../vendor/autoload.php';

if (!file_exists($autoload)) {
    exit('Run composer first');
}

// Autoload
require_once $autoload;

$case = @$_GET['case'];

require './sample-resources.php';