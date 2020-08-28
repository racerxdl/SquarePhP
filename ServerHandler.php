<?php

require_once 'ServerConfig.php';

class ServerHandler
{
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
}