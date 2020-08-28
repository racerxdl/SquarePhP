<?php
include_once 'SquarePacketList/Handshake.php';
include_once 'SquarePacketList/Handshake_SERVER_PONG.php';
include_once 'SquarePacketList/KeepAlive.php';

$PROTOCOL_VERSION_DATA = 751; // 1.16.2
$packetDecoders = array(
    0x00 => "Handshake",
    0x01 => "Handshake_SERVER_PONG",
    0x1F => "KeepAlive",
);
