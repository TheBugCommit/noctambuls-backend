<?php

use Symfony\Component\Dotenv\Dotenv;
use DG\BypassFinals;

require dirname(__DIR__).'/vendor/autoload.php';
$_SERVER['APP_ENV'] = 'test';
$_ENV['APP_ENV'] = 'test';
if (method_exists(Dotenv::class, 'bootEnv')) {
    new Dotenv()->bootEnv(dirname(__DIR__).'/.env');
}

BypassFinals::enable();

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}
