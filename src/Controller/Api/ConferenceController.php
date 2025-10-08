<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Repository\ConferenceRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class ConferenceController
{
    public function __construct(
        private readonly ConferenceRepository $conferenceRepository,
        private readonly SerializerInterface $serializer,
    ) {
    }

    #[Route(
        path: '/api/conferences',
        name: 'api_conferences_list',
        methods: ['GET'],
    )]
    public function list(): JsonResponse
    {
        $conferences = $this->conferenceRepository->list();

        $json = $this->serializer->serialize($conferences, 'json');

        return new JsonResponse($json, json: true);
    }
}
