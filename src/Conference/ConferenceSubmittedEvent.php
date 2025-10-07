<?php

declare(strict_types=1);

namespace App\Conference;

use App\Entity\Conference;
use Symfony\Contracts\EventDispatcher\Event;

final class ConferenceSubmittedEvent extends Event
{
    public function __construct(
        public readonly Conference $conference,
    ) {
    }
}
