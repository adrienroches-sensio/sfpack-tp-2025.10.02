<?php

require_once 'EventDispatcher.php';

$dispatcher = new EventDispatcher();
$dispatcher->addListener('event-1', function () {
    echo 'Listener - 1 / Event - 1' . PHP_EOL;
});
$dispatcher->addListener('event-1', function () {
    echo 'Listener - 2 / Event - 1'  . PHP_EOL;
});
$dispatcher->addListener('event-1', function () {
    echo 'Listener - 3 / Event - 1'  . PHP_EOL;
});
$dispatcher->addListener('event-2', function () {
    echo 'Listener - 4 / Event - 2'  . PHP_EOL;
});

$dispatcher->dispatch(new stdClass(), 'event-1');
