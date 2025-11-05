<?php
namespace App\Controller;

use App\Entity\Event;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class EventGalleryController extends AbstractController
{
    #[Route('/events/{id}/send-gallery', name: 'app_events_send_gallery', methods: ['POST'])]
    public function sendGallery(
        //OMENY SYMFONY AUTOMATIQUE INJECTION AUTOMATIQUE
        Event $event, 
        MailerInterface $mailer
    ): Response {
        $this->denyAccessUnlessGranted('edit', $event);
        $guests = $event->getParticipants();
        $sentCount = 0;//Manomboka amin’ny isa 0 ny mail voaray.
        //generateUrl dia fonction an’i Symfony ahafahana manorina URL feno (ex: https://site.com/events/12/gallery).
        //ity ilay lien alefa par email vao cliquena dia mitondra makany amin'ny EventGalleryPublicController.php izay hiantsoy templaite
        $galleryUrl = $this->generateUrl('app_event_gallery', ['id' => $event->getId()], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL);
        $messageContent = sprintf('
            <table width="100%%" cellpadding="0" cellspacing="0" style="background:#f8fafc;padding:0;margin:0;font-family:Segoe UI,Arial,sans-serif;">
                <tr>
                    <td align="center" style="padding:32px 0 0 0;">
                        <div style="background:#6f42c1;color:#fff;font-size:2.2rem;font-weight:bold;border-radius:16px 16px 0 0;padding:18px 32px 10px 32px;max-width:520px;margin:auto;">
                            🎉 Souvenirs d\'événement !
                        </div>
                        <div style="background:#fff;max-width:520px;margin:auto;border-radius:0 0 16px 16px;box-shadow:0 4px 24px rgba(111,66,193,0.08);padding:28px 32px 24px 32px;">
                            <h2 style="color:#6f42c1;font-size:1.35rem;margin-top:0;margin-bottom:18px;text-align:center;">Les photos de l\'événement<br><span style="color:#0d6efd;">\"%s\"</span> sont en ligne !</h2>
                            <p style="font-size:1.08rem;color:#444;text-align:center;margin-bottom:30px;">Cliquez sur le bouton ci-dessous pour découvrir ou télécharger vos souvenirs :</p>
                            <div style="text-align:center;margin-bottom:32px;">
                                <a href="%s" style="display:inline-block;padding:14px 36px;background:linear-gradient(90deg,#6f42c1,#0d6efd);color:#fff;font-size:1.13rem;font-weight:600;border-radius:8px;text-decoration:none;box-shadow:0 2px 8px rgba(111,66,193,0.10);transition:background .18s;">🎈 Voir la galerie photo</a>
                            </div>
                            <div style="color:#888;font-size:1.02rem;text-align:center;">Partagez vos meilleurs moments et téléchargez vos photos favorites !<br><br>Cordialement,<br>L\'équipe <span style="color:#6f42c1;font-weight:bold;">Echeck-in</span></div>
                        </div>
                    </td>
                </tr>
            </table>',
            htmlspecialchars($event->getTitle()),//apetaka %s voalohany karazana titre fotsiny
            $galleryUrl // %s faharoa href du bouton, URL mankany amin’ny galerie, ampidirina ao amin’ny bouton.
        );
        foreach ($guests as $guest) {
            if (!$guest->getEmail()) continue;
            $email = (new \Symfony\Component\Mime\Email())
                ->from('no-reply@echeckin.local')
                ->to($guest->getEmail())
                ->subject(sprintf('Galerie photo de l’événement : %s', $event->getTitle()))
                ->html($messageContent);
            $mailer->send($email);
            $sentCount++;
        }
        $this->addFlash('success', sprintf('La galerie photo a été envoyée à %d invité(s) avec succès.', $sentCount));
        return $this->redirectToRoute('app_events_show', ['id' => $event->getId()]);
    }

    #[Route('/events/{id}/send-menu', name: 'app_events_send_menu', methods: ['POST'])]
    public function sendMenu(
        Event $event,
        MailerInterface $mailer,
    ): Response {
        $this->denyAccessUnlessGranted('edit', $event);
        if (!$event->getMenu()) {
            $this->addFlash('danger', 'Aucun menu renseigné pour cet événement.');
            return $this->redirectToRoute('app_events_show', ['id' => $event->getId()]);
        }
        $guests = $event->getParticipants();
        $sentCount = 0;
        $htmlBody = $this->renderView('emails/event_menu_notification.html.twig', [
            'event' => $event
        ]);
        foreach ($guests as $guest) {
            if (!$guest->getEmail()) continue;
            $email = (new \Symfony\Component\Mime\Email())
                ->from('no-reply@echeckin.local')
                ->to($guest->getEmail())
                ->subject(sprintf('Menu de l’événement : %s', $event->getTitle()))
                ->html($htmlBody);
            $mailer->send($email);
            $sentCount++;
        }
        $this->addFlash('success', sprintf('Le menu a été envoyé à %d invité(s) avec succès.', $sentCount));
        return $this->redirectToRoute('app_events_show', ['id' => $event->getId()]);
    }
}
