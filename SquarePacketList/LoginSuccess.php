<?php

include_once 'SquarePacket.php';
include_once 'SquareConstants.php';

class LoginSuccess extends SquarePacket
{
    function serialize()
    {
        $loginSucess = new SquarePacket($this->handler);
        $loginSucess->packetID = 0x2;
        $loginSucess->WriteUUID(999); // UUID, não importa agora.
        $loginSucess->WriteString("An0nyMoUS");
        $loginSucess->SendPacket();
    }

}