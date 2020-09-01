<?php
// https://wiki.vg/Mojang_API
class MojangAPI
{
    // Player name to UUID
    public $PLAYER_NAME_TO_UUID = "https://api.mojang.com/users/profiles/minecraft/";

    // Skin Data
    public $PLAYER_SKIN_DATA = "https://sessionserver.mojang.com/session/minecraft/profile/";

    // Player Name -> UUID
    function GetPlayerUUID($val)
    {
        return json_decode(@file_get_contents($this->PLAYER_NAME_TO_UUID . urlencode($val)), true)['id'];
    }

    // Get Skin Data
    function GetSkinData($Nick) {
       return json_decode(@file_get_contents($this->PLAYER_SKIN_DATA . $this->GetPlayerUUID(urlencode($Nick)) . "?unsigned=false"), true);
    }  
}