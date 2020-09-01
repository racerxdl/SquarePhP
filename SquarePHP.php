<?php

include_once __DIR__ . '/vendor/autoload.php';
include_once 'ClientHandler.php';
include_once 'ServerHandler.php';
include_once 'SquareWorld/World.php';
use React\Promise\Timer;

// Listas
$worldList = array("world", "world_the_nether", "world_the_end");
$worlds = array();
$serverHandler = new ServerHandler;

{
    // Lista de Mundos
    for ($i = 0; $i < count($worldList); $i++) {
        if (!file_exists($worldList[$i])) {
            Logger::getLogger("PHPServer")->info("Criando o mundo {$worldList[$i]}, por favor aguarde...");
            mkdir($worldList[$i]);
        }
        $world = new World;
        $world->SetWorldName($worldList[$i]);;
        array_push($worlds, $world);
    }
}

Logger::getLogger("PHPServer")->info("Pronto para conexoes!");
{
    $loop = React\EventLoop\Factory::create();
    $socket = new React\Socket\Server('0.0.0.0:25565', $loop);

    $serverHandler->config->description = "Seja bem-vindo ao NaN e ao undefined is not defined";
    $serverHandler->config->maxPlayers = 10;
    $serverHandler->config->onlinePlayers = 0;
    $serverHandler->config->pngImage = $serverHandler->config->buildFavIcon();
    $serverHandler->worlds = $worlds;

    $socket->on('connection', function (React\Socket\ConnectionInterface $connection) use ($loop, $serverHandler) {
        $clientHandler = new ClientHandler($loop, $serverHandler, $connection);
        $clientHandler->do();
    });

    // World Tick - A cada 1s
    $loop->addPeriodicTimer(1, function () use($serverHandler) {
        $serverHandler->TickWorlds();
    });

    $loop->run();
}
