<?php
namespace App\Controller;

use App\Entity\Event;
use App\Entity\EventPhoto;
use App\Form\EventPhotoType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
//ato an'ny admin resaka gestion des photos satria ato izy manao ajout syppression photos 
class EventPhotoAdminController extends AbstractController
{
    #[Route('/events/{id}/photos', name: 'app_events_photos', methods: ['GET', 'POST'])] // Mampiseho pejy fitantanana sary.Mandefa formulaire hanampiana sary.
    public function managePhotos(Event $event, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('edit', $event);
        $photo = new EventPhoto();
        $form = $this->createForm(EventPhotoType::class, $photo);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //Maka ilay fichier
            $file = $form['filename']->getData();
            if ($file) {
                $filename = uniqid('eventphoto_') . '.' . $file->guessExtension();//Mamorona anarana unique ho an’ilay fichier.
                $file->move(
                    $this->getParameter('event_photos_directory'), //Mametaka ilay fichier ao amin’ny dossier photos.
                    $filename
                );
                $photo->setFilename($filename);
                $photo->setEvent($event);
                $em->persist($photo);
                $em->flush();
                $this->addFlash('success', 'Photo ajoutée avec succès !');
                return $this->redirectToRoute('app_events_photos', ['id' => $event->getId()]);
            }
        }
        return $this->render('event/photos_admin.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
            'photos' => $event->getPhotos(),
        ]);
    }

    #[Route('/photo/{id}/delete', name: 'event_photo_delete', methods: ['POST'])]//rehefa mamafa tsy maintsy methode post fona
    public function deletePhoto(EventPhoto $photo, EntityManagerInterface $em, Request $request): Response
    {
        $event = $photo->getEvent();
        $this->denyAccessUnlessGranted('edit', $event);
        if ($this->isCsrfTokenValid('delete' . $photo->getId(), $request->request->get('_token'))) {
            $photoPath = $this->getParameter('event_photos_directory') . '/' . $photo->getFilename();//Maka ny path an’ilay fichier sary amin’ny disk.
            if (file_exists($photoPath)) {
                @unlink($photoPath);// mamafa amin’ny disk (unlink).
            }
            $em->remove($photo);
            $em->flush();
            $this->addFlash('success', 'Photo supprimée avec succès.');
        } else {
            $this->addFlash('danger', 'Jeton CSRF invalide.');
        }
        return $this->redirectToRoute('app_events_photos', ['id' => $event->getId()]);
    }
}
