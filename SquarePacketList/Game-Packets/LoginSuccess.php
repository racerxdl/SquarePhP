<?php

include_once 'SquarePacket.php';
include_once 'SquareConstants.php';
include_once 'SquarePacketConstants.php';

class LoginSuccess extends SquarePacket
{
    public string $loginID;

    function __construct(ClientHandler $handler , string $loginID)
    {
        $this->handler = $handler;
        $this->loginID = $loginID;
    }

    function serialize()
    {
        $loginSucess = new SquarePacket($this->handler);
        $loginSucess->packetID = SquarePacketConstants::$SERVER_LOGIN;
        $loginSucess->WriteUUID(0);
        $loginSucess->WriteString($this->loginID);
        $loginSucess->SendPacket();
    }
}