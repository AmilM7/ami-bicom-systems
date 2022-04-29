<?php

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

require('message-server.php');

require 'vendor/autoload.php';

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Messages()
        )
    ),
    8080
);

$server->run();
