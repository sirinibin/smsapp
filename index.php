<?php
require_once(__DIR__ . '/vendor/autoload.php');
require_once("config.php");
require_once("Sms.php");
require_once("Api.php");


$api = new Api;
$sms = new Sms($api->recipients, $api->message, $api->originator);

$result = $sms->send();

$api->sendResponse($result,$sms->messageLength);


