<?php
include_once 'SquarePacket.php';
include_once 'SquareConstants.php';
include_once 'SquarePacketConstants.php';
class ServerPluginMessage extends SquarePacket
{
    function serialize()
    {
        $PluginMessage = new SquarePacket($this->handler);
        $PluginMessage->packetID = SquarePacketConstants::$SERVER_PLUGIN_MESSAGE;
        
        // https://wiki.vg/Plugin_channels
        // So importa esse, o resto nao importa por agora.
        $PluginMessage->WriteString("minecraft:brand");
        $PluginMessage->WriteString("Sim, servidor de Minecraft em PHP.");
        $PluginMessage->SendPacket();
    }
}