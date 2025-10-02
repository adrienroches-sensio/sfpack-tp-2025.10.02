<?php

require_once 'EventListenerInterface.php';
require_once 'EventDispatcher.php';

$dispatcher = new EventDispatcher();
$dispatcher->addListener('event-1', function (): void {
    echo 'Listener - 1 / Event - 1' . PHP_EOL;
});
$dispatcher->addListener('event-1', new class implements EventListenerInterface {
    public function handle(object $event, string $name): void
    {
        echo 'Listener - 2 / Event - 1'  . PHP_EOL;
    }
});
$dispatcher->addListener('event-1', function (): void {
    echo 'Listener - 3 / Event - 1'  . PHP_EOL;
});

$dispatcher->dispatch(new stdClass(), 'event-1');
