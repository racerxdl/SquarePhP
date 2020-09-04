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
    private $Entities;
    public $ChunkData;

    function __construct()
    {
        $this->WorldTime = 0;
        $this->TotalWorldTime = 0;
        $this->TotalEntities = 0;
        $this->TotalChunks = 0;
        $this->RenderDistance = 8;
        $this->WorldName = "world";
        $this->Entities = array();
        $this->WorldDifficulty = 3; // Hard
        $this->WorldSeed = round(microtime(true) * 1000);
        $this->ChunkData = array();
    }

    function GetRenderDistance()
    {
        return $this->RenderDistance;
    }

    function SetChunk($START_X, $END_Z, $chunk)
    {
        $this->ChunkData[$START_X][$END_Z] = $chunk;
    }

    function GetTotalChunks() {
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
        $chunk = new Chunk;
        for ($x = 0; $x < 16; $x++) {
            for ($z = 0; $z < 16; $z++) {
                $chunk->SetBlock($x, 0, $z, 33); // bedrock
                $chunk->SetBlock($x, 1, $z, 10); // terra
                $chunk->SetBlock($x, 2, $z, 10); // terra
                $chunk->SetBlock($x, 3, $z, 9); // grama
            }
        }
        $this->SetChunk($X, $Z, $chunk);
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
                    $ChunkPacket->WriteStringNBT("");
                    $ChunkPacket->WriteInt(36);
                    for ($i = 0; $i < 36; $i++) {
                        $ChunkPacket->WriteLong($i);
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
