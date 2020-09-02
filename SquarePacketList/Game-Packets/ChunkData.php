<?php

use NoiseGenerator\PerlinNoise;
use React\ChildProcess\Process;
use React\Promise\Timer;

include_once 'SquarePacket.php';
include_once 'SquareConstants.php';
include_once 'SquarePacketConstants.php';
include_once '../SquarePhP/SquareWorld/Chunk.php';
include_once 'API/MojangSkinAPI.php';

class ChunkData extends SquarePacket
{

    function PutChunk($chunk, $x, $z)
    {
//        $ChunkPacket = new SquarePacket($this->handler);
//        $ChunkPacket->packetID = SquarePacketConstants::$SERVER_CHUNK_DATA;
//        $ChunkPacket->WriteInt($x); // chunkX
//        $ChunkPacket->WriteInt($z); // chunkZ
//        $ChunkPacket->WriteByte(true); // fullChunk
//        $ChunkPacket->WriteVarInt($chunk->GetSelectionMask());
//
//        // Heightmap Tags
//        {
//            $ChunkPacket->WriteByte(10);
//            $ChunkPacket->WriteStringNBT("MOTION_BLOCKING"); // Root Element
//            {
//                $ChunkPacket->WriteByte(12);
//                $ChunkPacket->WriteStringNBT("");
//                $ChunkPacket->WriteInt(36);
//                for ($i = 0; $i < 36; $i++) {
//                    $ChunkPacket->WriteLong(36);
//                }
//            }
//            $ChunkPacket->WriteByte(0);
//        }
//
//        // Biomes
//        $ChunkPacket->WriteVarInt(1024);
//        for ($i = 0; $i < 1024; $i++) {
//            $ChunkPacket->WriteVarInt(1);
//        }
//
//        $chunk->Serialize($ChunkPacket);
//        $ChunkPacket->WriteVarInt(0); // Title Entities
//        $ChunkPacket->SendPacket();

        $params = array(
            "CHUNK" => $chunk,
            "x" => $x,
            "z" => $z,
        );

        $data = serialize($params);

        $process = new Process("php preparechunk.php");
        $process->start($this->handler->loop);

        $process->stdout->on('data', function ($chunk) {
            Logger::getLogger("GAMBIARRA")->info("RECEBIDO ");
            $this->handler->SendPacket($chunk);
        });
        $process->stderr->on('data', function ($chunk) {
            Logger::getLogger("GAMBIARRA")->error("RECEBIDO ERRO     " . $chunk);
        });

//        $process->stdout->on('end', function () {
//            Logger::getLogger("GAMBIARRA")->info("FIM");
//        });

        $process->stdout->on('error', function (Exception $e) {
            Logger::getLogger("GAMBIARRA")->error($e->getMessage());
        });

        $process->stdin->write($data);
        $process->stdin->end($data = null);
        Logger::getLogger("GAMBIARAR")->info("METENDO PACOTE");
    }

    function serialize()
    {
        // Cria a chunk de 0~16
        $chunk = new Chunk();
        for ($x = 0; $x < 16; $x++) {
            for ($z = 0; $z < 16; $z++) {
                $chunk->SetBlock($x, 0, $z, 33); // bedrock
                $chunk->SetBlock($x, 1, $z, 10); // terra
                $chunk->SetBlock($x, 2, $z, 10); // terra
                $chunk->SetBlock($x, 3, $z, rand(1, 9)); // grama
                $chunk->SetBlock($x, 40, $z, rand(1, 9)); // teto
            }
        }

        // Chunk Data
        {
            $this->PutChunk($chunk, 0, 0);
            Timer\resolve(5, $this->handler->loop)->then(function () use($chunk) {
                for ($x = -16; $x < 16; $x++) {
                    for ($z = -16; $z < 16; $z++) {
                        $this->PutChunk($chunk, $x, $z);
                    }
                }
            });
//            $this->PutChunk($chunk, 0, 1);
            // melhorar desempenho
        }
    }
}
