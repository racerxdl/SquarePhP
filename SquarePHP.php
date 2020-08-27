<?php

include_once __DIR__ . '/vendor/autoload.php';
include_once 'SquareUtils.php';
include_once 'SquareConstants.php';
include_once 'SquarePacketHandler.php';

$loop = React\EventLoop\Factory::create();
$socket = new React\Socket\Server('0.0.0.0:25565', $loop);

$socket->on('connection', function (React\Socket\ConnectionInterface $connection) {
    $connection->on('data', function ($data) use ($connection) {

        // Retorna a classe Packet
        $SquarePacket = DecodePacket($connection, $data);

        // base concluida.
        echo "Packet ID {$SquarePacket->packetID}, size {$SquarePacket->packetSize} \n";

        // Packet handler
        $packetHandler = new SquarePacketHandler;
        $packetHandler->tryHandle($SquarePacket);
    });
});
$loop->run();
