<?php

declare(strict_types=1);

namespace App\Conference;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use function str_contains;
use function strtolower;

final class ConferenceSusbcriber
{
    #[AsEventListener]
    public function rejectConferenceIfRelatedToSymfony(ConferenceSubmittedEvent $event): void
    {
        if (str_contains(strtolower($event->conference->getName()), 'symfony')) {
            $event->reject('The conference name contains "symfony".');
        }
    }
}
