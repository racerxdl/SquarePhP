<?php
include_once 'SquarePacket.php';
include_once 'SquareConstants.php';
include_once 'SquarePacketConstants.php';
class PlayerAbilities extends SquarePacket
{
    function serialize()
    {
        $PlayerAbilities = new SquarePacket($this->handler);
        $PlayerAbilities->packetID = SquarePacketConstants::$SERVER_PLAYER_ABILITIES;
        $PlayerAbilities->WriteByte(0x02 | 0x04 | 0x08);
        $PlayerAbilities->WriteFloat(0.05);
        $PlayerAbilities->WriteFloat(0.1);
        $PlayerAbilities->SendPacket();
    }
}