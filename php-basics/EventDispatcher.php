<?php

declare(strict_types=1);

final class EventDispatcher
{
    private array $listeners = [];

    public function addListener(string $eventName, callable $listener)
    {
        $this->listeners[$eventName] ??= [];

        $this->listeners[$eventName][] = $listener;
    }

    public function dispatch(object $event, string|null $name = null)
    {
        $name ??= $event::class;

        $listeners = $this->listeners[$name] ?? [];

        foreach ($listeners as $listener) {
            $listener($event, $name);
        }
    }
}
