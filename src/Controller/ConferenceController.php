<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Conference;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
}
