<?php

include_once 'SquarePacket.php';
include_once 'SquareConstants.php';
include_once 'SquarePacketConstants.php';
class KeepAlive extends SquarePacket
{
    function serialize()
    {
        $SERVER_PONG = new SquarePacket($this->handler);
        $SERVER_PONG->packetID = SquarePacketConstants::$CLIENT_PLAYER_KEEP_ALIVE;
        $SERVER_PONG->WriteLong(round(microtime(true) * 1000)); // PHP CurrentTimeMillis();
        $SERVER_PONG->SendPacket();
    }
}