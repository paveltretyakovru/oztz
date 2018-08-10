<?php require_once __DIR__ . '/vendor/autoload.php';

use Oz\OzSender;

$message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);

$ozSender = new OzSender();
$ozSender->execute($message);