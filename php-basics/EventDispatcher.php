<?php

declare(strict_types=1);

final class EventDispatcher
{
    private array $listeners = [];

    public function addListener(string $eventName, callable|EventListenerInterface $listener): void
    {
        $this->listeners[$eventName] ??= [];

        $this->listeners[$eventName][] = $listener;
    }

    public function dispatch(object $event, string|null $name = null): void
    {
        $name ??= $event::class;

        $listeners = $this->listeners[$name] ?? [];

        if ([] === $listeners) {
            throw NoListenersException::forEvent($name);
        }

        foreach ($listeners as $listener) {
            if ($listener instanceof EventListenerInterface) {
                $listener->handle($event, $name);
                continue;
            }

            $listener($event, $name);
        }
    }
}
