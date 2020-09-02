<?php
include_once 'Array/PaleteArray.php';
include_once 'SquarePacket.php';

// Suporta ate 16x16x16 blocos. (x = 16, y = 16, z = 16);
class ChunkSelection
{
    public $Array;
    public $BlockList;

    function __construct()
    {
        $this->Array = new PaleteArray(4, 4096);
        $this->BlockList = array();
        $this->SetBlock(0, 0, 0, 0);
    }

    function getBlockIndex($x, $y, $z)
    {
        return ($y << 8) | ($z << 4) | $x;
    }

    function SetBlock($x, $y, $z, $blockID)
    {
        $BlockListID = array_search($blockID, $this->BlockList);
        if ($BlockListID == null) {
            array_push($this->BlockList, $blockID);
            $BlockListID = array_search($blockID, $this->BlockList);
        }
        $this->Array->SetNumber($this->getBlockIndex($x, $y, $z), $BlockListID);
    }

    function GetSolidBlocks()
    {
        $solidBlocks = 0;
        foreach ($this->BlockList as $v) {
            $solidBlocks++;
        }
        return $solidBlocks;
    }

    function Read($Buffer)
    {
        // Quantidade de blocos s?lidos.
        $solidBlocks = $Buffer->ReadShort();

        // Paleta
        $Pallete = $Buffer->ReadByte();

        // Total de Blocos presentes na chunk
        $BlockLength = $Buffer->DecodeVarInt();
        for ($i = 0; $i < $BlockLength; $i++) {
            $this->BlockList[$i] = $Buffer->DecodeVarInt();
        }

        // Tamanho da paleta
        $PalleteLength = $Buffer->DecodeVarInt();
        for ($i = 0; $i < $PalleteLength; $i++) {
            $this->Array->SetHardPosition($i, $Buffer->ReadLong());
        }
    }

    function Serialize($Output)
    {
        $PositionBlocks = count($this->Array->getPalette());

        if ($PositionBlocks != 256) {
            Logger::getLogger("PHPServer")->error("Quantidade de blocos ultrapassa 256 {$PositionBlocks}.");
            return;
        }

        $Buffer = new SquarePacket($Output->handler);

        // Number of non-air blocks present in the chunk section, for lighting purposes. "Non-air" is defined as any block other than air, cave air, and void air (in particular, note that fluids such as water are still counted).
        $Buffer->WriteShort($this->GetSolidBlocks());

        // Determines how many bits are used to encode a block. Note that not all numbers are valid here.
        $Buffer->WriteByte(4);

        // Lista de Blocos
        $Buffer->WriteVarInt(count($this->BlockList));
        foreach ($this->BlockList as $b) {
            $Buffer->WriteVarInt($b);
        }

        // Posicao dos blocos
        $Buffer->WriteVarInt(count($this->Array->getPalette()));
        foreach ($this->Array->getPalette() as $b) {
            $Buffer->WriteLong($b);
        }
        return $Buffer;
    }
}

// Suporta 16x256x16 (x = 16, y = 256, z = 16);
class Chunk
{
    public $ChunkSelections;
    public $SelectionMask;

    function __construct()
    {
        $this->ChunkSelections = array();
    }

    function SetBlock($X, $Y, $Z, $BlockID)
    {
        // 16x256x16
        if ($Y > 256) return;

        // 256 / 16 = Usar? a selection 8.
        $SelectionIndex = $Y / 16;

        // Se nao existir 
        if (!array_key_exists((int)$SelectionIndex, $this->ChunkSelections)) {
            if ($BlockID != 0) {
                $this->ChunkSelections[$SelectionIndex] = new ChunkSelection;
                $this->SelectionMask |= 1 << $SelectionIndex;
            }
        }

        // Aplica o bloco na posicao.
        $this->ChunkSelections[$SelectionIndex]->SetBlock($X, $Y & 0xF, $Z, $BlockID);
    }

    function GetSelectionMask()
    {
        return $this->SelectionMask;
    }

    function Read($buffer)
    {
        $buffer->DecodeVarInt();
        for ($i = 0; $i < 16; $i++) {
            if (($this->SelectionMask & (1 << $i)) != 0) {
                $chunkSelection = new ChunkSelection;
                $chunkSelection->Read($buffer);
                $this->ChunkSelections[$i] = $chunkSelection;
            }
        }
    }

    function Serialize($Output)
    {
        // Selections que existem
        $Selections = array();

        // Tamanho total para enviar no pacote
        $TotalSelectionLength = 0;

        // Carrega as selections e verifica se existe.
        for ($i = 0; $i < 16; $i++) {
            if (($this->SelectionMask & (1 << $i)) != 0) {
                $Selections[$i] = $this->ChunkSelections[$i]->Serialize($Output);
                $TotalSelectionLength += $Selections[$i]->GetDataLength();
            }
        }

        // Tamanho total
        $Output->WriteVarInt($TotalSelectionLength);
        for ($i = 0; $i < 16; $i++) {
            if (($this->SelectionMask & (1 << $i)) != 0) {
                $SelectionBuffer = $Selections[$i];
                for ($j = 0; $j < $SelectionBuffer->GetDataLength(); $j++) {
                    $Output->WriteByte($SelectionBuffer->GetData()[$j]);
                }
            }
        }
    }
}
