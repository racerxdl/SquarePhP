<?php
include_once 'Entity.php';
class Player extends Entity
{
   public string $PlayerName;
   public $UUID;

   public float $X;
   public float $Y;
   public float $Z;
   public $HaveChunks;

   function GenerateUUID()
   {
      $this->UUID = rand(0, 999999);
   }

   function __construct($PlayerName)
   {
      $this->PlayerName = $PlayerName;
      $this->HaveChunks = array();
      $this->X = 0;
      $this->Y = 0;
      $this->Z = 0;
   }

   function SetHaveChunk($X, $Z)
   {
      $this->HaveChunks[$X][$Z] = 1;
   }

   function HaveChunk($X, $Z)
   {
      return @$this->HaveChunks[$X][$Z];
   }

   function UnsetChunk($X, $Z) {
      unset($this->HaveChunks[$X][$Z]);
   }

   function GetPlayerName()
   {
      return $this->PlayerName;
   }

   function GetPlayerUUID()
   {
      return $this->UUID;
   }

   function SetPosition($X, $Y, $Z)
   {
      $this->X = $X;
      $this->Y = $Y;
      $this->Z = $Z;
   }

   function GetX()
   {
      return $this->X;
   }

   function GetY()
   {
      return $this->Y;
   }

   function GetZ()
   {
      return $this->Z;
   }
}
