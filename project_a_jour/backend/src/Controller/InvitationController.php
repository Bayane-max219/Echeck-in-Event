<?php

namespace App\Controller;

use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
//manova ny participant ho lasa confirmé 
class InvitationController extends AbstractController
{
    #[Route('/confirm/{token}', name: 'app_invitation_confirm')]
    public function confirmInvitation(
        string $token,
        ParticipantRepository $participantRepository,
        EntityManagerInterface $em
    ): Response {
        // Mitady ao amin'ny base raha misy invitation manana io token io.
        $invitation = $em->getRepository(\App\Entity\Invitation::class)->findOneBy(['token' => $token]) /*ity Token ity nalefa avy  any amin'ny InvitationService*/;
        if (!$invitation) {
            throw $this->createNotFoundException('Invalid or expired confirmation link.');
        }

        // miantso ny méthode conifrm ary manova ny status ilay participant ho lasa confirmé
        $invitation->confirm();
        $invitation->getParticipant()->setStatus('confirmed');
        $em->flush();

        // Afficher un message de succès
        return $this->render('participant/confirmation_success.html.twig', [
            'participant' => $invitation->getParticipant(),
        ]);
    }

    #[Route('/reconfirm/{token}', name: 'app_invitation_reconfirm')]
    public function reconfirmInvitation(
        string $token,
        ParticipantRepository $participantRepository,
        EntityManagerInterface $em
    ): Response {
        // Chercher le participant par son token de confirmation
        $participant = $participantRepository->findOneBy(['confirmationToken' => $token]);
        if (!$participant) {
            throw $this->createNotFoundException('Lien de reconfirmation invalide ou expiré.');
        }

        // IMPORTANT: Générer un nouveau QR code lors de la reconfirmation
        // L'ancien QR code devient invalide
        $newQrCode = bin2hex(random_bytes(16)); // Nouveau QR code unique
        $participant->setQrCode($newQrCode);
        
        // Générer l'image QR code
        $result = Builder::create()
            ->writer(new PngWriter())
            ->data($newQrCode)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::Low)
            ->size(200)
            ->margin(10)
            ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->build();
        
        // Convertir en base64 pour l'affichage
        $qrCodeDataUri = $result->getDataUri();
        
        // Confirmer la participation après changement
        $participant->setStatus('confirmed');
        $em->flush();

        // Afficher un message de succès avec le nouveau QR code
        return $this->render('participant/reconfirmation_success.html.twig', [
            'participant' => $participant,
            'event' => $participant->getEvent(),
            'newQrCode' => $newQrCode,
            'qrCodeImage' => $qrCodeDataUri,
        ]);
    }

    #[Route('/decline/{token}', name: 'app_invitation_decline')]
    public function declineInvitation(
        string $token,
        ParticipantRepository $participantRepository,
        EntityManagerInterface $em
    ): Response {
        // Chercher le participant par son token de confirmation
        $participant = $participantRepository->findOneBy(['confirmationToken' => $token]);
        if (!$participant) {
            throw $this->createNotFoundException('Lien invalide ou expiré.');
        }

        // Marquer comme décliné
        $participant->setStatus('declined');
        $em->flush();

        // Afficher un message de confirmation
        return $this->render('participant/decline_success.html.twig', [
            'participant' => $participant,
            'event' => $participant->getEvent(),
        ]);
    }
}
