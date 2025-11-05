<?php

namespace App\Controller\Api;

use App\Entity\CheckIn;
use App\Entity\Participant;
use App\Repository\CheckInRepository;
use App\Repository\EventRepository;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('/api/checkin', name: 'api_checkin_')]

class CheckInController extends AbstractController
{
    /**
     * izy ity dia securité miaro ny API mba ho ilay user ihany no afaka amikitikitika ilay evenement sy ny checkins
     */
    private function isGrantedToEvent($event, $action): bool
    {
        $user = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;
        if (!$user) return false;
        // Pour 'edit' et 'delete', seul l'organisateur peut modifier/supprimer
        if (in_array($action, ['edit', 'delete'])) {
            return $event->getOrganizer() && $event->getOrganizer()->getId() === $user->getId();
        }
        // Pour 'view', l'organisateur ou toute autre logique d'accès
        if ($action === 'view') {
            return $event->getOrganizer() && $event->getOrganizer()->getId() === $user->getId();
        }
        return false;
    }

    private $tokenStorage;

    public function __construct(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ParticipantRepository $participantRepository,
        TokenStorageInterface $tokenStorage
    ) {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->participantRepository = $participantRepository;
        $this->tokenStorage = $tokenStorage;
    }
//ity route ity code ity di amanamarika raha ohatr aka niova tampoka ilay evenement nefa mbola hiscan ialy agent dia mampiseho hoe invalide satria ialy evenement efa draft na cancelled
    #[Route('/{qrCode}', name: 'scan', methods: ['POST'])]
    public function scan(Request $request, string $qrCode): JsonResponse
    {
        // Vérifier si le QR code est invalide (commence par "INVALID_")
        if (str_starts_with($qrCode, 'INVALID_')) {
            return new JsonResponse(['error' => 'Code QR invalide - Reconfirmation requise'], 403);
        }
        
        $participant = $this->participantRepository->findOneBy(['qrCode' => $qrCode]);
        
        if (!$participant) {
            return new JsonResponse(['error' => 'Code QR invalide'], 404);
        }

        $event = $participant->getEvent();
        if (!$event) {
            return new JsonResponse(['error' => 'Aucun événement associé à ce participant'], 403);
        }
        if ($event->getStatus() !== 'active') {
            $status = $event->getStatus();
            $reason = '';
            if ($status === 'draft') {
                $reason = 'L\'événement est en mode brouillon.';
            } elseif ($status === 'cancelled') {
                $reason = 'L\'événement a été annulé.';
            } elseif ($status === 'completed') {
                $reason = 'L\'événement est déjà terminé.';
            } else {
                $reason = 'L\'événement n\'est pas actif.';
            }
            return new JsonResponse([
                'error' => 'Scan non autorisé : statut de l\'événement "' . $status . '"',
                'status' => $status,
                'reason' => $reason
            ], 403); //403 dia tsy azo atao 
        }

        //*********ity indray maneho  raha efa déja checké ilay participant
        if ($participant->isCheckedIn()) {
            return new JsonResponse([
                'error' => 'Participant déjà enregistré',
                'participant' => json_decode($this->serializer->serialize($participant, 'json', ['groups' => 'participant:read']))
            ], 409);//resaka conflit ny 409 tsy mahzo miverina 
        }
        //******raha mbola tsy chécké ilay parcticipant dia mitohy ilay code 
        //mandray ny valiny avy any amin'ny application mobile mandray ilay data
        $data = json_decode($request->getContent(), true);
        
        $checkIn = new CheckIn();
        $checkIn->setEvent($participant->getEvent())
            ->setParticipant($participant)
            ->setCheckedInBy($data['checkedInBy'] ?? 'Mobile App')
            ->setNotes($data['notes'] ?? null);

        // Manova status ilay participant ho lasa checké
        $participant->setStatus('checked_in');

        $this->entityManager->persist($checkIn);
        $this->entityManager->flush();
        //Mamerina ny valiny any aminy mobile hoe efa chécké tsara amizay izy 
        return new JsonResponse([
            'message' => 'Enregistrement réussi',
            'participant' => json_decode($this->serializer->serialize($participant, 'json', ['groups' => 'participant:read'])),
            'checkIn' => json_decode($this->serializer->serialize($checkIn, 'json', ['groups' => 'checkin:read']))
        ]); //tsy aseho ny 200 succès satria efa automatique
    }

