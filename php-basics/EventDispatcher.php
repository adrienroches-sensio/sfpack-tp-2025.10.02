<?php

declare(strict_types=1);

final class EventDispatcher
{
    private array $listeners = [];

    public function addListener(string $eventName, callable|EventListenerInterface $listener, int $priority = 0): void
    {
        $this->listeners[$eventName] ??= [];

        $this->listeners[$eventName][] = [$listener, $priority];
    }

    public function dispatch(object $event, string|null $name = null): void
    {
        $name ??= $event::class;

        $listeners = $this->listeners[$name] ?? [];

        if ([] === $listeners) {
            throw NoListenersException::forEvent($name);
        }

        usort($listeners, function (array $listener1, array $listener2): int {
            [, $priority1] = $listener1;
            [, $priority2] = $listener2;

            return $priority2 <=> $priority1;
        });

        foreach ($listeners as [$listener]) {
            if ($listener instanceof EventListenerInterface) {
                $listener->handle($event, $name);
                continue;
            }

            $listener($event, $name);
        }
    }
}
