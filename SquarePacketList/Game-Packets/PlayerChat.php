<?php
 include_once 'SquarePacket.php';
 include_once 'SquarePacketInclusion.php';
 class PlayerChat extends SquarePacket {
     public $mJsonMinecraft = "";
     function deserialize() {

         // Mensagem em raw text.
         $mChatMessage = $this->ReadString();

         // Converte para JSON
         $temp = array(
             "translate" => "chat.type.text",
             "with" => array(
                array(
                    "text" => "PHPServer", // Nick do Usuario
                ),
                array(
                    "text" => $mChatMessage,
                ),
             ),
         );

         $this->mJsonMinecraft = json_encode($temp);
         $this->serialize();
     }     
     
     function serialize() {
        $ChatMessage = new SquarePacket($this->handler);
        $ChatMessage->packetID = SquarePacketConstants::$SERVER_PLAYER_CHAT;
        $ChatMessage->WriteString($this->mJsonMinecraft);
        $ChatMessage->WriteByte(0);
        $ChatMessage->WriteUUID(0);
        $ChatMessage->SendPacket();    
     }
 }
?>