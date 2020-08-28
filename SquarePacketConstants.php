<?php
 class SquarePacketConstants {

    // Pre-Handshake
    public static $SERVER_HANDSHAKE = 0x0; 
    public static $SERVER_PONG = 0x1; 
    public static $LOGIN_START = 0x0;
    public static $SERVER_ENCRYPT_REQUEST = 0x1;

    // Client Packets
    public static $CLIENT_PLAYER_KEEP_ALIVE = 0x1F;
    public static $CLIENT_PLAYER_CHAT = 0x3;

    // Server Packets
    public static $SERVER_LOGIN = 0x2;
    public static $SERVER_JOIN_GAME = 0x24;
    public static $SERVER_POSITION = 0x34;
    public static $SERVER_PLAYER_CHAT = 0x0E;
 }
