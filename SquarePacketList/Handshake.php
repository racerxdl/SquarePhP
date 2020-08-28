<?php
include_once 'SquarePacket.php';
include_once 'SquareConstants.php';
include_once 'Handshake_SERVER_MOTD.php';
include_once 'ENCRYPT_REQUEST.php';
include_once 'JoinGame.php';
include_once 'Position.php';
include_once 'LoginSuccess.php';

class Handshake extends SquarePacket
{
    function deserialize()
    {
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

        // Verifica se o protocolo e válido.
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
                // For unauthenticated ("cracked"/offline-mode) and localhost connections (either of the two conditions is enough for an unencrypted connection) there is no encryption. In that case Login Start is directly followed by Login Success.
                $loginSuccess = new LoginSuccess($this->handler);
                $loginSuccess->serialize();

                // Join game
                $JoinGame = new JoinGame($this->handler);
                $JoinGame->serialize();

                // Handle Join
                $this->handler->onJoin();

                break;
        }
    }
}

?>