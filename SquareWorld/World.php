<?php
include_once 'SquareWorld/Generation/PerlinNoise.php';
include_once 'SquareMath.php';

use NoiseGenerator\PerlinNoise;

class World
{
    private int $WorldTime;
    private int $TotalWorldTime;
    private int $TotalEntities;
    private int $WorldDifficulty;
    private string $WorldName;
    private int $TotalChunks;
    private int $WorldSeed;
    private int $RenderDistance;
    public $PlayerList;
    public int $PlayerCount;
    public $ChunkData;

    function __construct()
    {
        $this->WorldTime = 0;
        $this->TotalWorldTime = 0;
        $this->TotalEntities = 0;
        $this->TotalChunks = 0;
        $this->RenderDistance = 8;
        $this->WorldName = "world";
        $this->PlayerList = array();
        $this->WorldDifficulty = 3; // Hard
        $this->WorldSeed = round(microtime(true) * 1000);
        $this->ChunkData = array();
        $this->PlayerCount = 0;
    }

    function GetRenderDistance()
    {
        return $this->RenderDistance;
    }

    function SetChunk($START_X, $END_Z, $chunk)
    {
        $this->ChunkData[$START_X][$END_Z] = $chunk;
    }

    function AddPlayer($player)
    {
        $this->PlayerList[$this->PlayerCount++] = $player;
        return rand($this->PlayerCount, 0xFFFFFFFFFF); // TODO: melhorar esse ID.
    }

    function GetTotalChunks()
    {
        return count($this->ChunkData);
    }

    function PlaceBlock($LocX, $LocY, $LocZ)
    {
        if ($this->GetChunk($LocX >> 4, $LocZ >> 4) == null || $LocY < 0) {
            return;
        }
        $this->ChunkData[$LocX >> 4][$LocZ >> 4]->SetBlock(SquareMath::fixModule($LocX, 16), $LocY, SquareMath::fixModule($LocZ, 16), 1);
    }

    // TODO: GERACAO DO MUNDO
    function CreateChunk($X, $Z)
    {
        $ores = array(1, 3354, 6731, 69, 70, 71, 5254, 72, 232, 3886);
        $chunk = new Chunk;
        $noise = new PerlinNoise(rand(0, 0xFFFF));
        for ($x = 0; $x < 16; $x++) {
            for ($z = 0; $z < 16; $z++) {
                // Blocos iniciais
                $chunk->SetBlock($x, 0, $z, 33); // bedrock
                $chunk->SetBlock($x, 1, $z, 10); // terra
                $chunk->SetBlock($x, 2, $z, 10); // terra
                $chunk->SetBlock($x, 3, $z, 9); // grama

                // teste altitude
                /*
                   $Noised = $noise->random2D(($x + rand(0, 0xFFFF)) / sqrt(4 * rand(1, 25)), ($z + rand(0, 0xFFFF) / sqrt(2 * rand(1, 25))));
                   $Noised = abs($Noised);
                   $Noised *= rand(1, rand(1, 63));
                   $Noised = floor($Noised);
                   $chunk->SetBlock($x, 4 + $Noised, $z, $ores[rand(0, count($ores) - 1)]);
                */
            }
        }
        $this->SetChunk($X, $Z, $chunk);
    }

    // Broadcast packet
    function BroadCastPacket($pkt)
    {
        for ($i = 0; $i < count($this->PlayerList); $i++) {
            $Player = $this->PlayerList[$i];
            $pkt->handler = $Player->ClientHandler;
            $pkt->SendPacket();
        }
    }

    // Update Tab
    function UpdateTabList($handler)
    {
        // Tab List
        {
            $PlayerList = new SquarePacket($handler);
            $PlayerList->packetID = SquarePacketConstants::$SERVER_PLAYER_LIST; 
            {
                $PlayerList->WriteVarInt(0); // Add Player
                $PlayerList->WriteVarInt(count($this->PlayerList)); {
                    for ($i = 0; $i < count($this->PlayerList); $i++) {
                        $Player = $this->PlayerList[$i]; 
                        {
                            $PlayerList->WriteUUID($Player->GetUUID());
                            $PlayerList->WriteString($Player->GetPlayerName());
                            $PlayerList->WriteVarInt(0); // Sem Skin.
                            $PlayerList->WriteVarInt(0); // gamemode
                            $PlayerList->WriteVarInt(0); // ping
                            $PlayerList->WriteByte(false);
                        }
                    }
                }
            }
            $PlayerList->SendPacket();
        }
    }

