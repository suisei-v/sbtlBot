<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('Europe/Moscow');

require_once 'sbtlBot.php';
require_once 'config.php';

$response = file_get_contents('php://input');
$update = json_decode($response, true);
$bot = new SbtlBot($token, $proxy, $db);
$res = $bot->newUpdate($update);
