<?php

use React\Promise\Timer;

include_once 'SquarePacket.php';
include_once 'SquareConstants.php';

class ClientHandler
{
    // Conexao com cliente
    public $conn;

    // Loop de Eventos do ReactPHP
    public $loop;

    function __construct(React\EventLoop\StreamSelectLoop $loop, React\Socket\ConnectionInterface $conn)
    {
        $this->loop = $loop;
        $this->conn = $conn;
        echo("Nova conexão de " . $conn->getRemoteAddress() . PHP_EOL);
    }

    function do()
    {
        $this->conn->on('data', function ($data) {
            $this->onData($data);
        });
    }

    function onData($data)
    {
        // Retorna a classe Packet
        $SquarePacket = DecodePacket($this, $data);

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