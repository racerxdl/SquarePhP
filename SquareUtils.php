<?php

include_once 'SquarePacket.php';

function DecodePacket($handler, $data) {
    // Pacote
    $packet = new SquarePacket($handler);
    $packet->data = $data;

    // Pacote normais possui tamanho e packet ID.
    $packet->packetSize = $packet->DecodeVarInt();
    $packet->packetID = $packet->DecodeVarInt();

    // Return data.
    return $packet;
}