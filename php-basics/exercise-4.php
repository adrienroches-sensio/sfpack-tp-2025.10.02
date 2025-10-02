<?php

require_once 'EventDispatcher.php';

$dispatcher = new EventDispatcher();
$dispatcher->addListener('event-1', function (): void {
    echo 'Listener - 1 / Event - 1' . PHP_EOL;
}, 10);
$dispatcher->addListener('event-1', function (): void {
    echo 'Listener - 2 / Event - 1'  . PHP_EOL;
}, 0);
$dispatcher->addListener('event-1', function (): void {
    echo 'Listener - 3 / Event - 1'  . PHP_EOL;
}, 50);
$dispatcher->addListener('event-1', function (): void {
    echo 'Listener - 4 / Event - 1'  . PHP_EOL;
}, -50);

$dispatcher->dispatch(new stdClass(), 'event-1');
