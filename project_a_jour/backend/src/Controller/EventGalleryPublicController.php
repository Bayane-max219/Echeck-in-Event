<?php
namespace App\Controller;

use App\Entity\Event;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
// Mampiseho galerie photo amin’ny public (affichage).
class EventGalleryPublicController extends AbstractController
{
    #[Route('/events/{id}/gallery', name: 'app_event_gallery')]
    public function gallery(Event $event): Response
    {
        // On suppose que getPhotos() retourne une liste d'objets Photo avec ->getFilename()
        $photos = $event->getPhotos();
        return $this->render('event/gallery.html.twig', [
            'event' => $event,
            'photos' => $photos,
        ]);
    }
}
