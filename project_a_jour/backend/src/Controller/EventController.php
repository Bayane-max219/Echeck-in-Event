<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use App\Service\ExportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

use App\Repository\ParticipantRepository;

#[Route('/events', name: 'app_events_')]
class EventController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private EventRepository $eventRepository,
        private ExportService $exportService,
        private ParticipantRepository $participantRepository
    ) {}

    #[Route('', name: 'index')]
    public function index(): Response
    {
        $user = $this->getUser();
        $events = $this->eventRepository->findBy(['organizer' => $user], ['createdAt' => 'DESC']);

        return $this->render('event/index.html.twig', [
            'events' => $events,
        ]);
    }

    #[Route('/new', name: 'new')]
    public function new(Request $request, TranslatorInterface $translator): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event->setOrganizer($this->getUser());
            // Log temporaire pour debug locale
            file_put_contents(__DIR__.'/event_locale_debug.log', '[NEW] locale: '.($request->getLocale() ?? 'NULL')."\n", FILE_APPEND);
            $event->setLocale($request->getLocale() ?? 'fr');
            $this->entityManager->persist($event);
            $this->entityManager->flush();

            $this->addFlash('success', 'Événement créé avec succès.');
            return $this->redirectToRoute('app_events_show', ['id' => $event->getId()]);
        }

        return $this->render('event/new.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'])]
    public function show(Event $event): Response
    {
        $this->denyAccessUnlessGranted('view', $event);

        $stats = [
            'totalParticipants' => $event->getParticipantCount(),
            'checkedInParticipants' => $event->getCheckedInCount(),
            'attendanceRate' => $event->getAttendanceRate(),
        ];

        return $this->render('event/show.html.twig', [
            'event' => $event,
            'stats' => $stats,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit')]
    public function edit(Request $request, Event $event, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('edit', $event);

        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event->setUpdatedAt(new \DateTimeImmutable());
            // Correction : forcer la locale même si le champ n’est pas dans le form
            $event->setLocale($request->getLocale() ?? 'fr');
            $this->entityManager->flush();

            $this->addFlash('success', 'Événement modifié avec succès');
            return $this->redirectToRoute('app_events_show', ['id' => $event->getId()]);
        }

        return $this->render('event/edit.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Event $event, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('delete', $event);

        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->request->get('_token'))) {
            try {
                $this->entityManager->remove($event);
                $this->entityManager->flush();
                $this->addFlash('success', 'Événement supprimé avec succès.');
            } catch (\Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException | \Doctrine\ORM\Exception\ForeignKeyConstraintViolationException $e) {
                $this->addFlash('danger', "Impossible de supprimer l'événement car il est lié à d'autres données (participants, photos, etc.).");
            } catch (\Throwable $e) {
                $this->addFlash('danger', "Erreur lors de la suppression de l'événement : " . $e->getMessage());
            }
        }

        return $this->redirectToRoute('app_events_index');
    }

    #[Route('/{id}/participants', name: 'participants')]
    public function participants(Event $event): Response
    {
        $this->denyAccessUnlessGranted('view', $event);

        $participants = $this->participantRepository->findByEventWithCheckInStatus($event->getId());
        return $this->render('participant/index.html.twig', [
            'event' => $event,
            'participants' => $participants,
        ]);
    }

    #[Route('/{id}/export', name: 'export')]
    public function export(Event $event, \Symfony\Component\HttpFoundation\Request $request): Response
    {
        $this->denyAccessUnlessGranted('view', $event);

        $locale = $request->getLocale();
        $filename = $this->exportService->exportEventToExcel($event, $locale);
        return $this->file($filename, 'event_' . $event->getId() . '_export.xlsx');
    }

    #[Route('/{id}/notify-guests', name: 'notify_guests', methods: ['POST'])]
    public function notifyGuests(Event $event, \Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\Mailer\MailerInterface $mailer, \Symfony\Contracts\Translation\TranslatorInterface $translator): \Symfony\Component\HttpFoundation\Response
    {
        $this->denyAccessUnlessGranted('edit', $event);
        $messageContent = $request->request->get('message');
        $guests = $event->getParticipants();
        $sentCount = 0;
        
        // Remettre tous les participants en statut "invited" pour reconfirmation
        // IMPORTANT: Invalider tous les anciens QR codes pour éviter les fraudes
        foreach ($guests as $guest) {
            $guest->setStatus('invited');
            // Générer un token de confirmation s'il n'en a pas
            if (!$guest->getConfirmationToken()) {
                $guest->setConfirmationToken(bin2hex(random_bytes(32)));
            }
            // SÉCURITÉ: Invalider l'ancien QR code immédiatement
            // Un nouveau sera généré seulement lors de la reconfirmation
            // Utiliser un token unique "INVALID_" pour éviter les conflits de contrainte unique
            $guest->setQrCode('INVALID_' . bin2hex(random_bytes(16)));
            $this->entityManager->persist($guest);
        }
        $this->entityManager->flush();
        
        foreach ($guests as $guest) {
            if (!$guest->getEmail()) continue;
            $htmlBody = $this->renderView('emails/event_change_notification.html.twig', [
                'event' => $event,
                'message' => $messageContent,
                'participant' => $guest
            ]);
            $email = (new \Symfony\Component\Mime\Email())
                ->from('no-reply@echeckin.local')
                ->to($guest->getEmail())
                ->subject(sprintf('Changement de programme : %s - Reconfirmation requise', $event->getTitle()))
                ->html($htmlBody);
            $mailer->send($email);
            $sentCount++;
        }
        $this->addFlash('success', sprintf('La notification a été envoyée à %d invité(s). Tous les participants sont maintenant invités et en attente de reconfirmation.', $sentCount));
        return $this->redirectToRoute('app_events_show', ['id' => $event->getId()]);
    }
}