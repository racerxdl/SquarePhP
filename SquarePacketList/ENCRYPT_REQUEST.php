<?php
 // https://wiki.vg/Protocol#Login
 include_once 'SquarePacket.php';
 class ENCRYPT_REQUEST extends SquarePacket {
     function serealize() {

        // Encryptation
        $ENCRYPT_REQUEST = new SquarePacket;
        $ENCRYPT_REQUEST->originSocket = $this->originSocket;
        $ENCRYPT_REQUEST->packetID = 0x01;

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