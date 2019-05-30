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

// $ms = new MessageSender($token, $proxy);

// $ms->sendMessage($update["message"]["chat"]["id"],
//                  "You typed: " . $update["message"]["text"]);


$ch = curl_init("https://nyaa.si/?page=rss");

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
if ($proxy) {
    curl_setopt($ch, CURLOPT_PROXY, $proxy['host']);
    curl_setopt($ch, CURLOPT_PROXYTYPE, $proxy['type']);
    curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxy['auth']);
}

$res = curl_exec($ch);
$error = curl_error($ch);
if ($error) {
    error_log("curl error:  " . $error);
}
