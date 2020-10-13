<?php

use NoiseGenerator\PerlinNoise;

include_once 'SquarePacket.php';
include_once 'SquareConstants.php';
include_once 'SquarePacketConstants.php';
include_once '../SquarePhP/SquareWorld/Chunk.php';
include_once 'API/MojangSkinAPI.php';
class ChunkData extends SquarePacket
{
    function serialize()
    {
        $renderDistance = $this->ServerHandler->GetWorld(0)->GetRenderDistance();
        for ($x = -$renderDistance; $x < $renderDistance; $x++) {
            for ($z = -$renderDistance; $z < $renderDistance; $z++) {
                $ChunkData = $this->ServerHandler->GetWorld(0)->GetChunk($x, $z);
                if ($ChunkData == null) {
                    Logger::getLogger("PHPServer")->info("Nao existe coordenadas de chunks na regiao ({$x}/{$z}), criando uma nova.");
                    $this->ServerHandler->GetWorld(0)->CreateChunk($x, $z);
                }
                $this->ServerHandler->GetWorld(0)->SendChunk($this->handler, $x, $z);
                $this->handler->GetMyPlayer()->SetHaveChunk($x, $z);
            }
        }
    }
}
