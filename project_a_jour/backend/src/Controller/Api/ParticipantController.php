<?php

namespace App\Controller\Api;

use App\Entity\Event;
use App\Entity\Participant;
use App\Repository\ParticipantRepository;
use App\Service\CsvImportService;
use App\Service\InvitationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('/api/events/{eventId}/participants', name: 'api_participants_')]

class ParticipantController
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
        ParticipantRepository $participantRepository,
        CsvImportService $csvImportService,
        InvitationService $invitationService,
        TokenStorageInterface $tokenStorage
    ) {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->participantRepository = $participantRepository;
        $this->csvImportService = $csvImportService;
        $this->invitationService = $invitationService;
        $this->tokenStorage = $tokenStorage;
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(int $eventId): JsonResponse
    {
        $event = $this->entityManager->getRepository(Event::class)->find($eventId);
        if (!$event) {
            return new JsonResponse(['error' => 'Event not found'], 404);
        }

        if (!$this->isGrantedToEvent($event, 'view')) {
    return new JsonResponse(['error' => 'Access denied'], 403);
}

        $participants = $this->participantRepository->findBy(['event' => $event]);

        return new JsonResponse([
            'participants' => json_decode($this->serializer->serialize($participants, 'json', ['groups' => 'participant:read']))
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $eventId, Participant $participant): JsonResponse
    {
        if ($participant->getEvent()->getId() !== $eventId) {
            return new JsonResponse(['error' => 'Participant not found in this event'], 404);
        }

        $this->denyAccessUnlessGranted('view', $participant->getEvent());

        return new JsonResponse([
            'participant' => json_decode($this->serializer->serialize($participant, 'json', ['groups' => 'participant:read']))
        ]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, int $eventId): JsonResponse
    {
        $event = $this->entityManager->getRepository(Event::class)->find($eventId);
        if (!$event) {
            return new JsonResponse(['error' => 'Event not found'], 404);
        }

        if (!$this->isGrantedToEvent($event, 'edit')) {
    return new JsonResponse(['error' => 'Access denied'], 403);
}

        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['firstName'], $data['lastName'], $data['email'])) {
            return new JsonResponse(['error' => 'Missing required fields'], 400);
        }

        // Check if participant already exists for this event
        $existingParticipant = $this->participantRepository->findOneBy([
            'event' => $event,
            'email' => $data['email']
        ]);

        if ($existingParticipant) {
            return new JsonResponse(['error' => 'Participant already exists for this event'], 409);
        }

        $participant = new Participant();
        $participant->setFirstName($data['firstName'])
            ->setLastName($data['lastName'])
            ->setEmail($data['email'])
            ->setPhone($data['phone'] ?? null)
            ->setCompany($data['company'] ?? null)
            ->setPosition($data['position'] ?? null)
            ->setEvent($event);

        $errors = $this->validator->validate($participant);
        if (count($errors) > 0) {
            return new JsonResponse(['errors' => (string) $errors], 400);
        }

        $this->entityManager->persist($participant);
        $this->entityManager->flush();

        // Send invitation if requested
        if (isset($data['sendInvitation']) && $data['sendInvitation']) {
            $this->invitationService->sendInvitation($participant);
        }

        return new JsonResponse([
            'message' => 'Participant created successfully',
            'participant' => json_decode($this->serializer->serialize($participant, 'json', ['groups' => 'participant:read']))
        ], 201);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, int $eventId, Participant $participant): JsonResponse
    {
        if ($participant->getEvent()->getId() !== $eventId) {
            return new JsonResponse(['error' => 'Participant not found in this event'], 404);
        }

        $this->denyAccessUnlessGranted('edit', $participant->getEvent());

        $data = json_decode($request->getContent(), true);

        if (isset($data['firstName'])) {
            $participant->setFirstName($data['firstName']);
        }
        if (isset($data['lastName'])) {
            $participant->setLastName($data['lastName']);
        }
        if (isset($data['email'])) {
            $participant->setEmail($data['email']);
        }
        if (isset($data['phone'])) {
            $participant->setPhone($data['phone']);
        }
        if (isset($data['company'])) {
            $participant->setCompany($data['company']);
        }
        if (isset($data['position'])) {
            $participant->setPosition($data['position']);
        }

        $errors = $this->validator->validate($participant);
        if (count($errors) > 0) {
            return new JsonResponse(['errors' => (string) $errors], 400);
        }

        $this->entityManager->flush();

        return new JsonResponse([
            'message' => 'Participant updated successfully',
            'participant' => json_decode($this->serializer->serialize($participant, 'json', ['groups' => 'participant:read']))
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $eventId, Participant $participant): JsonResponse
    {
        if ($participant->getEvent()->getId() !== $eventId) {
            return new JsonResponse(['error' => 'Participant not found in this event'], 404);
        }

        $this->denyAccessUnlessGranted('edit', $participant->getEvent());

        $this->entityManager->remove($participant);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Participant deleted successfully']);
    }

    #[Route('/import', name: 'import_csv', methods: ['POST'])]
    public function importCsv(Request $request, int $eventId): JsonResponse
    {
        $event = $this->entityManager->getRepository(Event::class)->find($eventId);
        if (!$event) {
            return new JsonResponse(['error' => 'Event not found'], 404);
        }

        if (!$this->isGrantedToEvent($event, 'edit')) {
    return new JsonResponse(['error' => 'Access denied'], 403);
}

        $uploadedFile = $request->files->get('csv_file');
        if (!$uploadedFile) {
            return new JsonResponse(['error' => 'No CSV file uploaded'], 400);
        }

        try {
            $result = $this->csvImportService->importParticipants($uploadedFile, $event);
            
            return new JsonResponse([
                'message' => 'CSV import completed',
                'imported' => $result['imported'],
                'skipped' => $result['skipped'],
                'errors' => $result['errors']
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Import failed: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/send-invitations', name: 'send_invitations', methods: ['POST'])]
    public function sendInvitations(int $eventId): JsonResponse
    {
        $event = $this->entityManager->getRepository(Event::class)->find($eventId);
        if (!$event) {
            return new JsonResponse(['error' => 'Event not found'], 404);
        }

        if (!$this->isGrantedToEvent($event, 'edit')) {
    return new JsonResponse(['error' => 'Access denied'], 403);
}

        $participants = $this->participantRepository->findBy([
            'event' => $event,
            'status' => 'pending'
        ]);

        $sent = 0;
        foreach ($participants as $participant) {
            try {
                $this->invitationService->sendInvitation($participant);
                $sent++;
            } catch (\Exception $e) {
                // Log error but continue with other participants
                continue;
            }
        }

        return new JsonResponse([
            'message' => "Invitations sent to {$sent} participants"
        ]);
    }
}