    // Remove o jogador da tab list
    function RemovePlayerGame($Player)
    {
        for ($i = 0; $i < count($this->PlayerList); $i++) {
            // Envia a todos os jogadores para remover o player do tab list
            $PlayerList = new SquarePacket($this->PlayerList[$i]->ClientHandler);
            $PlayerList->packetID = SquarePacketConstants::$SERVER_PLAYER_LIST;
            $PlayerList->WriteVarInt(4); // Remove Player
            $PlayerList->WriteVarInt(1);
            $PlayerList->WriteUUID($Player->GetUUID());
            $PlayerList->SendPacket();

            // Envia a todos os jogadores removendo a entidade do mundo.
            $DestroyEntity = new SquarePacket($this->PlayerList[$i]->ClientHandler);
            $DestroyEntity->packetID = SquarePacketConstants::$SERVER_DESTROY_ENTITY;
            $DestroyEntity->WriteVarInt(1);
            $DestroyEntity->WriteVarInt($Player->GetEntityID());
            $DestroyEntity->SendPacket();
       }  
    }

    // Remove Player
    function RemovePlayer($Player, $handler)
    {
        for ($i = 0; $i < count($this->PlayerList); $i++) {
            if ($this->PlayerList[$i] == $Player) {
                unset($this->PlayerList[$i]);
                $this->PlayerCount--;
            }
        }
        $this->PlayerList = array_values($this->PlayerList);
        $this->RemovePlayerGame($Player);
    }

    // Lista de Jogadores
    function SendPlayerList($self, $handler)
    {
        // Criacao de conteudo.
        $PLAYER_CREEPER_ENTITY_TEST = true;

        $this->UpdateTabList($handler);

        // Spawna os jogadores
        {
            for ($i = 0; $i < count($this->PlayerList); $i++) {
                $Player = $this->PlayerList[$i]; {
                    if ($Player != $self) {
                        if ($PLAYER_CREEPER_ENTITY_TEST) {
                            $SpawnEntity = new SquarePacket($handler);
                            $SpawnEntity->packetID = 0x02;
                            $SpawnEntity->WriteVarInt($Player->GetEntityID()); // ID Entity
                            $SpawnEntity->WriteUUID($Player->GetUUID());
                            $SpawnEntity->WriteVarInt(12); // Entity ID ( https://wiki.vg/Entity_metadata#Mobs )
                            $SpawnEntity->WriteDouble($Player->GetX());
                            $SpawnEntity->WriteDouble($Player->GetY());
                            $SpawnEntity->WriteDouble($Player->GetZ());
                            $SpawnEntity->WriteByte(0);
                            $SpawnEntity->WriteByte(0);
                            $SpawnEntity->WriteByte(0);
                            $SpawnEntity->WriteShort(0.5);
                            $SpawnEntity->WriteShort(0.5);
                            $SpawnEntity->WriteShort(0.5);
                            $SpawnEntity->SendPacket();
                        } else {
                            $PlayerSpawn = new SquarePacket($handler);
                            $PlayerSpawn->packetID = SquarePacketConstants::$SERVER_PLAYER_SPAWN;
                            $PlayerSpawn->WriteVarInt($Player->GetEntityID());
                            $PlayerSpawn->WriteUUID($Player->GetUUID());
                            $PlayerSpawn->WriteDouble($Player->GetX());
                            $PlayerSpawn->WriteDouble($Player->GetY());
                            $PlayerSpawn->WriteDouble($Player->GetZ());
                            $PlayerSpawn->WriteByte(0);
                            $PlayerSpawn->WriteByte(0);
                            $PlayerSpawn->SendPacket();
                        }
                    }
                }
            }
        }

        // Cria a entidade no cliente
        {
            for ($i = 0; $i < count($this->PlayerList); $i++) {
                $EntityStart = new SquarePacket($handler);
                $EntityStart->packetID = SquarePacketConstants::$SERVER_ENTITY_START;
                $EntityStart->WriteVarInt($this->PlayerList[$i]->GetEntityID());
                $EntityStart->SendPacket();
            }
        }
    }

    // Unload chunk
    function UnloadChunk2Player($handler, $X, $Z)
    {
        $UnloadChunk = new SquarePacket($handler);
        $UnloadChunk->packetID = SquarePacketConstants::$SERVER_UNLOAD_CHUNK;
        $UnloadChunk->WriteInt($X); // chunkX
        $UnloadChunk->WriteInt($Z); // chunkZ
        $UnloadChunk->SendPacket();
    }

