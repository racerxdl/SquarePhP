<?php

require_once 'ServerConfig.php';

class ServerHandler
{
    public $worlds;
    public ServerConfig $config;
    public $PlayerList;

    function __construct()
    {
        $this->config = new ServerConfig();
        $this->PlayerList = array();
    }

    function AddPlayer($Player)
    {
        array_push($this->PlayerList, $Player);
    }

    function RemovePlayer($Player)
    {
        $key = array_search($Player, $this->PlayerList);
        if ($key != null) {
            unset($this->PlayerList[$key]);
        }
    }

    function clientConnect()
    {
        $this->config->onlinePlayers++;
    }

    function clientDisconnect()
    {
        $this->config->onlinePlayers--;
    }

    function getServerConfig(): string
    {
        return $this->config->buildJson();
    }

    function TickWorlds()
    {
        for ($i = 0; $i < count($this->worlds); $i++) {
            $this->worlds[$i]->Tick();
        }
    }
    function GetWorld($index)
    {
        return $this->worlds[$index];
    }
}
