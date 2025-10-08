<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Conference;
use App\Entity\Organization;
use App\Entity\User;
use App\Repository\ConferenceRepository;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

final class ConferenceController
{
    public function __construct(
        private readonly ConferenceRepository $conferenceRepository,
        private readonly SerializerInterface $serializer,
    ) {
    }

    #[Route(
        path: '/api/conferences-callback',
        name: 'api_conferences_list-fallback',
        methods: ['GET'],
    )]
    public function listWithCallback(): JsonResponse
    {
        $conferences = $this->conferenceRepository->list();

        $json = $this->serializer->serialize($conferences, 'json', [
            AbstractNormalizer::CALLBACKS => [
                'organizations' => function (object $attributeValue, object $object, string $attributeName, ?string $format = null, array $context = []) {
                    return $attributeValue->map(
                        fn (Organization $organization) => $organization->getName()
                    )->toArray();
                },
                'createdBy' => function (object $attributeValue, object $object, string $attributeName, ?string $format = null, array $context = []) {
                    return $attributeValue->getUserIdentifier();
                },
            ],
        ]);

        return new JsonResponse($json, json: true);
    }

    #[Route(
        path: '/api/conferences-circular',
        name: 'api_conferences_list-circular',
        methods: ['GET'],
    )]
    public function listWithCircular(): JsonResponse
    {
        $conferences = $this->conferenceRepository->list();

        $json = $this->serializer->serialize($conferences, 'json', [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function (object $object, ?string $format, array $context): int {
                return $object->getId();
            },
        ]);

        return new JsonResponse($json, json: true);
    }
}
