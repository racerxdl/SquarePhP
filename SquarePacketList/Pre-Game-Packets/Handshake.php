<?php
include_once 'SquarePacket.php';
include_once 'SquareConstants.php';
include_once 'SquarePacketInclusion.php';

class Handshake extends SquarePacket
{
    function deserialize()
    {
        // Login
        if ($this->handler->State == 2) {
            $this->handler->onJoin($this->ReadString());
            return;
        }

        // ACK
        $handACK = new Handshake_SERVER_MOTD($this->handler);

        // As vezes o cliente envia um pacote de tamanho 1. Provavelmente seja o disconnect.
        // Ou talvez, seja cache?
        if ($this->packetSize == 1) {
            $handACK->serialize();
            return;
        }

        // Numero de Protocolo.
        $protocolVersion = $this->DecodeVarInt();

        // Verifica se o protocolo e valido.
        if ($protocolVersion != $GLOBALS["PROTOCOL_VERSION_DATA"]) {
            return;
        }

        // Server Address
        $serverIP = $this->ReadString();
        $serverPort = $this->ReadShort();
        $nextState = $this->DecodeVarInt();

        // Next-state
        // https://wiki.vg/Server_List_Ping
        switch ($nextState) {
            case 1:
                $handACK->serialize();
                break;
            case 2:
                $this->handler->State = $nextState;
                if ($this->GetDataLength() != $this->GetFullDataLength()) {
                    $this->handler->onJoin($this->ReadString());
                }
                break;
        }
    }
}
