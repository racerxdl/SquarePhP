<?php
  include_once 'SquarePacketList/Handshake.php';
  include_once 'SquarePacketList/Handshake_SERVER_PONG.php';
  $PROTOCOL_VERSION_DATA = 751;
  $packetDecoders = array(
    0x00 => new Handshake,
    0x01 => new Handshake_SERVER_PONG,
  );
?>