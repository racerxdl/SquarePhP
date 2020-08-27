<?php
   include_once 'SquarePacket.php';
   include_once 'SquarePacketList.php';
   class SquarePacketHandler {
     function tryHandle($packet) {
        
        // Verifica se o pacote existe.
        if (!array_key_exists($packet->packetID, $GLOBALS["packetDecoders"])) {
            return;
        }

        // Tenta ler o pacote.
        $packetClassHandler = $GLOBALS["packetDecoders"][$packet->packetID];
        $packetClassHandler->data = $packet->data;
        $packetClassHandler->offset = $packet->offset;
        $packetClassHandler->originSocket = $packet->originSocket;
        $packetClassHandler->packetSize = $packet->packetSize;
        $packetClassHandler->deserialize();
     } 
   }
?>