<?php
include_once 'Entity.php';
class Player extends Entity
{
   public string $PlayerName;
   public $UUID;

   function GenerateUUID()
   {
      $this->UUID = rand(0, 999999);
   }

   function __construct($PlayerName)
   {
      $this->PlayerName = $PlayerName;
   }

   function GetPlayerName()
   {
      return $this->PlayerName;
   }

   function GetPlayerUUID()
   {
      return $this->UUID;
   }
}
