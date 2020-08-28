<?php

require_once 'ServerConfig.php';

class ServerHandler
{
    public $worlds;
    public ServerConfig $config;

    function __construct() {
        $this->config = new ServerConfig();
    }

    function clientConnect() {
        $this->config->onlinePlayers++;
    }

    function clientDisconnect() {
        $this->config->onlinePlayers--;
    }

    function getServerConfig() : string {
        return $this->config->buildJson();
    }

    function TickWorlds() {
        for ($i = 0; $i < count($this->worlds); $i++) {
            $this->worlds[$i]->Tick();
        }
    }
    function GetWorld($index) {
        return $this->worlds[$index];
    }
}