<?php

include_once 'SquarePacket.php';
include_once 'SquareConstants.php';
include_once 'SquarePacketConstants.php';
include_once 'SquareMath.php';

class PlayerMove extends SquarePacket
{
    function deserialize()
    {
        // Le o pacote de movimento
        {
            // Distancia de Renderizacao
            $renderDistance = $this->ServerHandler->GetWorld(0)->GetRenderDistance();

            // Posicao anterior
            $OldX = $this->handler->GetMyPlayer()->GetX();
            $OldY = $this->handler->GetMyPlayer()->GetY();
            $OldZ = $this->handler->GetMyPlayer()->GetZ();

            // Posicao atual
            $PlayerX = $this->ReadDouble();
            $PlayerY = $this->ReadDouble();
            $PlayerZ = $this->ReadDouble();
            $onGround = $this->ReadByte();

            // Chunk Anterior
            $OldChunkX = $OldX >> 4;
            $OldChunkZ = $OldZ >> 4;

            // Chunk atual.
            $ChunkX = $PlayerX >> 4;
            $ChunkZ = $PlayerZ >> 4;

            // Informacao da chunk atual.
            $ChunkData = $this->ServerHandler->GetWorld(0)->GetChunk($ChunkX, $ChunkZ);

            // Aplica a posicao.
            $this->handler->GetMyPlayer()->SetPosition($PlayerX, $PlayerY, $PlayerZ);
            $this->handler->GetMyPlayer()->SetOnGround($onGround);

            // Verifica se a chunk existe
            if ($ChunkData == null) {
                Logger::getLogger("PHPServer")->info("Nao existe coordenadas de chunks na regiao (X/Z) = ({$ChunkX}/{$ChunkZ}), criando uma nova.");
                $this->ServerHandler->GetWorld(0)->CreateChunk($ChunkX, $ChunkZ);
            }

            // Verifica se o player saiu da borda de 16x16x16
            if ($ChunkX != $OldChunkX || $ChunkZ != $OldChunkZ) {
                // Carrega novamente todas chunks novas.
                for ($x = -$renderDistance; $x < $renderDistance; $x++) {
                    for ($z = -$renderDistance; $z < $renderDistance; $z++) {
                        // Chunks a ser enviadas.
                        $ChunkOffsetX = $ChunkX + $x;
                        $ChunkOffsetZ = $ChunkZ + $z;

                        // Chunks Antigas
                        $OldChunkOffsetX = $OldChunkX + $x;
                        $OldChunkOffsetZ = $OldChunkZ + $z;

                        // Descarrega as Chunks anteriores
                        {
                            $this->ServerHandler->GetWorld(0)->UnloadChunk2Player($this->handler, -$OldChunkOffsetX, -$OldChunkOffsetZ);
                        }

                        // Envia as chunks.
                        {
                            // Verifica se existe e crie caso nao exista.
                            $ChunkData = $this->ServerHandler->GetWorld(0)->GetChunk($ChunkOffsetX, $ChunkOffsetZ);
                            if ($ChunkData == null) {
                                Logger::getLogger("PHPServer")->info("Nao existe coordenadas de chunks na regiao (X/Z) = ({$ChunkOffsetX}/{$ChunkOffsetZ}), criando uma nova.");
                                $this->ServerHandler->GetWorld(0)->CreateChunk($ChunkOffsetX, $ChunkOffsetZ);
                            }
                            // Envia ao jogador a chunk
                            $this->ServerHandler->GetWorld(0)->SendChunk($this->handler, $ChunkOffsetX, $ChunkOffsetZ);
                        }

                        // Seta a posicao do player na chunk
                        $chunkPos = new SquarePacket($this->handler);
                        $chunkPos->packetID = 0x40;
                        $chunkPos->WriteVarInt($ChunkOffsetX);
                        $chunkPos->WriteVarInt($ChunkOffsetZ);
                        $chunkPos->SendPacket();
                    }
                }
                // Distancia para o jogador.
                {
                    $viewdistance = new SquarePacket($this->handler);
                    $viewdistance->packetID = 0x41;
                    $viewdistance->WriteVarInt($renderDistance);
                    $viewdistance->SendPacket();
                }
            }

            // Envia o movimento para outros jogadores
            {
                $PlayerMove = new SquarePacket($this->handler);
                $PlayerMove->packetID = SquarePacketConstants::$SERVER_ENTITY_TELEPORT;
                $PlayerMove->WriteVarInt($this->handler->GetMyPlayer()->GetEntityID());
                $PlayerMove->WriteDouble($PlayerX);
                $PlayerMove->WriteDouble($PlayerY);
                $PlayerMove->WriteDouble($PlayerZ);
                $PlayerMove->WriteByte($this->handler->GetMyPlayer()->GetYaw());
                $PlayerMove->WriteByte($this->handler->GetMyPlayer()->GetPitch());
                $PlayerMove->WriteByte($onGround);
                for ($i = 0; $i < count($this->ServerHandler->GetWorld(0)->PlayerList); $i++) {
                    $bPlayer = $this->ServerHandler->GetWorld(0)->PlayerList[$i];
                    if ($bPlayer != $this->handler->GetMyPlayer()) {
                        $PlayerMove->handler = $bPlayer->ClientHandler;
                        $PlayerMove->SendPacket();
                    }
                }
            }
        }
    }
}
