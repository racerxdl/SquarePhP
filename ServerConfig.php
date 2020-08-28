<?php

require_once "SquareConstants.php";

class ServerConfig
{
    // Descrição do servidor
    public string $description;

    // Máximo de Jogadores
    public int $maxPlayers;

    // Numero de Jogadores Online
    public int $onlinePlayers;

    // Nome do Servidor
    public string $serverName;

    function buildJson(): string
    {
        $data = array(
            "description" => array(
                "extra" => array(
                    array(
                        "text" => $this->description,
                    )
                ),
                "text" => "",
            ),
            "players" => array(
                "max" => $this->maxPlayers,
                "online" => $this->onlinePlayers,
            ),
            "version" => array(
                "name" => "PhP Quadrado",
                "protocol" => $GLOBALS["PROTOCOL_VERSION_DATA"],
            )
        );

        return json_encode($data);
    }
}