    // TODO:
    function SendChunk($handler, $X, $Z)
    {
        // Chunk
        $chunk = $this->ChunkData[$X][$Z];

        // Pacote
        {
            $ChunkPacket = new SquarePacket($handler);
            $ChunkPacket->packetID = SquarePacketConstants::$SERVER_CHUNK_DATA;
            $ChunkPacket->WriteInt($X); // chunkX
            $ChunkPacket->WriteInt($Z); // chunkZ
            $ChunkPacket->WriteByte(true); // fullChunk
            $ChunkPacket->WriteVarInt($chunk->GetSelectionMask());

            // Heightmap Tags
            {
                $ChunkPacket->WriteByte(10);
                $ChunkPacket->WriteStringNBT("MOTION_BLOCKING"); // Root Element
                {
                    $ChunkPacket->WriteByte(12);
                    $ChunkPacket->WriteStringNBT("MOTION_BLOCKING");
                    $ChunkPacket->WriteInt(36);
                    for ($i = 0; $i < 36; $i++) {
                        $ChunkPacket->WriteLong(-1);
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
            $ChunkPacket->SendPacket();
        }
    }

    function GetChunk($START_X, $END_Z)
    {
        return @$this->ChunkData[$START_X][$END_Z];
    }

    // Carrega as chunks do disco
    function LoadChunks()
    {
        $files = array_diff(scandir($this->WorldName . "/data/"), array('.', '..'));
        foreach ($files as $file) { {
                // Leitura de chunks do arquivo
                $content = zlib_decode(file_get_contents($this->WorldName . "/data/" . $file));
                $Buffer = new SquarePacket(null);
                $Buffer->SetData($content);

                // Chunk X / Z
                $ChunkX = explode(".", $file)[0];
                $ChunkZ = explode(".", $file)[1];
                $SelectionMask = explode(".", $file)[2];

                // Cria as chunks
                {
                    Logger::getLogger("PHPServer")->info("Loading chunk for {$this->WorldName} ({$ChunkX}.{$ChunkZ}.{$SelectionMask}), please wait....");
                    $chunk = new Chunk;
                    $chunk->SelectionMask = $SelectionMask;
                    $chunk->Read($Buffer);
                    $this->SetChunk($ChunkX, $ChunkZ, $chunk);
                }
            }
        }
    }

    // Delete
    function DeleteOldChunks()
    {
        $files = glob($this->WorldName . "/data/*");
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    // Salva a chunk em disco
    function Write2Disk()
    {
        $this->DeleteOldChunks();
        foreach (array_keys($this->ChunkData) as $x) {
            foreach (array_keys($this->ChunkData[$x]) as $z) {
                Logger::getLogger("PHPServer")->info("Writing chunk to disk {$x}.{$z}.SPHP, wait....");

                // Buffer de Saida
                $Buffer = new SquarePacket(null);

                // Arquivo de chunk
                $chunkFile = $this->ChunkData[$x][$z];
                $chunkFile->serialize($Buffer);

                // Converte para string
                $string = $Buffer->Buffer2String();

                // Compress Nivel 9
                // 78 DA = Best compression
                // 78 9C = padrao
                $string = zlib_encode($string, ZLIB_ENCODING_DEFLATE, 9);

                // Salva em disco.
                $worldFile = fopen($this->WorldName . "/data/" . "{$x}.{$z}.{$chunkFile->GetSelectionMask()}.SPHP", "w");
                fwrite($worldFile, $string);
                fclose($worldFile);
            }
        }
    }

    // Salva o mundo
    function SaveWorld()
    {
        $this->Write2Disk();
    }

    function GetWorldTime()
    {
        return $this->WorldTime;
    }

    function GetWorldSeed()
    {
        return $this->WorldSeed;
    }

    function SetWorldName($val)
    {
        $this->WorldName = $val;
    }

    function GetTotalWorldTime()
    {
        return $this->TotalWorldTime;
    }

    function GetWorldDifficulty()
    {
        return $this->WorldDifficulty;
    }

    function AddEntity($entity)
    {
        $this->Entities[$this->TotalEntities++] = $entity;
    }

    function Tick()
    {
        $this->WorldTime += 20; // 20 = 1s
    }

    function SetWorldTime($value)
    {
        $this->WorldTime = $value;
    }

    function AddWorldTime($value)
    {
        $this->WorldTime += $value;
    }

    function RemoveWorldTime($value)
    {
        $this->WorldTime -= $value;
    }
}
