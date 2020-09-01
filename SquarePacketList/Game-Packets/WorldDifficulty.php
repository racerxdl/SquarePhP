<?php
include_once 'SquarePacket.php';
include_once 'SquareConstants.php';
include_once 'SquarePacketConstants.php';
class WorldDifficulty extends SquarePacket
{
    function serialize()
    {
        $WorldDifficulty = new SquarePacket($this->handler);
        $WorldDifficulty->packetID = SquarePacketConstants::$SERVER_WORLD_DIFFICULTY;
        $WorldDifficulty->WriteByte($this->ServerHandler->GetWorld(0)->GetWorldDifficulty());
        $WorldDifficulty->WriteByte(true); // Lock
        $WorldDifficulty->SendPacket();
    }
}