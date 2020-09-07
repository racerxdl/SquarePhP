<?php

include_once 'SquarePacket.php';
include_once 'SquareConstants.php';
include_once 'SquarePacketConstants.php';

class JoinGame extends SquarePacket
{
    function serialize()
    {
        {
            $JoinGame = new SquarePacket($this->handler);

            // 1.16 - 0x24 - Pre Release - Join Game
            $JoinGame->packetID = SquarePacketConstants::$SERVER_JOIN_GAME;

            // SJoinGamePacket.java - Decompiled
            $JoinGame->WriteInt($this->handler->Player->GetEntityID());
            $JoinGame->WriteByte(false);
            $JoinGame->WriteByte(1);
            $JoinGame->WriteByte(0xFF);

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
                            $JoinGame->WriteStringNBT($mapNames[0]); // Value....
                        }
                    }
                }
                // Finalizar
                $JoinGame->WriteByte(0);  // TAG_END
            }

            $JoinGame->WriteString($mapNames[0]); // Default map name. (minecraft:overworld);
            $JoinGame->WriteLong($this->ServerHandler->GetWorld(0)->GetWorldSeed()); // Seed.
            $JoinGame->WriteVarInt(20); // Max Players
            $JoinGame->WriteVarInt($this->ServerHandler->GetWorld(0)->GetRenderDistance()); // View Distance
            $JoinGame->WriteByte(false); //  this.reducedDebugInfo = buf.readBoolean();
            $JoinGame->WriteByte(false); //  this.enableRespawnScreen = buf.readBoolean();
            $JoinGame->WriteByte(false); //   this.field_240814_m_ = buf.readBoolean();
            $JoinGame->WriteByte(true); //  this.field_240815_n_ = buf.readBoolean();
            $JoinGame->SendPacket();
        }
    }
}