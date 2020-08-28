<?php
  class Entity {
      private int    $ID;
      private int    $MinecraftID;
      private double $X;
      private double $Y;
      private double $Z;

      function GetEntityX() {
          return $this->X;
      }

      function GetEntityY() {
          return $this->Y;
      }

      function GetEntityZ() {
          return $this->Z;
      }

      function EncodePosition() {
          return (($this->X & 0x3FFFFFF) << 38) | (($this->Z & 0x3FFFFFF) << 12) | ($this->Y & 0xFFF);
      }

      function DecodePosition($value) {
        $this->X = $value >> 38;
        $this->Y = $value & 0xFFF;
        $this->Z = ($value << 26 >> 38);
      }
  }
?>