<?php
include_once 'Entity.php';
class Player
{
   public int $PlayerID;
   public string $PlayerName;
   public $ClientHandler;
   public $UUID;

   public float $X;
   public float $Y;
   public float $Z;
   public float $Yaw;
   public float $Pitch;
   public float $onGround;
   public $HaveChunks;

   function __construct($ClientHandler, $ServerHandler, $PlayerName)
   {
      $this->PlayerName = $PlayerName;
      $this->HaveChunks = array();
      $this->X = 0;
      $this->Y = 0;
      $this->Z = 0;
      $this->ClientHandler = $ClientHandler;
      $this->PlayerID = $ServerHandler->GetWorld(0)->AddPlayer($this);
      $this->UUID = rand(0, 999999);
      $this->Yaw = 0;
      $this->Pitch = 0;
      $this->onGround = false;
   }

   function SetYaw($yaw)
   {
      $this->Yaw = $yaw;
   }

   function SetPitch($pitch)
   {
      $this->Pitch = $pitch;
   }

   function SetOnGround($onGround)
   {
      $this->onGround = $onGround;
   }

   function GetYaw() {
      return $this->Yaw;
   }

   function GetPitch() {
      return $this->Pitch;
   }

   function GetGround() {
      return $this->onGround;
   }

   function GetUUID()
   {
      return $this->UUID;
   }

   function GetEntityID()
   {
      return $this->PlayerID;
   }

   function SetHaveChunk($X, $Z)
   {
      $this->HaveChunks[$X][$Z] = 1;
   }

   function HaveChunk($X, $Z)
   {
      return @$this->HaveChunks[$X][$Z];
   }

   function UnsetChunk($X, $Z)
   {
      unset($this->HaveChunks[$X][$Z]);
   }

   function GetPlayerName()
   {
      return $this->PlayerName;
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
