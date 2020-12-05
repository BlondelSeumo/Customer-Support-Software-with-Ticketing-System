#!/usr/bin/php -q
<?php

ini_set('memory_limit', '256M'); //The concern here is having enough mem for emails with attachments.

if ( ! defined('STDIN')) {
    define('STDIN', fopen('php://stdin', 'r'));
}

$content = '';
while($f = fgets(STDIN)){
    $content .= $f;
}

define('PATH_INSTALL', str_replace("\\", "/", dirname(__FILE__)));

$autoloadFile = PATH_INSTALL . '/vendor/autoload.php';
if (!file_exists($autoloadFile)) {
    throw new Exception('Unable to find autoloader: ~/vendor/autoload.php');
}
require $autoloadFile;

$appFile = PATH_INSTALL . '/bootstrap/app.php';
if (!file_exists($appFile)) {
    throw new Exception('Unable to find app loader: ~/bootstrap/app.php');
}
$app = require_once $appFile;
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

app(\App\Services\Mail\IncomingMailHandler::class)
    ->parseEmailIntoTicketOrReply($content, 'mime');
?>
