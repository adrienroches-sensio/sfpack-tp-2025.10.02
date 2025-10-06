<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Conference;
use App\Form\ConferenceType;
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
        path: '/conference/new',
        name: 'app_conference_new',
        methods: ['GET'],
    )]
    public function newConference(
    ): Response {
        $conference = new Conference();

        $form = $this->createForm(ConferenceType::class, $conference);

        return $this->render('conference/new.html.twig', [
            'form' => $form,
        ]);
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

        return $this->render('conference/list.html.twig', [
            'conferences' => $conferences,
        ]);
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
        return $this->render('conference/show.html.twig', [
            'conference' => $conference,
        ]);
    }
}
