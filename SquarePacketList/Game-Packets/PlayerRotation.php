<?php
class PlayerRotation extends SquarePacket {
    function deserialize()
    {
        $this->handler->GetMyPlayer()->SetYaw($this->ReadFloat());
        $this->handler->GetMyPlayer()->SetPitch($this->ReadFloat());
        $this->handler->GetMyPlayer()->SetOnGround($this->ReadByte());
    }
}
?>