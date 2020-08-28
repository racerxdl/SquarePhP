<?php
   class World {
       private int $WorldTime;
       private int $TotalWorldTime;
       private int $TotalEntities;
       private string $WorldName;
       private $Entities;

       function __construct() {
           $this->WorldTime = 19000;
           $this->TotalWorldTime = 0;
           $this->TotalEntities = 0;
           $this->WorldName = "";
           $this->Entities = array();
       }

       function GetWorldTime() {
           return $this->WorldTime;
       }

       function SetWorldName($val) {
           $this->WorldName = $val;
       }

       function GetTotalWorldTime() {
          return $this->TotalWorldTime;
       }

      function AddEntity($entity) {
         $this->Entities[$this->TotalEntities++] = $entity;
      }

      function Tick() {
          $this->WorldTime += 20; // 20 = 1s
      }

      function SetWorldTime($value) {
          $this->WorldTime = $value;
      }

      function AddWorldTime($value) {
          $this->WorldTime += $value;
      }

      function RemoveWorldTime($value) {
        $this->WorldTime -= $value;
    }
   }
?>