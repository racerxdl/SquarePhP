<?php

include_once 'SquarePacket.php';
include_once 'SquareConstants.php';

class KeepAlive extends SquarePacket
{
    function deserialize()
    {
        // https://wiki.vg/Protocol - Keep Alive
        $time = $this->ReadLong();
        $SERVER_PONG = new SquarePacket($this->handler);
        $SERVER_PONG->packetID = 0x1F;
        $SERVER_PONG->WriteLong($time);
        $SERVER_PONG->SendPacket();
    }

    function serialize()
    {
        $SERVER_PONG = new SquarePacket($this->handler);
        $SERVER_PONG->packetID = 0x1F;
        $SERVER_PONG->WriteLong(round(microtime(true) * 1000)); // PHP CurrentTimeMillis();
        $SERVER_PONG->SendPacket();
    }
}