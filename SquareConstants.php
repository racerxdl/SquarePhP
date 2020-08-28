<?php
include_once 'SquarePacketInclusion.php';

$PROTOCOL_VERSION_DATA = 751; // 1.16.2

$GamePackets = array(
    // Server Status
    SquarePacketConstants::$SERVER_HANDSHAKE => "Handshake",
    SquarePacketConstants::$SERVER_PONG => "Handshake_SERVER_PONG",

    // Client Packets
    SquarePacketConstants::$CLIENT_PLAYER_KEEP_ALIVE => "KeepAlive",
    SquarePacketConstants::$CLIENT_PLAYER_CHAT => "PlayerChat",
);