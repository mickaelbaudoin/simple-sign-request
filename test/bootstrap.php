<?php
date_default_timezone_set('UTC');
$autoloadFile = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoloadFile)) {
throw new RuntimeException('Autoload vendor not found !');
}
require_once $autoloadFile;