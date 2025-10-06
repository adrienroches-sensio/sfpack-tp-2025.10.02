<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Conference;
use App\Repository\ConferenceRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

class ConferenceController extends AbstractController
{
    #[Route(
        path: '/conference/{name}/{start}/{end}',
        name: 'app_conference_new',
        requirements: [
            'name' => '[a-zA-Z0-9]+',
            'start' => Requirement::DATE_YMD,
            'end' => Requirement::DATE_YMD,
        ],
        methods: ['GET'],
    )]
    public function newConference(
        string $name,
        string $start,
        string $end,
        EntityManagerInterface $entityManager,
    ): Response {
        $conference = (new Conference())
            ->setName($name)
            ->setDescription('Some generic description')
            ->setAccessible(true)
            ->setStartAt(new DateTimeImmutable($start))
            ->setEndAt(new DateTimeImmutable($end))
        ;

        $entityManager->persist($conference);
        $entityManager->flush();

        return new Response("Conference nº{$conference->getId()} created");
    }

    #[Route(
        path: '/conferences',
        name: 'app_conference_list',
        methods: ['GET'],
    )]
    public function list(Request $request, ConferenceRepository $conferenceRepository): Response
    {
        $start = null;
        $end = null;

        if ($request->query->has('start')) {
            $start = new DateTimeImmutable($request->query->get('start'));
        }

        if ($request->query->has('end')) {
            $end = new DateTimeImmutable($request->query->get('end'));
        }

        if ($start !== null || $end !== null) {
            $conferences = $conferenceRepository->searchBetweenDates($start, $end);
        } else {
            $conferences = $conferenceRepository->list();
        }

        $content = array_map(function (Conference $conference): array {
            return [
                'id' => $conference->getId(),
                'name' => $conference->getName(),
            ];
        }, $conferences);

        return $this->json($content);
    }

    #[Route(
        path: '/conference/{id}',
        name: 'app_conference_show',
        requirements: [
            'id' => Requirement::DIGITS,
        ],
        methods: ['GET'],
    )]
    public function show(Conference $conference): Response
    {
        return $this->json([
            'id' => $conference->getId(),
            'name' => $conference->getName(),
        ]);
    }
}
