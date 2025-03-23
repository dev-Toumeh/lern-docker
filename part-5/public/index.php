<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Database;
use App\RequestHandler;

// Initialize the Database
$database = new Database('mariadb', 'root', '1234', 'todo');

// Ensure the Tasks table exists
$database->ensureTasksTableExists();

// Initialize and handle the request
$requestHandler = new RequestHandler($database);
$requestHandler->handleRequest();

