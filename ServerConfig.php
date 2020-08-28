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

    // PNG
    public string $pngImage;

    // Carrega a imagem .png
    function buildFavIcon() {
        if (file_exists ("favicon.png")) {
            $path = 'favicon.png';
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            return 'data:image/png;base64,' . base64_encode($data);
        }        
    }

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
            ),
            "favicon" => $this->pngImage,    
        );
        return json_encode($data);
    }
}