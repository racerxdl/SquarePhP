<?php

include_once 'SquarePacket.php';
include_once 'SquareConstants.php';
include_once 'SquarePacketConstants.php';
class Position extends SquarePacket
{
    function serialize()
    {
        $Position = new SquarePacket($this->handler);
        $Position->packetID = SquarePacketConstants::$SERVER_POSITION;
        $Position->WriteDouble(0); // X
        $Position->WriteDouble(16); // Y
        $Position->WriteDouble(0); // Z
        $Position->WriteFloat(0); // YAW
        $Position->WriteFloat(0); // PITCH
        $Position->WriteByte(0x01);
        $Position->WriteVarInt(0);
        $Position->SendPacket();
    }
}