<?php

class SquarePacket
{

    // Pacote original com todos os byte[] array.
    public $data = array();

    // Posi??o atual
    public $offset = 0;

    // Packet ID
    public $packetID = 0;

    // Packet Size
    public $packetSize = 0;

    // Client Handler do cliente
    public $handler;

    // Server Handler
    public $ServerHandler;

    function __construct(?ClientHandler $handler)
    {
        $this->handler = $handler;
    }

    // Metodos para serializacao.
    function DecodeVarInt(): int
    {
        $numRead = 0;
        $result = 0;
        do {
            $read = ord($this->data[$this->offset++]);
            $value = ($read & 0b01111111);
            $result |= ($value << (7 * $numRead));
            $numRead++;
            if ($numRead > 5) {
                throw new Exception("VarInt is too big");
            }
        } while (($read & 0b10000000) != 0);
        return $result;
    }

    // Leitura de String
    function ReadString(): string
    {
        $stringLength = $this->DecodeVarInt();
        $str = "";
        for ($i = 0; $i < $stringLength; $i++) {
            $str .= chr(ord($this->data[$this->offset++]));
        }
        return trim($str);
    }

    // Unicode Strings
    function ReadUnicodeString(): string
    {
        $stringLength = $this->DecodeVarInt() * 2;
        $str = "";
        for ($i = 0; $i < $stringLength; $i++) {
            $str .= chr(ord($this->data[$this->offset++]));
        }
        return trim($str);
    }

    // Read Byte
    function ReadByte(): int
    {
        return ord($this->data[$this->offset++]);
    }

    // Read Int
    function ReadInt(): int
    {
        return ($this->ReadByte() << 24) + ($this->ReadByte() << 16) + ($this->ReadByte() << 8) + $this->ReadByte();
    }

    // Read Short
    function ReadShort(): int
    {
        return (($this->ReadByte() << 8) + $this->ReadByte());
    }

    // Short - Little-endian
    function ReadShortLE(): int
    {
        return $this->ReadByte() + ($this->ReadByte() << 8);
    }

    // Read Long
    function ReadLong(): int
    {
        return ($this->ReadByte() << 56) + ($this->ReadByte() << 48) + ($this->ReadByte() << 40) + ($this->ReadByte() << 32) +
            ($this->ReadByte() << 24) + ($this->ReadByte() << 16) + ($this->ReadByte() << 8) + $this->ReadByte();
    }

    // Read Double
    function ReadDouble() {
        $array = unpack('E', $this->data, $this->offset);
        $this->offset += 8;
        return $array[1];
    }

    // Read Double
    function ReadFloat() {
        $array = unpack('G', $this->data, $this->offset);
        $this->offset += 4;
        return $array[1];
    }

    // Write Byte
    function WriteByte($value)
    {
        $this->data[$this->offset++] = $value;
    }

    // WriteInt
    function WriteInt($i)
    {
        $this->WriteByte(($i >> 24) & 0xFF);
        $this->WriteByte(($i >> 16) & 0xFF);
        $this->WriteByte(($i >> 8) & 0xFF);
        $this->WriteByte($i & 0xFF);
    }

    function floatToIntBits($float_val)
    {
        $int = unpack('i', pack('f', $float_val));
        return $int[1];
    }

    // Write Float
    function WriteFloat($i)
    {
        $i = $this->floatToIntBits($i);
        $this->WriteInt($i);
    }


    // Write Long
    function WriteLong($i)
    {
        $this->WriteByte(($i >> 56) & 0xFF);
        $this->WriteByte(($i >> 48) & 0xFF);
        $this->WriteByte(($i >> 40) & 0xFF);
        $this->WriteByte(($i >> 32) & 0xFF);
        $this->WriteByte(($i >> 24) & 0xFF);
        $this->WriteByte(($i >> 16) & 0xFF);
        $this->WriteByte(($i >> 8) & 0xFF);
        $this->WriteByte($i & 0xFF);
    }

    // Write Short
    function WriteShort($i)
    {
        $this->WriteByte(($i << 8) & 0xFF);
        $this->WriteByte($i & 0xFF);
    }

    function WriteVarInt($value)
    {
        $value &= 0xFFFFFFFF; // Signed to Unsigned. (https://en.wikipedia.org/wiki/LEB128, https://wiki.vg/Protocol)
        do {
            $temp = ($value & 0b01111111);
            // Note: >>> means that the sign bit is shifted with the rest of the number rather than being left alone
            $value >>= 7;
            if ($value != 0) {
                $temp |= 0b10000000;
            }
            $this->WriteByte($temp);
        } while ($value != 0);
    }

