<?php 
chdir(dirname(__DIR__));
require_once __DIR__ . '/vendor/autoload.php';

use Oz\OzReceiver;

// Load mysql properties
$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

// Collection mysql props
$db_props = [
  'db_host' => getenv('DB_HOST'),
  'db_dbname' => getenv('DB_DBNAME'),
  'db_username' => getenv('DB_USERNAME'),
  'db_password' => getenv('DB_PASSWORD'),
];

$ozReceiver = new OzReceiver($db_props);
$ozReceiver->listen();