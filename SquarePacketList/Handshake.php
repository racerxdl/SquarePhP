<?php
 include_once 'SquarePacket.php';
 include_once 'SquareConstants.php';
 include_once 'Handshake_SERVER_MOTD.php';
 include_once 'ENCRYPT_REQUEST.php';
 class Handshake extends SquarePacket {
     function deserialize() { 
        // ACK
        $handACK = new Handshake_SERVER_MOTD;

        // As vezes o cliente envia um pacote de tamanho 1. Provavelmente seja o disconnect.
        // Ou talvez, seja cache?
        if ($this->packetSize == 1) {
             $handACK->originSocket = $this->originSocket;
             $handACK->serealize();
             return;
        }

        // Numero de Protocolo.
        $protocolVersion = $this->DecodeVarInt();

        // Verifica se o protocolo e válido.
        if ($protocolVersion != $GLOBALS["PROTOCOL_VERSION_DATA"]) {
            return;
        }

        // Server Address
        $serverIP = $this->ReadString();
        $serverPort = $this->ReadShort();
        $nextState = $this->DecodeVarInt();

        // Next-state
        // https://wiki.vg/Server_List_Ping
        switch ($nextState) {
            case 1:
                $handACK->originSocket = $this->originSocket;
                $handACK->serealize();
                break;
            case 2:
                // For unauthenticated ("cracked"/offline-mode) and localhost connections (either of the two conditions is enough for an unencrypted connection) there is no encryption. In that case Login Start is directly followed by Login Success.
                $loginSucess = new SquarePacket;
                $loginSucess->originSocket = $this->originSocket;
                $loginSucess->packetID = 0x2;
                $loginSucess->WriteUUID(999); // UUID, não importa agora.
                $loginSucess->WriteString("An0nyMoUS");
                $loginSucess->SendPacket();

                // Join game
                {
                    $JoinGame = new SquarePacket;
                    $JoinGame->originSocket = $this->originSocket;

                    // 1.16 - 0x24 - Pre Release - Join Game
                    $JoinGame->packetID = 0x24;

                    // SJoinGamePacket.java - Decompiled
                    $JoinGame->WriteInt(1500);
                    $JoinGame->WriteByte(false);
                    $JoinGame->WriteByte(0); // Current Game mode.
                    $JoinGame->WriteByte(0); // Old game mode.

                    // Map Names
                    // https://wiki.vg/Pre-release_protocol#Join_Game
                    $mapNames = array("minecraft:overworld", "minecraft:the_nether", "minecraft:the_end");
                    
                    /*
                        World Map Names?
                        int i = buf.readVarInt();
                        this.field_240811_e_ = Sets.newHashSet();
                        for(int j = 0; j < i; ++j) {
                            this.field_240811_e_.add(RegistryKey.func_240903_a_(Registry.field_239699_ae_, buf.readResourceLocation()));
                        }
                    */
                    $JoinGame->WriteVarInt(count($mapNames));
                    for ($i = 0; $i < count($mapNames); $i++) {
                        $JoinGame->WriteString($mapNames[$i]);
                    }

                    /*
                      public PacketBuffer writeCompoundTag(@Nullable CompoundNBT nbt) {
                        if (nbt == null) {
                            this.writeByte(0);
                        } else {
                            try {
                                CompressedStreamTools.write(nbt, new ByteBufOutputStream(this));
                            } catch (IOException ioexception) {
                                throw new EncoderException(ioexception);
                            }
                        }

                        return this;
                    }
                    */

                    // Dimension Codec - 0
                    // https://paste.gg/p/KennyTV/ad46ef552c6443cc808385f060564550
                    {
                        $JoinGame->WriteByte(10); // ID DA TAG.
                        $JoinGame->WriteStringNBT(""); // Root Element
                        {
                            $JoinGame->WriteByte(10); // ID TAG
                            $JoinGame->WriteStringNBT("minecraft:worldgen/biome");
                            {

                               // Tipo de Bioma     
                               $JoinGame->WriteByte(8);
                               $JoinGame->WriteStringNBT("type");
                               $JoinGame->WriteStringNBT("minecraft:worldgen/biome"); // Biome world Type
                               
                               // TAG_List
                               $JoinGame->WriteByte(9); // List Type
                               $JoinGame->WriteStringNBT("value");

                               // writeTagPayload
                               $JoinGame->WriteByte(10);
                               $JoinGame->WriteInt(1);

                               // Float
                               {
                                   for ($i = 0; $i < 1; $i++) {
                                       {
                                            // Nome do Bioma
                                            $JoinGame->WriteByte(8);
                                            $JoinGame->WriteStringNBT("name");
                                            $JoinGame->WriteStringNBT("minecraft:plains"); // Value....   

                                            // ID
                                            $JoinGame->WriteByte(3);
                                            $JoinGame->WriteStringNBT("id");
                                            $JoinGame->WriteInt(1); // Value....

                                            // Element array[]
                                            $JoinGame->WriteByte(10); // ID DA TAG.
                                            $JoinGame->WriteStringNBT("element");       
                                            {
                                                // precipitation: rain
                                                $JoinGame->WriteByte(8);
                                                $JoinGame->WriteStringNBT("precipitation");
                                                $JoinGame->WriteStringNBT("rain");

                                                // Element array[]
                                                $JoinGame->WriteByte(10); // ID DA TAG.
                                                $JoinGame->WriteStringNBT("effects");       
                                                {
                                                    $JoinGame->WriteByte(3);
                                                    $JoinGame->WriteStringNBT("sky_color");
                                                    $JoinGame->WriteInt(7907327); // Value....

                                                    $JoinGame->WriteByte(3);
                                                    $JoinGame->WriteStringNBT("water_fog_color");
                                                    $JoinGame->WriteInt(329011); // Value....

                                                    $JoinGame->WriteByte(3);
                                                    $JoinGame->WriteStringNBT("fog_color");
                                                    $JoinGame->WriteInt(12638463); // Value....

                                                    $JoinGame->WriteByte(3);
                                                    $JoinGame->WriteStringNBT("water_color");
                                                    $JoinGame->WriteInt(4159204); // Value....
                                                }  
                                                $JoinGame->WriteByte(0);  
                                            }  

                                            // depth: 0.125F
                                            $JoinGame->WriteByte(5);
                                            $JoinGame->WriteStringNBT("depth");
                                            $JoinGame->WriteFloat(0.125); // Value....

                                            //  scale: 0.05F
                                            $JoinGame->WriteByte(5);
                                            $JoinGame->WriteStringNBT("scale");
                                            $JoinGame->WriteFloat(0.05); // Value....

                                            // category
                                            $JoinGame->WriteByte(8);
                                            $JoinGame->WriteStringNBT("category");
                                            $JoinGame->WriteStringNBT("plains");

                                            // downfall 0.4F
                                            $JoinGame->WriteByte(5);
                                            $JoinGame->WriteStringNBT("downfall");
                                            $JoinGame->WriteFloat(0.4); // Value....

                                            // temperature 0.4F
                                            $JoinGame->WriteByte(5);
                                            $JoinGame->WriteStringNBT("temperature");
                                            $JoinGame->WriteFloat(0.4); // Value....

                                            $JoinGame->WriteByte(0);      
                                        }
                                       $JoinGame->WriteByte(0);      
                                   }
                               }
                            }
                            $JoinGame->WriteByte(0);  // TAG_END 
                        }
                        $JoinGame->WriteByte(0);  // TAG_END 
                    }

                    // Dimension Int Enum NBT Tag Compound	- 0
                    {
                        $JoinGame->WriteByte(10); // ID DA TAG.
                        $JoinGame->WriteStringNBT("MapLike");                   
                        {
                            // NBT Values
                            {
                                {
                                    // Ambient Light
                                    $JoinGame->WriteByte(5);
                                    $JoinGame->WriteStringNBT("ambient_light");
                                    $JoinGame->WriteFloat(5); // Value....

                                    // Infiniburn
                                    $JoinGame->WriteByte(8);
                                    $JoinGame->WriteStringNBT("infiniburn");
                                    $JoinGame->WriteStringNBT(""); // Value....

                                    // Logical height
                                    $JoinGame->WriteByte(5);
                                    $JoinGame->WriteStringNBT("logical_height");
                                    $JoinGame->WriteFloat(0); // Value....     
                                    
                                    // Raids
                                    $JoinGame->WriteByte(1);
                                    $JoinGame->WriteStringNBT("has_raids");
                                    $JoinGame->WriteByte(0); // Value....   

                                    // respawn_anchor_works
                                    $JoinGame->WriteByte(1);
                                    $JoinGame->WriteStringNBT("respawn_anchor_works");
                                    $JoinGame->WriteByte(0); // Value....   

                                    // bed_works
                                    $JoinGame->WriteByte(1);
                                    $JoinGame->WriteStringNBT("bed_works");
                                    $JoinGame->WriteByte(0); // Value....   

                                    // piglin_safe
                                    $JoinGame->WriteByte(1);
                                    $JoinGame->WriteStringNBT("piglin_safe");
                                    $JoinGame->WriteByte(0); // Value....   

                                    // coordinate_scale
                                    $JoinGame->WriteByte(5);
                                    $JoinGame->WriteStringNBT("coordinate_scale");
                                    $JoinGame->WriteFloat(1.0); // Value....   

                                    // natural
                                    $JoinGame->WriteByte(1);
                                    $JoinGame->WriteStringNBT("natural");
                                    $JoinGame->WriteByte(0); // Value....   

                                    // ultrawarm
                                    $JoinGame->WriteByte(1);
                                    $JoinGame->WriteStringNBT("ultrawarm");
                                    $JoinGame->WriteByte(0); // Value....   

                                    // has_ceiling
                                    $JoinGame->WriteByte(1);
                                    $JoinGame->WriteStringNBT("has_ceiling");
                                    $JoinGame->WriteByte(0); // Value....   

                                    // has_skylight
                                    $JoinGame->WriteByte(1);
                                    $JoinGame->WriteStringNBT("has_skylight");
                                    $JoinGame->WriteByte(1); // Value....    
                                    
                                    // name
                                    $JoinGame->WriteByte(8);
                                    $JoinGame->WriteStringNBT("name");
                                    $JoinGame->WriteStringNBT("minecraft:overworld"); // Value....   
                                }
                            }
                        }
                        // Finalizar
                        $JoinGame->WriteByte(0);  // TAG_END 
                    }

                    $JoinGame->WriteString("default"); // Default map name. (minecraft:overworld);
                    $JoinGame->WriteLong(0); // Seed.
                    $JoinGame->WriteVarInt(20); // Max Players
                    $JoinGame->WriteVarInt(5); // View Distance
                    $JoinGame->WriteByte(false); //  this.reducedDebugInfo = buf.readBoolean();
                    $JoinGame->WriteByte(false); //  this.enableRespawnScreen = buf.readBoolean();
                    $JoinGame->WriteByte(false); //   this.field_240814_m_ = buf.readBoolean();
                    $JoinGame->WriteByte(false); //  this.field_240815_n_ = buf.readBoolean();
                    $JoinGame->SendPacket();

                    // Spawn Position.
                    {
                            // Posicao do jogador
                            {
                                $Position = new SquarePacket;
                                $Position->originSocket = $this->originSocket;
                                $Position->packetID = 0x34;
                                $Position->WriteDouble(0);
                                $Position->WriteDouble(64);
                                $Position->WriteDouble(0);
                                $Position->WriteFloat(0);
                                $Position->WriteFloat(0);
                                $Position->WriteByte(0x01);
                                $Position->WriteVarInt(0);
                                $Position->SendPacket();
                            }
                    }
                }
                break; 
        }
     }
 }
?>