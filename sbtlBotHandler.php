<?php

ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/php_errors');

require_once "MessageSender.php";
require_once "Router.php";
require_once "config.php";

error_reporting(E_ALL);

date_default_timezone_set('Europe/Moscow');

// $response = file_get_contents('php://input');
// $update = json_decode($response, true);
// $bot = new SbtlBot($token, $proxy, $db);
// $res = $bot->newUpdate($update);



$response = file_get_contents('php://input');
$update = json_decode($response, true);

$ms = new MessageSender($token, $proxy);
$pdo = new PDO($DB_DSN, $DB_USER, $DB_PASSWORD,
               [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
               PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8']);

$router = new Router($ms, $pdo, $admin_list);
$reply = $router->route($update);
