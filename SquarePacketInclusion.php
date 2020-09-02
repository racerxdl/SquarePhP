<?php
 // Pre-game
 include_once 'SquarePacketList/Pre-Game-Packets/Handshake.php';
 include_once 'SquarePacketList/Pre-Game-Packets/Handshake_SERVER_MOTD.php';
 include_once 'SquarePacketList/Pre-Game-Packets/Handshake_SERVER_PONG.php';

 // In-Game
 include_once 'SquarePacketList/Game-Packets/LoginSuccess.php';
 include_once 'SquarePacketList/Game-Packets/JoinGame.php';
 include_once 'SquarePacketList/Game-Packets/Position.php';
 include_once 'SquarePacketList/Game-Packets/KeepAlive.php';
 include_once 'SquarePacketList/Game-Packets/PlayerChat.php';
 include_once 'SquarePacketList/Game-Packets/PluginMessage.php';
 include_once 'SquarePacketList/Game-Packets/WorldDifficulty.php';
 include_once 'SquarePacketList/Game-Packets/PlayerAbilities.php';
 include_once 'SquarePacketList/Game-Packets/HeldItemChange.php';
 include_once 'SquarePacketList/Game-Packets/ChunkData.php';
 include_once 'SquarePacketList/Game-Packets/PlayerMove.php';
 include_once 'SquarePacketList/Game-Packets/PlayerBlock.php';
?>