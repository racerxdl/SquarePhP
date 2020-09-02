<?php
include_once 'SquarePacket.php';
include_once 'SquareConstants.php';
include_once 'SquarePacketConstants.php';
class Handshake_SERVER_PONG extends SquarePacket
{
    function serialize()
    {
        $SERVER_PONG = new SquarePacket($this->handler);
        $SERVER_PONG->packetID = SquarePacketConstants::$SERVER_PONG;
        $SERVER_PONG->WriteLong(round(microtime(true) * 1000)); // PHP CurrentTimeMillis();
        $SERVER_PONG->SendPacket();
    }
}

?>