    // https://en.wikipedia.org/wiki/Universally_unique_identifier
    function WriteUUID($value)
    {
       $this->WriteLong($value);
       $this->WriteLong($value);
    }

    // Double
    function WriteDouble($value)
    {
       // E	double (machine dependent size, big endian byte order)
       // https://www.php.net/manual/pt_BR/function.pack.php
       $temp = pack("E", $value);
       for ($i = 0; $i < strlen($temp); $i++) {
           $this->WriteByte(ord($temp[$i]));
       }
    }

    // Write String
    function WriteString($value)
    {
        $stringSize = strlen($value);
        $this->WriteVarInt($stringSize);
        for ($i = 0; $i < $stringSize; $i++) {
            $this->WriteByte(ord($value[$i]));
        }
    }

    // SHORT
    function WriteStringNBT($value)
    {
        $stringSize = strlen($value);
        $this->WriteShort($stringSize);
        for ($i = 0; $i < $stringSize; $i++) {
            $this->WriteByte(ord($value[$i]));
        }
    }

    function WriteUnicodeString($value)
    {
        // UTF-16
        $utf16String = mb_convert_encoding($value, "UTF-16LE", "UTF-8");
        $stringSize = strlen($utf16String);

        // Tamanho / 2.
        $this->WriteVarInt($stringSize / 2);
        for ($i = 0; $i < $stringSize; $i++) {
            $this->WriteByte(ord($utf16String[$i]));
        }
    }

    function WriteStringArray($data)
    {
        $this->WriteVarInt(strlen($data));
        for ($i = 0; $i < strlen($data); $i++) {
            $this->WriteByte(ord($data[$i]));
        }
    }

    function WriteArray($data)
    {
        $this->WriteVarInt(count($data));
        for ($i = 0; $i < count($data); $i++) {
            $this->WriteByte(ord($data[$i]));
        }
    }

    function SetData($string) {
        for ($i = 0; $i < strlen($string); $i++) {
            $this->WriteByte($string[$i]);
        }
        $this->offset = 0;
    }

    function GetData() {
        return $this->data;
    }

    function GetFullDataLength() {
        return strlen($this->data);
    }

    function GetDataLength() {
        return $this->offset;
    }

    function Buffer2String() {
        $string = "";
        $array = $this->GetData();
        for ($i = 0; $i < count($array); $i++) {
            $string .= chr($array[$i]);
        }
        return $string;
    }

    function PrintHexString() {
        $buffer = "";
        for ($i = 0; $i < strlen ($this->data); $i++) {
            $buffer .= chr(ord($this->data[$i]));
        }
        return bin2hex($buffer);
    }

    // Send Packet 
    function SendPacket()
    {
        // Header
        $header = array();
        $headerOffset = 0;

        // Pacote completo
        $fullPacket = array();
        $fullPacketOffset = 0;

        // PacketID
        $packetID = $this->packetID;

        // String de saida
        $byteArray = "";

        // Header = (PacketID + data);
        {
            do {
                $temp = ($packetID & 0b01111111);
                // Note: >>> means that the sign bit is shifted with the rest of the number rather than being left alone
                $packetID >>= 7;
                if ($packetID != 0) {
                    $temp |= 0b10000000;
                }
                $header[$headerOffset++] = $temp;
            } while ($packetID != 0);
        }

        // Copia a informacao do pacote
        for ($i = 0; $i < count($this->data); $i++) {
            $header[$headerOffset++] = $this->data[$i];
        }

        // Tamanho do Pacote
        $packetLength = count($header);

        // Insere o tamanho do pacote.
        {
            do {
                $temp = ($packetLength & 0b01111111);
                // Note: >>> means that the sign bit is shifted with the rest of the number rather than being left alone
                $packetLength >>= 7;
                if ($packetLength != 0) {
                    $temp |= 0b10000000;
                }
                $fullPacket[$fullPacketOffset++] = $temp;
            } while ($packetLength != 0);
        }

        // Insere o header
        for ($i = 0; $i < count($header); $i++) {
            $fullPacket[$fullPacketOffset++] = $header[$i];
        }

        // Converte em string.
        for ($i = 0; $i < count($fullPacket); $i++) {
            $byteArray .= chr($fullPacket[$i]);
        }

        // Escreve se estiver dispon?vel.
        if ($this->handler->conn->isWritable()) {
            $this->handler->conn->write($byteArray);
            //Logger::getLogger("PHPServer")->info("Enviando para o player " . bin2hex($byteArray));
        }
    }


    function deserialize()
    {
        // Usado nas classes de pacotes.
    }

    function serialize()
    {
        // Usado nas classes de pacotes.
    }
}