<?php

declare(strict_types=1);

namespace App\Search;

use Symfony\Component\DependencyInjection\Attribute\AsDecorator;

#[AsDecorator(ConferenceSearchInterface::class)]
final class CacheableConferenceSearch implements ConferenceSearchInterface
{
    private array $cache;

    public function __construct(
        private readonly ConferenceSearchInterface $inner,
    ) {
    }

    public function searchByName(string|null $name = null): array
    {
        $name = trim($name ?? '');

        if ('' === $name) {
            return $this->inner->searchByName($name);
        }

        return $this->cache[$name] ??= $this->inner->searchByName($name);
    }
}
