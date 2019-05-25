<?php

ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/php_errors');

require_once "MessageSender.php";
require_once "config.php";

error_reporting(E_ALL);

date_default_timezone_set('Europe/Moscow');

$response = file_get_contents('php://input');
$update = json_decode($response, true);

$ms = new MessageSender($token, $proxy);

$ms->sendMessage($update["message"]["chat"]["id"],
                 "You typed: " . $update["message"]["text"]);
