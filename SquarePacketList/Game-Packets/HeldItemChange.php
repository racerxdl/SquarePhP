<?php
include_once 'SquarePacket.php';
include_once 'SquareConstants.php';
include_once 'SquarePacketConstants.php';
class HeldItemChange extends SquarePacket
{
    function serialize()
    {
        $HeldItemChange = new SquarePacket($this->handler);
        $HeldItemChange->packetID = SquarePacketConstants::$SERVER_HELD_ITEM;
        $HeldItemChange->WriteByte(0);
        $HeldItemChange->SendPacket();
    }
}