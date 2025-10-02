<?php

require_once 'NoListenersException.php';
require_once 'EventListenerInterface.php';
require_once 'EventDispatcher.php';

$dispatcher = new EventDispatcher();
$dispatcher->dispatch(new stdClass(), 'event-1');
