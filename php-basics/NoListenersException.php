<?php

declare(strict_types=1);

final class NoListenersException extends Exception
{
    public static function forEvent(string $eventName): self
    {
        return new self("No listeners for event '{$eventName}'.");
    }
}
