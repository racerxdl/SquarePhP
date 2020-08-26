<?php

function DecodeVarInt($data) : Array {
    $numRead = 0;
    $result = 0;

    do {
        $read = ord($data[0]);
        $data = substr($data, 1);

        $value = ($read & 0b01111111);
        $result |= ($value << (7 * $numRead));
        $numRead++;

        if ($numRead > 5) {
            throw new Exception("VarInt is too big");
        }
    } while (($read & 0b10000000) != 0);

    return Array(
        "value" => $result,
        "data" => $data,
    );
}

function DecodePacket($data) {
    echo("NAO SEI O QUE TO FAZENDO\n");
    $result = DecodeVarInt($data);
    $tamanho = $result["value"];
    $data = $result["data"];
    $result = DecodeVarInt($data);
    $packetId = $result["value"];
    $data = $result["data"];
    echo("Tamanho do pacote: " . $tamanho . "\n");
    echo("ID do pacote: " . $packetId . "\n");
    echo("RESTO: " . $data . "\n");

    return array(
        "len" => $tamanho,
        "id" => $packetId,
        "data" => $data,
    );
}