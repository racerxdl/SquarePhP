<?php

include_once 'SquarePacket.php';

function DecodePacket($connection, $data) {

    // Pacote
    $packet = new SquarePacket;
    $packet->data = $data;
    $packet->originSocket = $connection;

    // Pacote normais possui tamanho e packet ID.
    $packet->packetSize = $packet->DecodeVarInt();
    $packet->packetID = $packet->DecodeVarInt();

    // Return data.
    return $packet;
}