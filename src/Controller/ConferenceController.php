<?php

declare(strict_types=1);

namespace App\Controller;

use App\Conference\ConferenceSubmittedEvent;
use App\Entity\Conference;
use App\Form\ConferenceType;
use App\Search\ConferenceSearchInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Bridge\Twig\Attribute\Template;
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
        methods: ['GET', 'POST'],
    )]
    public function newConference(
        Request $request,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
    ): Response {
        $conference = new Conference();

        $form = $this->createForm(ConferenceType::class, $conference);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($conference);
            $entityManager->flush();

            $event = new ConferenceSubmittedEvent($conference);
            $eventDispatcher->dispatch($event);

            return $this->redirectToRoute('app_conference_list');
        }

        return $this->render('conference/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route(
        path: '/conferences',
        name: 'app_conference_list',
        methods: ['GET'],
    )]
    public function list(Request $request, ConferenceSearchInterface $conferenceSearch): Response
    {
        $conferences = $conferenceSearch->searchByName($request->query->getString('name'));

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

    #[Route(
        path: '/conference/search',
        name: 'app_conference_search',
        methods: ['GET'],
    )]
    #[Template('conference/search.html.twig')]
    public function search(Request $request, ConferenceSearchInterface $conferenceSearch): array
    {
        $name = $request->query->getString('name', '');

        $conferences = $conferenceSearch->searchByName($name);

        return [
            'conferences' => $conferences,
            'nameQuery' => $name,
        ];
    }
}
