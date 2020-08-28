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

        Logger::getLogger("PHPServer")->info("Cliente " . $this->conn->getRemoteAddress() . " entrou no mundo!");
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

        // Packet handler
        $this->tryHandle($SquarePacket);
    }

    function SendWorldTime() {
        // TODO: Pegar em qual mundo o jogador est?, e usar no index.
        $WorldTime = $this->server->GetWorld(0)->GetWorldTime();
        $TotalWorldTime = $this->server->GetWorld(0)->GetTotalWorldTime();

        // Envia o World Time
        $WorldTimePacket = new SquarePacket($this);
        $WorldTimePacket->packetID = SquarePacketConstants::$SERVER_TIME_UPDATE;
        $WorldTimePacket->WriteLong($TotalWorldTime);
        $WorldTimePacket->WriteLong($WorldTime);
        $WorldTimePacket->SendPacket();
    }

    function sendKeepAlive()
    {
        // Manda o KeepAlive
        if ($this->conn->isWritable()) {
            $keepAlive = new KeepAlive($this);
            $keepAlive->serialize();
            Timer\resolve(1, $this->loop)->then(function () {
                $this->SendWorldTime();
                $this->sendKeepAlive();
            });
        }
    }

    function tryHandle($packet)
    {
        // Verifica se o pacote existe.
        if (!array_key_exists($packet->packetID, $GLOBALS["GamePackets"])) {
            Logger::getLogger("PHPServer")->warn("Cliente enviou um pacote invalido de ID 0x" . strtoupper(dechex($packet->packetID)) . " ({$packet->packetSize})");
            return;
        }

        // Lista de Pacotes
        $packetClassHandler = new $GLOBALS["GamePackets"][$packet->packetID]($this);

        // Leitura
        $packetClassHandler->data = $packet->data;
        $packetClassHandler->offset = $packet->offset;
        $packetClassHandler->packetSize = $packet->packetSize;
        $packetClassHandler->ServerHandler = $this->server;
        $packetClassHandler->deserialize();
    }
}

?>