<?php
  class SquareMath {
    // Modulo negativo no php e zoado.
    static function fixModule($a, $b)
    {
        $r = $a % $b;
        if ($r < 0) {
            $r += abs($b);
        }
        return $r;
    }
  }
?>