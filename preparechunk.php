<?php

include_once __DIR__ . '/vendor/autoload.php';
include_once "SquarePacketList/Game-Packets/ChunkData.php";

//echo("LENDO INPUT" . PHP_EOL);
$input_data = file_get_contents("php://stdin");

//echo("DESERIALIZANDO" . PHP_EOL);
$params = unserialize($input_data);

$chunk = $params["CHUNK"];
$x = $params["x"];
$z = $params["z"];

//echo("FAZENDO A GAMBI" . PHP_EOL);
$ChunkPacket = new SquarePacket(null);
$ChunkPacket->packetID = SquarePacketConstants::$SERVER_CHUNK_DATA;
$ChunkPacket->WriteInt($x); // chunkX
$ChunkPacket->WriteInt($z); // chunkZ
$ChunkPacket->WriteByte(true); // fullChunk
$ChunkPacket->WriteVarInt($chunk->GetSelectionMask());

// Heightmap Tags
{
    $ChunkPacket->WriteByte(10);
    $ChunkPacket->WriteStringNBT("MOTION_BLOCKING"); // Root Element
    {
        $ChunkPacket->WriteByte(12);
        $ChunkPacket->WriteStringNBT("");
        $ChunkPacket->WriteInt(36);
        for ($i = 0; $i < 36; $i++) {
            $ChunkPacket->WriteLong(36);
        }
    }
    $ChunkPacket->WriteByte(0);
}

// Biomes
$ChunkPacket->WriteVarInt(1024);
for ($i = 0; $i < 1024; $i++) {
    $ChunkPacket->WriteVarInt(1);
}

$chunk->Serialize($ChunkPacket);
$ChunkPacket->WriteVarInt(0); // Title Entities

$packetData = $ChunkPacket->PreparePacket();

//var_dump($packetData);

echo($packetData);

//echo("O INICIO DO FIM". PHP_EOL);

