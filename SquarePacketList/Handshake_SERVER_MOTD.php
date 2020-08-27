<?php
  class Handshake_SERVER_MOTD extends SquarePacket {
      function serealize() {

        // Send MOTD
        $SERVER_LIST_PING = new SquarePacket;
        $SERVER_LIST_PING->originSocket = $this->originSocket;
        $SERVER_LIST_PING->packetID = 0;
        $SERVER_LIST_PING->WriteString("{\"description\":{\"extra\":[{\"text\":\"vai tomar no cu bagulho dificil do caralho\"}],\"text\":\"\"},\"players\":{\"max\":20,\"online\":0},\"version\":{\"name\":\"FUNCIONOU MERDA\",\"protocol\":751}}");
        $SERVER_LIST_PING->SendPacket();

        // Send PONG
        $SERVER_PONG = new HANDSHAKE_SERVER_PONG;
        $SERVER_PONG->originSocket = $this->originSocket;
        $SERVER_PONG->serealize();
      }
  }
?>