    #[Route('/verify/{qrCode}', name: 'verify', methods: ['GET'])]
    public function verify(string $qrCode): JsonResponse
    {
        // Vérifier si le QR code est invalide (commence par "INVALID_")
        if (str_starts_with($qrCode, 'INVALID_')) {
            return new JsonResponse(['error' => 'Code QR invalide - Reconfirmation requise'], 403);
        }
        
        $participant = $this->participantRepository->findOneBy(['qrCode' => $qrCode]);
        
        if (!$participant) {
            return new JsonResponse(['error' => 'Code QR invalide'], 404);//404 resaka nt found tsy hita zany
        }

        $event = $participant->getEvent();
        if (!$event) {
            return new JsonResponse(['error' => 'Aucun événement associé à ce participant', 'valid' => false, 'event_status' => null], 403);
        }
        if ($event->getStatus() !== 'active') {
            $status = $event->getStatus();
            $reason = '';
            if ($status === 'draft') {
                $reason = "L'événement est en mode brouillon (draft).";
            } elseif ($status === 'cancelled') {
                $reason = "L'événement a été annulé (cancelled).";
            } elseif ($status === 'completed') {
                $reason = "L'événement est terminé (completed).";
            } else {
                $reason = "L'événement n'est pas actif (status: $status).";
            }
            return new JsonResponse([
                'error' => $reason,
                'valid' => false,
                'event_status' => $status,
            ], 403);
        }

        return new JsonResponse([
            'valid' => true,
            'participant' => json_decode($this->serializer->serialize($participant, 'json', ['groups' => 'participant:read'])),
            'event' => json_decode($this->serializer->serialize($participant->getEvent(), 'json', ['groups' => 'event:read'])),
            'alreadyCheckedIn' => $participant->isCheckedIn()
        ]);
    }


    #[Route('/events/{eventId}/checkins', name: 'event_checkins', methods: ['GET'])]
    public function eventCheckIns(
        int $eventId,
        \App\Repository\CheckInRepository $checkInRepository,
        \App\Repository\EventRepository $eventRepository
    ): JsonResponse {
        $event = $eventRepository->find($eventId);
        if (!$event) {
            return new JsonResponse(['error' => 'Event not found'], 404);
        }
        if (!$this->isGrantedToEvent($event, 'view')) {
    return new JsonResponse(['error' => 'Access denied'], 403);
}
        $checkIns = $checkInRepository->findByEventWithParticipant($eventId);
        return new JsonResponse([
            'checkIns' => json_decode($this->serializer->serialize($checkIns, 'json', ['groups' => ['checkin:read', 'participant:read']]))
        ]);
    }

    #[Route('/events/{eventId}/stats', name: 'event_stats', methods: ['GET'])]
    public function eventStats(
        int $eventId,
        \App\Repository\CheckInRepository $checkInRepository,
        \App\Repository\EventRepository $eventRepository
    ): JsonResponse {
        $event = $eventRepository->find($eventId);
        if (!$event) {
            return new JsonResponse(['error' => 'Event not found'], 404);
        }
        if (!$this->isGrantedToEvent($event, 'view')) {
    return new JsonResponse(['error' => 'Access denied'], 403);
}
        $stats = $checkInRepository->getCheckInStatsByEvent($eventId);
        return new JsonResponse(['stats' => $stats]);
    }

    #[Route('/recent', name: 'recent', methods: ['GET'])]
    public function recent(
        \App\Repository\CheckInRepository $checkInRepository
    ): JsonResponse {
        // Récupère les 20 derniers check-ins (tous events)
        $checkIns = $checkInRepository->findBy([], ['createdAt' => 'DESC'], 20);
        return new JsonResponse([
            'checkIns' => json_decode($this->serializer->serialize($checkIns, 'json', ['groups' => ['checkin:read', 'participant:read', 'event:read']]))
        ]);
    }

    #[Route('/recent-per-event', name: 'recent_per_event', methods: ['GET'])]
    public function recentPerEvent(
        \App\Repository\CheckInRepository $checkInRepository
    ): JsonResponse {
        $checkIns = $checkInRepository->findLatestCheckInPerEvent();
        return new JsonResponse([
            'checkIns' => json_decode($this->serializer->serialize($checkIns, 'json', ['groups' => ['checkin:read', 'participant:read', 'event:read']]))
        ]);
    }
}