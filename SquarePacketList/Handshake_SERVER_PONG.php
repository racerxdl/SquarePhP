<?php
include_once 'SquarePacket.php';
include_once 'SquareConstants.php';

class Handshake_SERVER_PONG extends SquarePacket
{
    function deserialize()
    {
        // https://wiki.vg/Protocol - Pong
        $time = $this->ReadLong();
        $SERVER_PONG = new SquarePacket($this->handler);
        $SERVER_PONG->packetID = 0x01;
        $SERVER_PONG->WriteLong($time);
        $SERVER_PONG->SendPacket();
    }

    function serialize()
    {
        $SERVER_PONG = new SquarePacket($this->handler);
        $SERVER_PONG->packetID = 0x01;
        $SERVER_PONG->WriteLong(round(microtime(true) * 1000)); // PHP CurrentTimeMillis();
        $SERVER_PONG->SendPacket();
    }
}

?>