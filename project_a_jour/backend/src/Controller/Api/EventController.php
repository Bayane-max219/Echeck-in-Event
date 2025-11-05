<?php

namespace App\Controller\Api;

use App\Entity\Event;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('/api/events', name: 'api_events_')]

class EventController extends AbstractController
{
    /**
     * Vérifie si l'utilisateur courant a le droit d'effectuer une action sur l'événement.
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
        ValidatorInterface $validator,
        EventRepository $eventRepository,
        TokenStorageInterface $tokenStorage
    ) {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->eventRepository = $eventRepository;
        $this->tokenStorage = $tokenStorage;
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $user = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;
        $events = $this->eventRepository->findBy(['organizer' => $user], ['createdAt' => 'DESC']);

        return new JsonResponse([
            'events' => json_decode($this->serializer->serialize($events, 'json', ['groups' => 'event:read']))
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Event $event): JsonResponse
    {
        if (!$this->isGrantedToEvent($event, 'view')) {
    return new JsonResponse(['error' => 'Access denied'], 403);
}

        return new JsonResponse([
            'event' => json_decode($this->serializer->serialize($event, 'json', ['groups' => 'event:read']))
        ]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['title'], $data['startDate'], $data['location'])) {
            return new JsonResponse(['error' => 'Missing required fields'], 400);
        }

        $event = new Event();
        $event->setTitle($data['title'])
            ->setDescription($data['description'] ?? null)
            ->setStartDate(new \DateTime($data['startDate']))
            ->setLocation($data['location'])
            ->setOrganizer($this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null);

        if (isset($data['endDate'])) {
            $event->setEndDate(new \DateTime($data['endDate']));
        }

        $errors = $this->validator->validate($event);
        if (count($errors) > 0) {
            return new JsonResponse(['errors' => (string) $errors], 400);
        }

        $this->entityManager->persist($event);
        $this->entityManager->flush();

        return new JsonResponse([
            'message' => 'Event created successfully',
            'event' => json_decode($this->serializer->serialize($event, 'json', ['groups' => 'event:read']))
        ], 201);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, Event $event): JsonResponse
    {
        if (!$this->isGrantedToEvent($event, 'edit')) {
    return new JsonResponse(['error' => 'Access denied'], 403);
}

        $data = json_decode($request->getContent(), true);

        if (isset($data['title'])) {
            $event->setTitle($data['title']);
        }
        if (isset($data['description'])) {
            $event->setDescription($data['description']);
        }
        if (isset($data['startDate'])) {
            $event->setStartDate(new \DateTime($data['startDate']));
        }
        if (isset($data['endDate'])) {
            $event->setEndDate(new \DateTime($data['endDate']));
        }
        if (isset($data['location'])) {
            $event->setLocation($data['location']);
        }
        if (isset($data['status'])) {
            $event->setStatus($data['status']);
        }

        $event->setUpdatedAt(new \DateTimeImmutable());

        $errors = $this->validator->validate($event);
        if (count($errors) > 0) {
            return new JsonResponse(['errors' => (string) $errors], 400);
        }

        $this->entityManager->flush();

        return new JsonResponse([
            'message' => 'Événement modifié avec succès',
            'event' => json_decode($this->serializer->serialize($event, 'json', ['groups' => 'event:read']))
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Event $event): JsonResponse
    {
        if (!$this->isGrantedToEvent($event, 'delete')) {
    return new JsonResponse(['error' => 'Access denied'], 403);
}

        $this->entityManager->remove($event);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Event deleted successfully']);
    }

    #[Route('/{id}/statistics', name: 'statistics', methods: ['GET'])]
    public function statistics(Event $event): JsonResponse
    {
        if (!$this->isGrantedToEvent($event, 'view')) {
    return new JsonResponse(['error' => 'Access denied'], 403);
}

        $stats = [
            'totalParticipants' => $event->getParticipantCount(),
            'checkedInParticipants' => $event->getCheckedInCount(),
            'attendanceRate' => $event->getAttendanceRate(),
            'invitationsSent' => $event->getInvitations()->count(),
            'confirmedInvitations' => $event->getInvitations()->filter(
                fn($invitation) => $invitation->getStatus() === 'confirmed'
            )->count()
        ];

        return new JsonResponse(['statistics' => $stats]);
    }

    #[Route('/{id}/checkins', name: 'checkins', methods: ['GET'])]
    public function checkins(Event $event, \App\Repository\CheckInRepository $checkInRepository, SerializerInterface $serializer): JsonResponse
    {
        if (!$this->isGrantedToEvent($event, 'view')) {
    return new JsonResponse(['error' => 'Access denied'], 403);
}
        $checkIns = $checkInRepository->findByEventWithParticipant($event->getId());
        $data = [];
        foreach ($checkIns as $checkIn) {
            $participant = $checkIn->getParticipant();
            $data[] = [
                'id' => $checkIn->getId(),
                'checkedInAt' => $checkIn->getCheckedInAt()?->format('c'),
                'checkedInBy' => $checkIn->getCheckedInBy(),
                'notes' => $checkIn->getNotes(),
                'participant' => [
                    'id' => $participant->getId(),
                    'firstName' => $participant->getFirstName(),
                    'lastName' => $participant->getLastName(),
                    'email' => $participant->getEmail(),
                    'phone' => $participant->getPhone(),
                    'company' => $participant->getCompany(),
                    'position' => $participant->getPosition(),
                    'qrCode' => $participant->getQrCode(),
                    'status' => $participant->getStatus(),
                    'createdAt' => $participant->getCreatedAt()?->format('c'),
                    'isCheckedIn' => $participant->isCheckedIn(),
                ],
            ];
        }
        return new JsonResponse(['checkIns' => $data]);
    }
}