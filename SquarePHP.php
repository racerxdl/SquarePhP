<?php

include_once __DIR__ . '/vendor/autoload.php';
include_once 'ClientHandler.php';
include_once 'ServerHandler.php';

$loop = React\EventLoop\Factory::create();
$socket = new React\Socket\Server('0.0.0.0:25565', $loop);

$serverHandler = new ServerHandler;

$serverHandler->config->description = "Seja bem vindo ao NaN e ao undefined is not defined";
$serverHandler->config->maxPlayers = 10;
$serverHandler->config->onlinePlayers = 0;

$socket->on('connection', function (React\Socket\ConnectionInterface $connection) use ($loop, $serverHandler) {
    $clientHandler = new ClientHandler($loop, $serverHandler, $connection);
    $clientHandler->do();
});

$loop->run();
