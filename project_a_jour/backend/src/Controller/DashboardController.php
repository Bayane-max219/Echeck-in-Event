<?php

namespace App\Controller;

use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    public function index(EventRepository $eventRepository): Response
    {
        $user = $this->getUser();//Maka ny utilisateur connecté (organisateur)
        $events = $eventRepository->findBy(['organizer' => $user], ['createdAt' => 'DESC'], 6);//Maka ny events 6 farany an’ilay user, araka ny daty namoronana azy (ordre descendant).
        
        $stats = [
            'totalEvents' => $eventRepository->count(['organizer' => $user]),
            'activeEvents' => $eventRepository->count(['organizer' => $user, 'status' => 'active']),
            'totalParticipants' => 0,// isa totalin’ny participants amin’ny events rehetra (initialisé à 0).
            'totalCheckIns' => 0
        ];

        foreach ($eventRepository->findBy(['organizer' => $user]) as $event) {
            $stats['totalParticipants'] += $event->getParticipantCount();//Manampy ny isa totalin’ny participants amin’ny event tsirairay.
            $stats['totalCheckIns'] += $event->getCheckedInCount();
        }

        return $this->render('dashboard/index.html.twig', [
            'events' => $events,
            'stats' => $stats
        ]);
    }
}