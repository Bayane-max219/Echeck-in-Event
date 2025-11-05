<?php

namespace App\Service;

use App\Entity\Invitation;
use App\Entity\Participant;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;
use Twig\Environment;

class InvitationService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MailerInterface $mailer,
        private Environment $twig,
        private QrCodeService $qrCodeService,
        private string $fromEmail = 'noreply@echeck-in.com'
    ) {}
        //Participant ihany no miantso an'ity service ity fa ilay agent tsia ato no mamaorona an'ilay qr code
    public function sendInvitation(Participant $participant): void
    {
        $event = $participant->getEvent();
        
        // Jereo raha efa misy invitation ho an’io participant sy event io.
        $invitation = $this->entityManager->getRepository(Invitation::class)
            ->findOneBy(['participant' => $participant, 'event' => $event]);
        
        if (!$invitation) {
            $invitation = new Invitation();
            $invitation->setEvent($event)
                ->setParticipant($participant);
            $this->entityManager->persist($invitation);
        }

        // Mamorona fichier QR code vonjimaika (temporaire). miantso an'ilay qrCodeService hanamboatra an'ilay qr code
        $qrCodePath = sys_get_temp_dir() . '/qr_' . $participant->getQrCode() . '.png';
        $this->qrCodeService->generateQrCodeFile($participant->getQrCode(), $qrCodePath);

        // Manoratra log debug amin’ny fichier (fanaraha-maso token/lien).
        //fanaraha maso mamorona string misy info momba ilay invitation ilay partcipant(iza no nhz invitation ; inona no otken ; inona no lien de confirmation nalefa)
        $logData = sprintf(
            "[Invitation Debug] Date: %s\nParticipant ID: %s | Email: %s\nParticipant Token: %s\nInvitation Token: %s\nLien envoyé: %s\n\n",
            date('Y-m-d H:i:s'),
            $participant->getId(),
            $participant->getEmail(),
            $participant->getConfirmationToken(),
            $invitation->getToken(),
            $this->twig->render('emails/invitation_link_debug.txt.twig', [
                'participant' => $participant,
                'invitation' => $invitation
            ])
        );
        file_put_contents(__DIR__ . '/../invitation_debug.log', $logData, FILE_APPEND);

        // Manamboatra an'ilay email 
        $email = (new Email())
            ->from($this->fromEmail)
            ->to($participant->getEmail())
            ->subject('Invitation: ' . $event->getTitle())
            ->html($this->twig->render('emails/invitation.html.twig', [
                'participant' => $participant,
                'event' => $event,
                'invitation' => $invitation,
                //lien de confirmation antsoina io any ambany amin'ilay fonctuion anaovana an'ilay status ialy participant ho lasa confirmé
                'confirmationUrl' => sprintf('%s/confirm/%s', $_ENV['BASE_URL'] ?? 'http://127.0.0.1:8000', $invitation->getToken())/*ity Token ity tadiavina nay amin'ny InvitationController*///base url mamorona string url en local
            ]))
            ->addPart(new DataPart(fopen($qrCodePath, 'r'), 'qr-code.png', 'image/png'));//qr code

        // mandefa email
        $this->mailer->send($email);

        // Manova status
        $invitation->setStatus('sent');
        $participant->setStatus('invited');
        $this->entityManager->flush();

        // Mamafa ilay fichier QR code temporaire.
        if (file_exists($qrCodePath)) {
            unlink($qrCodePath);
        }
    }// tapitra eto
//mbola tsy vita developper izay manao reminder ilay participant izay fa mbola ilay mandefa invitaion fotsiny donc tsy ilaina ity ambany ity 
    public function sendReminder(Participant $participant): void
    {
        $event = $participant->getEvent();
        $invitation = $this->entityManager->getRepository(Invitation::class)
            ->findOneBy(['participant' => $participant, 'event' => $event]);

        if (!$invitation || $invitation->getStatus() === 'confirmed') {
            return;
        }

        // Generate QR code file
        $qrCodePath = sys_get_temp_dir() . '/qr_' . $participant->getQrCode() . '.png';
        $this->qrCodeService->generateQrCodeFile($participant->getQrCode(), $qrCodePath);

        // Prepare reminder email
        $email = (new Email())
            ->from($this->fromEmail)
            ->to($participant->getEmail())
            ->subject('Reminder: ' . $event->getTitle())
            ->html($this->twig->render('emails/reminder.html.twig', [
                'participant' => $participant,
                'event' => $event,
                'invitation' => $invitation
            ]))
            ->addPart(new DataPart(fopen($qrCodePath, 'r'), 'qr-code.png', 'image/png'));

        // Send email
        $this->mailer->send($email);

        // Clean up temporary file
        if (file_exists($qrCodePath)) {
            unlink($qrCodePath);
        }
    }
//Ity dia miandraikitra ny confirmation ilay invitation rehefa tsindrian'ilay participant ilay lien ao amin'ny email 
    public function confirmInvitation(string $token): bool
    {
        $invitation = $this->entityManager->getRepository(Invitation::class)
            ->findOneBy(['token' => $token]);

        if (!$invitation) {
            return false;
        }
        //raha misy ilayinvitation
        $invitation->confirm();
        $invitation->getParticipant()->setStatus('confirmed');
        
        $this->entityManager->flush();

        return true;
    }
}