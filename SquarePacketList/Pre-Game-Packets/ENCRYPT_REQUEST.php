<?php
// https://wiki.vg/Protocol#Login
include_once 'SquarePacket.php';
include_once 'SquarePacketConstants.php';
class ENCRYPT_REQUEST extends SquarePacket
{
    function serialize()
    {

        // Encryptation
        $ENCRYPT_REQUEST = new SquarePacket($this->handler);
        $ENCRYPT_REQUEST->packetID = SquarePacketConstants::$SERVER_ENCRYPT_REQUEST;

        // Secret KEY
        $array = array(rand(), rand(), rand(), rand());

        // Generate RSA 1024 keys
        {
            $ENCRYPT_REQUEST->WriteString("PHPServer");

            // RSA KEY
            {
                $config = array(
                    "digest_alg" => "sha1",
                    "private_key_bits" => 1024,
                    "private_key_type" => OPENSSL_KEYTYPE_RSA,
                );

                $pubkey = openssl_pkey_get_details(openssl_pkey_new($config));

                // Chave RSA Publica
                $pubkey = $pubkey["key"];

                // Get RSA Key
                $key = str_replace("-----BEGIN PUBLIC KEY-----", "", $pubkey);
                $key = str_replace("-----END PUBLIC KEY-----", "", $key);
                $key = trim($key);

                // Decode base64
                $base64Decoded = base64_decode($key);

                // Add RSA Key
                $ENCRYPT_REQUEST->WriteStringArray($base64Decoded);
                $ENCRYPT_REQUEST->WriteArray($array);
            }
        }

        // Send Packet
        $ENCRYPT_REQUEST->SendPacket();
    }
}

?>