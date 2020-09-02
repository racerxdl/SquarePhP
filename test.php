<?php

include_once __DIR__ . '/vendor/autoload.php';

use Ds\Queue;


$queue = new Ds\Queue();

echo("HUEBR");
while ($queue->count()) {
    $item =  $item = $queue->pop();
    var_dump($item);
}

echo("FIM");