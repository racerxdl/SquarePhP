<?php

use React\Promise\Timer;

include_once 'SquarePacket.php';
include_once 'SquareConstants.php';

class ClientHandler
{
    // Gerenciador do servidor
    public ServerHandler $server;

    // Conexao com cliente
    public $conn;

    // Loop de Eventos do ReactPHP
    public $loop;

    // Se o player já entrou no mundo
    public bool $isClientJoined;

    function __construct(React\EventLoop\StreamSelectLoop $loop, ServerHandler $server, React\Socket\ConnectionInterface $conn)
    {
        $this->server = $server;
        $this->loop = $loop;
        $this->conn = $conn;
        $this->isClientJoined = false;
        echo("Nova conexão de " . $conn->getRemoteAddress() . PHP_EOL);
    }

    function onJoin() {
        echo "Cliente " . $this->conn->getRemoteAddress() . " entrou no mundo!" . PHP_EOL;
        $this->isClientJoined = true;
        $this->server->clientConnect();
        // Spawn Position.
        $Position = new Position($this);
        $Position->serialize();

        // Start Pining
        $this->sendKeepAlive();
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
            echo "O " . $this->conn->getRemoteAddress() . " foi de base..." . PHP_EOL;
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
        echo "Packet ID {$SquarePacket->packetID}, size {$SquarePacket->packetSize} \n";

        // Packet handler
        $this->tryHandle($SquarePacket);
    }

    function sendKeepAlive()
    {
        // Manda o KeepAlive
        echo("Keep Alive\n");
        if ($this->conn->isWritable()) {
            $keepAlive = new KeepAlive($this);
            $keepAlive->serialize();
            Timer\resolve(5, $this->loop)->then(function () {
                $this->sendKeepAlive();
            });
        } else {
            echo("Parando keep alive. Conexão caiu!" . PHP_EOL);
        }
    }

    function tryHandle($packet)
    {

        // Verifica se o pacote existe.
        if (!array_key_exists($packet->packetID, $GLOBALS["packetDecoders"])) {
            return;
        }

        // Tenta ler o pacote.
        $packetClassHandler = new $GLOBALS["packetDecoders"][$packet->packetID]($this);
        $packetClassHandler->data = $packet->data;
        $packetClassHandler->offset = $packet->offset;
        $packetClassHandler->packetSize = $packet->packetSize;
        $packetClassHandler->deserialize();
    }
}

?>