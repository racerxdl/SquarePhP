<?php
class SquarePacketConstants
{

   // Pre-Handshake
   public static $SERVER_HANDSHAKE = 0x0;
   public static $SERVER_PONG = 0x1;
   public static $LOGIN_START = 0x0;
   public static $SERVER_ENCRYPT_REQUEST = 0x1;

   // Client Packets
   public static $CLIENT_PLAYER_KEEP_ALIVE = 0x1F;
   public static $CLIENT_PLAYER_CHAT = 0x3;
   public static $CLIENT_PLAYER_MOVE = 0x12;
   public static $CLIENT_PLACE_BLOCK = 0x2E;
   public static $CLIENT_PLAYER_ROTATION = 0x14;
   public static $CLIENT_PLAYER_POSITION_AND_ROTATION = 0x13;

   // Server Packets
   public static $SERVER_LOGIN = 0x2;
   public static $SERVER_JOIN_GAME = 0x24;
   public static $SERVER_POSITION = 0x34;
   public static $SERVER_PLAYER_CHAT = 0x0E;
   public static $SERVER_TIME_UPDATE = 0x4E;
   public static $SERVER_PLUGIN_MESSAGE = 0x17;
   public static $SERVER_WORLD_DIFFICULTY = 0x0D;
   public static $SERVER_PLAYER_ABILITIES = 0x30;
   public static $SERVER_HELD_ITEM = 0x3F;
   public static $SERVER_CHUNK_DATA = 0x20;
   public static $SERVER_UPDATE_VIEW_POSITION = 0x40;
   public static $SERVER_PLAYER_CONFIRM_MOVEMENT = 0x34;
   public static $SERVER_UNLOAD_CHUNK = 0x1C;
   public static $SERVER_VIEW_DISTANCE = 0x42;
   public static $SERVER_PLAYER_LIST = 0x32;
   public static $SERVER_PLAYER_SPAWN = 0x4;
   public static $SERVER_ENTITY_START = 0x2A;
   public static $SERVER_ENTITY_TELEPORT = 0x56;
   public static $SERVER_DESTROY_ENTITY = 0x36;
}

