<?php
 include_once 'SquarePacket.php';
 include_once 'SquarePacketInclusion.php';
 class PlayerChat extends SquarePacket {
     public $mJsonMinecraft = "";
     public $rawMinecraftMessage = "";
     function deserialize() {

         // Mensagem em raw text.
         $mChatMessage = $this->ReadString();

         // Converte para JSON
         $temp = array(
             "translate" => "chat.type.text",
             "with" => array(
                array(
                    "text" => $this->handler->GetMyPlayer()->GetPlayerName(), // Nick do Usuario
                ),
                array(
                    "text" => $mChatMessage,
                ),
             ),
         );

         $this->mJsonMinecraft = json_encode($temp);
         $this->rawMinecraftMessage = $mChatMessage;
         $this->serialize();
     }     
     
     function serialize() {
        if (strncmp($this->rawMinecraftMessage, '/', 1) == 0) {
            // todo: criar uma classe de comandos 
            // /time set valor
            if (strpos($this->rawMinecraftMessage, "time")) {
                $splitter = explode(" ", $this->rawMinecraftMessage);
                switch ($splitter[1]) {
                    case "set":
                        $this->ServerHandler->GetWorld(0)->SetWorldTime(intval($splitter[2]));
                    break;
                    case "add":
                        $this->ServerHandler->GetWorld(0)->AddWorldTime(intval($splitter[2]));
                    break;
                    case "remove":
                        $this->ServerHandler->GetWorld(0)->RemoveWorldTime(intval($splitter[2]));
                    break;
                }
                return;
            }

            // todo: criar uma classe de comandos 
            // salva os mundos
            // /save-all
            if (strpos($this->rawMinecraftMessage, "save-all")) {
                $this->ServerHandler->GetWorld(0)->SaveWorld();
                return;
            }
            return;
        } else {
            $ChatMessage = new SquarePacket($this->handler);
            $ChatMessage->packetID = SquarePacketConstants::$SERVER_PLAYER_CHAT;
            $ChatMessage->WriteString($this->mJsonMinecraft);
            $ChatMessage->WriteByte(0);
            $ChatMessage->WriteUUID(0);
            $this->ServerHandler->GetWorld(0)->BroadCastPacket($ChatMessage);
            return;    
        }
     }
 }
