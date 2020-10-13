<?php

include_once 'SquarePacket.php';
include_once 'SquareConstants.php';
include_once 'SquarePacketConstants.php';

class PlayerBlock extends SquarePacket
{
    function deserialize()
    {
        $this->DecodeVarInt();

        // Localizacao onde o bloco foi posto
        $location = $this->ReadLong();

        // Decode it
        {
            // Posicao do jogador
            $x = $location >> 38;
            $y = $location & 0xFFF;
            $z = ($location << 26 >> 38);

            // Face
            $this->DecodeVarInt();

            // Cursor Position (onde o player esta olhando)
            {
                $cursorX = $this->ReadFloat();
                $cursorY = $this->ReadFloat();
                $cursorZ = $this->ReadFloat();
            }

            // Altera a posicao
            $x += $cursorX;
            $y += $cursorY;
            $z += $cursorZ;

            // Posiciona o bloco no mundo
            $this->ServerHandler->GetWorld(0)->PlaceBlock($x, $y, $z);
        }
    }
}
