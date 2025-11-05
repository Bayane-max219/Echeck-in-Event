<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Participant;
use App\Form\ParticipantType;
use App\Repository\ParticipantRepository;
use App\Service\CsvImportService;
use App\Service\InvitationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/events/{eventId}/participants', name: 'app_participants_')]
class ParticipantController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ParticipantRepository $participantRepository,
        private CsvImportService $csvImportService,
        private InvitationService $invitationService
    ) {}

    #[Route('', name: 'index')]
    public function index(int $eventId): Response
    {
        $event = $this->entityManager->getRepository(Event::class)->find($eventId);
        if (!$event) {
            throw $this->createNotFoundException('Event not found');
        }

        
                $this->denyAccessUnlessGranted('view', $event);

        $participants = $this->participantRepository->findByEventWithCheckInStatus($eventId);

        return $this->render('participant/index.html.twig', [
            'event' => $event, /*variable event data alefa amin'ny templaite ny maba ho ampiasaina nay amin'ny templaite ohatra hoe {{ event.name }} {{ event.date }} {{ event.location }} */
            'participants' => $participants,/*variable participants  alefa amin'ny templaite mb aahafahana mampiasa  participant ohatra hoe {{ participants.name }} {{ participants.email }} {{ participants.phone }} {{ participants.checkedIn }}*/
        ]);
    }

    #[Route('/new', name: 'new')]
    public function new(Request $request, int $eventId): Response
    {
        $event = $this->entityManager->getRepository(Event::class)->find($eventId);
        if (!$event) {
            throw $this->createNotFoundException('Event not found');
        }

                $this->denyAccessUnlessGranted('edit', $event);

        $participant = new Participant();
        $participant->setEvent($event);
        
        $form = $this->createForm(ParticipantType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($participant);
            $this->entityManager->flush();

        // Génération automatique du token pour tous les participants
        if (!$participant->getConfirmationToken()) {
            $participant->setConfirmationToken(bin2hex(random_bytes(32)));
            $this->entityManager->flush();
        }

        // Mandefa an'ilay invitation tsy ilay indray mipalaka ho an'ny participant rehetra ary tsy ilay eo amin'ny liste de participant
        //  fa ilay participant eo amin'ny manao crée
            if ($form->get('sendInvitation')->getData()) {
                if (!$participant->getConfirmationToken()) {
                    $token = bin2hex(random_bytes(32));
                    $participant->setConfirmationToken($token);
                    $this->entityManager->flush();
                    // karazana debug fotsiny mba ahitana ny token ilay participant fa afaka esorina tsy misy effet aminy focntionnemet
                    file_put_contents(__DIR__ . '/../../../../token_debug.log', "Participant ID: {$participant->getId()} | Email: {$participant->getEmail()} | Token: $token\n", FILE_APPEND);
                }
                $this->invitationService->sendInvitation($participant);
                $this->addFlash('success', 'Participant ajouté et invité avec succès.');
            } else {
                $this->addFlash('success', 'Participant ajouté avec succès.');
            }

            return $this->redirectToRoute('app_participants_index', ['eventId' => $eventId]);
        }

        return $this->render('participant/new.html.twig', [
            'event' => $event,/*variable event data alefa amin'ny templaite ny maba ho ampiasaina nay amin'ny templaite ohatra hoe {{ event.name }} {{ event.date }} {{ event.location }}*/
            'participants' => $participant,/*variable participants  alefa amin'ny templaite mb aahafahana mampiasa  participant ohatra hoe {{ participants.name }} {{ participants.email }} {{ participants.phone }} {{ participants.checkedIn }}*/
            'form' => $form,/*variable form data alefa amin'ny templaite mb aahafahana mampiasa  participant ohatra hoe {{ form.name }} {{ form.email }} {{ form.phone }} {{ form.checkedIn }}*/
        ]);
    }

    #[Route('/{id}/edit', name: 'edit')]
    public function edit(Request $request, int $eventId, Participant $participant, TranslatorInterface $translator): Response
    {
        if ($participant->getEvent()->getId() !== $eventId) {
            throw $this->createNotFoundException('Participant not found in this event');
        }

        // Locale handling removed to fix syntax error
        $this->denyAccessUnlessGranted('edit', $participant->getEvent());

        $form = $this->createForm(ParticipantType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            $this->addFlash('success', 'Participant modifié avec succès.');

            return $this->redirectToRoute('app_participants_index', ['eventId' => $eventId]);
        }

        return $this->render('participant/edit.html.twig', [
            'event' => $participant->getEvent(),/*data alefa amin'ny templaite ny liste evenement*/
            'participant' => $participant,/*data alefa amin'ny templaite ny liste participant*/
            'form' => $form,/*data alefa amin'ny templaite ny formulaires*/
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, int $eventId, Participant $participant, TranslatorInterface $translator): Response
    {
        if ($participant->getEvent()->getId() !== $eventId) {
            throw $this->createNotFoundException('Participant not found in this event');
        }

        // Locale handling removed to fix syntax error
        $this->denyAccessUnlessGranted('edit', $participant->getEvent());

        if ($this->isCsrfTokenValid('delete'.$participant->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($participant);
            $this->entityManager->flush();
            $this->addFlash('success', 'Participant supprimé avec succès.');
        }

        return $this->redirectToRoute('app_participants_index', ['eventId' => $eventId]);
    }

    #[Route('/import', name: 'import')]
    public function import(Request $request, int $eventId): Response
    {
        $event = $this->entityManager->getRepository(Event::class)->find($eventId);
        if (!$event) {
            throw $this->createNotFoundException('Event not found');
        }

                $this->denyAccessUnlessGranted('edit', $event);

        if ($request->isMethod('POST')) {
            $uploadedFile = $request->files->get('csv_file');
            if ($uploadedFile) {
                try {
                    $result = $this->csvImportService->importParticipants($uploadedFile, $event);
                    
                    $this->addFlash('success', 'Import terminé : ' . $result['imported'] . ' importés, ' . $result['skipped'] . ' ignorés.');

                    if (!empty($result['errors'])) {
                        foreach ($result['errors'] as $error) {
                            $this->addFlash('warning', $error);
                        }
                    }
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Échec de l’import des participants : ' . $e->getMessage());
                }
            } else {
                $this->addFlash('error', 'Veuillez sélectionner un fichier CSV.');
            }

            return $this->redirectToRoute('app_participants_index', ['eventId' => $eventId]);
        }

        return $this->render('participant/import.html.twig', [
            'event' => $event,
        ]);
    }
//Mandefa any invitation indray mipalaka ho an'ny participant rehetra 
    #[Route('/send-invitations', name: 'send_invitations', methods: ['POST'])]
    public function sendInvitations(int $eventId): Response
    {
        $event = $this->entityManager->getRepository(Event::class)->find($eventId);
        if (!$event) {
            throw $this->createNotFoundException('Event not found');
        }

                $this->denyAccessUnlessGranted('edit', $event);

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
                // raha ohatra ka misy erreur dia tonga dia dinganina dia miditra amin'ny participant manaraka
                continue;
            }
        }

        $this->addFlash('success', $sent . ' invitations envoyées.');

        return $this->redirectToRoute('app_participants_index', ['eventId' => $eventId]);
    }

    #[Route('/{participantId}/send-invitation', name: 'send_invitation', methods: ['POST', 'GET'])]
    public function sendInvitation(int $eventId, int $participantId, InvitationService $invitationService, EntityManagerInterface $em, TranslatorInterface $translator): Response
    {
        $participant = $em->getRepository(Participant::class)->find($participantId);
        if (!$participant) {
            throw $this->createNotFoundException('Participant non trouvé');
        }

        // Locale handling removed to fix syntax error
        $invitationService->sendInvitation($participant);
        $this->addFlash('success', 'Invitation envoyée à ' . $participant->getEmail() . '.');
        return $this->redirectToRoute('app_participants_index', ['eventId' => $eventId]);
    }

    #[Route('/bulk-delete', name: 'bulk_delete', methods: ['POST'])] 
    public function bulkDelete(Request $request, int $eventId): Response
    {
        $event = $this->entityManager->getRepository(Event::class)->find($eventId);
        if (!$event) {
            throw $this->createNotFoundException('Event not found');
        }

        $this->denyAccessUnlessGranted('edit', $event);

        if ($this->isCsrfTokenValid('bulk_delete', $request->request->get('_token'))) {
            $participantIds = $request->request->get('participant_ids');
            
            if ($participantIds) {
                $ids = explode(',', $participantIds);
                $deletedCount = 0;
                
                foreach ($ids as $id) {
                    $participant = $this->participantRepository->find(trim($id));
                    if ($participant && $participant->getEvent()->getId() === $eventId) {
                        $this->entityManager->remove($participant);
                        $deletedCount++;
                    }
                }
                
                $this->entityManager->flush();
                $this->addFlash('success', $deletedCount . ' participant(s) supprimé(s) avec succès.');
            } else {
                $this->addFlash('error', 'Aucun participant sélectionné.');
            }
        } else {
            $this->addFlash('error', 'Token de sécurité invalide.');
        }

        return $this->redirectToRoute('app_participants_index', ['eventId' => $eventId]);
    }

}