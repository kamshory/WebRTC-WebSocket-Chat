<?php

use WS\ChatServer;

date_default_timezone_set("Asia/Jakarta");
require_once "vendor/autoload.php";
$wss = new ChatServer('127.0.0.1', 8888);
$wss->run();
