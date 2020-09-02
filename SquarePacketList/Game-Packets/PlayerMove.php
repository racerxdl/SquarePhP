<?php

include_once 'SquarePacket.php';
include_once 'SquareConstants.php';
include_once 'SquarePacketConstants.php';

class PlayerMove extends SquarePacket
{
    function deserialize()
    {
        // L? o pacote de movimento
        {
            // Distancia de Renderiza??o
            $renderDistance = $this->ServerHandler->GetWorld(0)->GetRenderDistance();

            // Posi??o anterior
            $OldX = $this->handler->GetMyPlayer()->GetX();
            $OldZ = $this->handler->GetMyPlayer()->GetZ();

            // Posi??o atual
            $x = $this->ReadDouble();
            $y = $this->ReadDouble();
            $z = $this->ReadDouble();
            $onGround = $this->ReadByte();

            // Old Chunks
            $OldChunkX = $OldX >> 4;
            $OldChunkZ = $OldZ >> 4;

            // Chunks!
            $ChunkX = $x >> 4;
            $ChunkZ = $z >> 4;
            $ChunkData = $this->ServerHandler->GetWorld(0)->GetChunk($ChunkX, $ChunkZ);

            // Verifica se a chunk existe
            if ($ChunkData == null) {
                Logger::getLogger("PHPServer")->info("Nao existe coordenadas de chunks na regiao (CX/CZ/X/Z) = ({$ChunkX}/{$ChunkZ}) ({$x}/{$z}), criando uma nova.");
                $this->ServerHandler->GetWorld(0)->CreateChunk($ChunkX, $ChunkZ);
            }

            // Precisa enviar as chunks?
            // todo: nao ta 100% perfeito, mas ja algo
            if ($ChunkX != $OldChunkX || $ChunkZ != $OldChunkZ && $this->handler->GetMyPlayer()->HaveChunk($ChunkX, $ChunkZ) == null) {
                // Carrega as chunks baseado no raio do player.
                for ($x = -$renderDistance; $x < $renderDistance; $x++) {
                    for ($z = -$renderDistance; $z < $renderDistance; $z++) {
                        // Se o player ainda nao recebeu essa chunk....
                        if ($this->handler->GetMyPlayer()->HaveChunk($ChunkX + $x, $ChunkZ + $z) == null) {
                            $this->ServerHandler->GetWorld(0)->UnloadChunk2Player($this->handler, $OldChunkX + $x, $OldChunkZ + $z);
                            $this->handler->GetMyPlayer()->UnsetChunk($OldChunkX + $x, $OldChunkZ + $z);
                            $ChunkData = $this->ServerHandler->GetWorld(0)->GetChunk($ChunkX + $x, $ChunkZ + $z);
                            if ($ChunkData == null) {
                                Logger::getLogger("PHPServer")->info("Nao existe coordenadas de chunks na regiao (CX/CZ) = ({$ChunkX}/{$ChunkZ}), criando uma nova.");
                                $this->ServerHandler->GetWorld(0)->CreateChunk($ChunkX + $x, $ChunkZ + $z);
                            }
                            $this->handler->GetMyPlayer()->SetHaveChunk($ChunkX + $x, $ChunkZ + $z);
                            $this->ServerHandler->GetWorld(0)->SendChunk($this->handler, $ChunkX + $x, $ChunkZ + $z);
                        }
                    }
                }
            }
            $this->handler->GetMyPlayer()->SetPosition($x, $y, $z);
        }

        // Sempre envie a view distance para o jogador.
        // Isso evita o jogo ignorar as chunks
        {
            $viewdistance = new SquarePacket($this->handler);
            $viewdistance->packetID = 0x41;
            $viewdistance->WriteVarInt(0xFF);
            $viewdistance->SendPacket();
        }
    }
}
