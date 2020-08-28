<?php

use React\Promise\Timer;

include_once 'SquarePacket.php';
include_once 'SquareConstants.php';
include_once 'SquarePacketInclusion.php';

class ClientHandler
{
    // Gerenciador do servidor
    public ServerHandler $server;

    // Conexao com cliente
    public $conn;

    // Loop de Eventos do ReactPHP
    public $loop;

    // Se o player ja entrou no mundo
    public bool $isClientJoined;

    function __construct(React\EventLoop\StreamSelectLoop $loop, ServerHandler $server, React\Socket\ConnectionInterface $conn)
    {
        $this->server = $server;
        $this->loop = $loop;
        $this->conn = $conn;
        $this->isClientJoined = false;
    }

    function onJoin() {  

        // For unauthenticated ("cracked"/offline-mode) and localhost connections (either of the two conditions is enough for an unencrypted connection) there is no encryption. In that case Login Start is directly followed by Login Success.
        $loginSuccess = new LoginSuccess($this, "PHPServer");
        $loginSuccess->serialize();
 
        // Join game
        $JoinGame = new JoinGame($this);
        $JoinGame->serialize();        

        // Spawn Position.
        $Position = new Position($this);
        $Position->serialize();

        // Start Ping
        $this->sendKeepAlive();

        // Variaveis
        $this->isClientJoined = true;
        $this->server->clientConnect();

        echo "Cliente " . $this->conn->getRemoteAddress() . " entrou no mundo!" . PHP_EOL;
    }

    function do()
    {
        $this->conn->on('data', function ($data) {
            $this->onData($data);
        });

        $this->conn->on('end', function () {
            if ($this->isClientJoined) {
                $this->server->clientDisconnect();
            }
        });
        $this->conn->on('error', function () {
            if ($this->isClientJoined) {
                $this->server->clientDisconnect();
            }
        });
    }

    static function DecodePacket($handler, $data) {
        // Pacote
        $packet = new SquarePacket($handler);
        $packet->data = $data;

        // Pacote normais possui tamanho e packet ID.
        $packet->packetSize = $packet->DecodeVarInt();
        $packet->packetID = $packet->DecodeVarInt();

        // Return data.
        return $packet;
    }

    function onData($data)
    {
        // Retorna a classe Packet
        $SquarePacket = ClientHandler::DecodePacket($this, $data);

        // base concluida.
        echo "Packet ID {$SquarePacket->packetID}, size {$SquarePacket->packetSize}" . PHP_EOL;

        // Packet handler
        $this->tryHandle($SquarePacket);
    }

    function sendKeepAlive()
    {
        // Manda o KeepAlive
        if ($this->conn->isWritable()) {
            $keepAlive = new KeepAlive($this);
            $keepAlive->serialize();
            Timer\resolve(5, $this->loop)->then(function () {
                $this->sendKeepAlive();
            });
        }
    }

    function tryHandle($packet)
    {
        // Verifica se o pacote existe.
        if (!array_key_exists($packet->packetID, $GLOBALS["GamePackets"])) {
            return;
        }

        // Lista de Pacotes
        $packetClassHandler = new $GLOBALS["GamePackets"][$packet->packetID]($this);

        // Leitura
        $packetClassHandler->data = $packet->data;
        $packetClassHandler->offset = $packet->offset;
        $packetClassHandler->packetSize = $packet->packetSize;
        $packetClassHandler->deserialize();
    }
}

?>