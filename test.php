<?php

require __DIR__ . '/vendor/autoload.php';

require 'tools.php';

$loop = React\EventLoop\Factory::create();
$socket = new React\Socket\Server('0.0.0.0:25565', $loop);

function DecodeHandshake($data) {
    $protocolData = DecodeVarInt($data);
    $protocol = $protocolData["value"];

    echo("FUNFO PORRA\nO PROTOCOLO É : " . $protocol . "\n");
}

$packetDecoders = array(
    0x00 => "DecodeHandshake",
);

$socket->on('connection', function (React\Socket\ConnectionInterface $connection) use ($packetDecoders) {
    echo("CONECTADO DE " . $connection->getRemoteAddress() . "\n");
    $connection->on('data', function ($data) use ($packetDecoders, $connection) {
        $packetData = DecodePacket($data);
        $packetDecoders[$packetData["id"]]($packetData["data"]);
        $connection->close();
    });
});

echo("RODANDO!!\n");
$loop->run();
