<?php

include_once __DIR__ . '/vendor/autoload.php';
include_once 'ClientHandler.php';

$loop = React\EventLoop\Factory::create();
$socket = new React\Socket\Server('0.0.0.0:25565', $loop);

$socket->on('connection', function (React\Socket\ConnectionInterface $connection) use($loop) {
    $clientHandler = new ClientHandler($loop, $connection);
    $clientHandler->do();
});

$loop->run();
