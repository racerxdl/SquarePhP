<?php

include_once 'SquarePacket.php';
include_once 'SquareConstants.php';

class Position extends SquarePacket
{
    function serialize()
    {
        $Position = new SquarePacket($this->handler);
        $Position->packetID = 0x34;
        $Position->WriteDouble(0);
        $Position->WriteDouble(64);
        $Position->WriteDouble(0);
        $Position->WriteFloat(0);
        $Position->WriteFloat(0);
        $Position->WriteByte(0x01);
        $Position->WriteVarInt(0);
        $Position->SendPacket();
    }
}