<?php

class Handshake_SERVER_MOTD extends SquarePacket
{
    function serialize()
    {
        // Send MOTD
        $SERVER_LIST_PING = new SquarePacket($this->handler);
        $SERVER_LIST_PING->packetID = 0;
        $SERVER_LIST_PING->WriteString($this->handler->server->getServerConfig());
        $SERVER_LIST_PING->SendPacket();

        // Send PONG
        $SERVER_PONG = new HANDSHAKE_SERVER_PONG($this->handler);
        $SERVER_PONG->serialize();
    }
}

?>