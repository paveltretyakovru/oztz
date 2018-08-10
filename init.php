<?php

chdir(dirname(__DIR__));
require_once('vendor/autoload.php');

/**
 * Working on the DB initialization
 */

// Collect env variables 
$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$db_host = getenv('DB_HOST');
$db_dbname = getenv('DB_DBNAME');
$db_username = getenv('DB_USERNAME');
$db_password = getenv('DB_PASSWORD');

// Migrate DB tables
$uri = new \ByJG\Util\Uri("mysql://${db_username}:${db_password}@${db_host}/${db_dbname}");
$migration = new \ByJG\DbMigration\Migration($uri, __DIR__);
$migration->registerDatabase('mysql', \ByJG\DbMigration\Database\MySqlDatabase::class);
$migration->prepareEnvironment();
$migration->reset();