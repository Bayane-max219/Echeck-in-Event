<?php
namespace App\Controller;

use App\Entity\Event;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\EventPhoto;

class EventPhotoZipController extends AbstractController
{
    #[Route('/events/{id}/photos/download', name: 'event_photos_download_zip')]
    public function downloadZip(Event $event): Response
    {
        $photosDir = $this->getParameter('kernel.project_dir') . '/public/uploads/event_photos/';//Dossier misy ny sary rehetra an'ny event.
        $eventPhotos = $event->getPhotos(); //Lisitry ny sary an'ilay event 
        $zipPath = sys_get_temp_dir() . '/event_' . $event->getId() . '_photos.zip';//Path temporaire ho an'ilay fichier ZIP

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) { //Manokatra (na mamorona) fichier ZIP vaovao (overwrite raha efa misy).
            throw new \Exception('Impossible de créer le fichier zip.');
        }
        //Raha misy ilay fichier amin'ny disk: ampidirina ao anaty ZIP, amin'ny anaran'ilay fichier.
        foreach ($eventPhotos as $photo) {
            $filePath = $photosDir . $photo->getFilename();
            if (file_exists($filePath)) {
                $zip->addFile($filePath, $photo->getFilename());
            }
        }
        $zip->close();

        return $this->file($zipPath, 'photos_evenement_' . $event->getId() . '.zip')->deleteFileAfterSend(true);
    }

    #[Route('/events/{id}/photos/download-selected', name: 'event_photos_download_selected_zip', methods: ['POST'])]
    public function downloadSelectedZip(Event $event, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('download_selected', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('app_events_photos', ['id' => $event->getId()]);
        }

        $photoIds = $request->request->get('photo_ids');
        if (!$photoIds) {
            $this->addFlash('error', 'Aucune photo sélectionnée.');
            return $this->redirectToRoute('app_events_photos', ['id' => $event->getId()]);
        }

        $ids = explode(',', $photoIds);
        $photosDir = $this->getParameter('kernel.project_dir') . '/public/uploads/event_photos/';
        $zipPath = sys_get_temp_dir() . '/event_' . $event->getId() . '_selected_photos_' . time() . '.zip';

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \Exception('Impossible de créer le fichier zip.');
        }

        $addedCount = 0;
        foreach ($ids as $photoId) {
            $photo = $em->getRepository(EventPhoto::class)->find(trim($photoId));
            if ($photo && $photo->getEvent()->getId() === $event->getId()) {
                $filePath = $photosDir . $photo->getFilename();
                if (file_exists($filePath)) {
                    $zip->addFile($filePath, $photo->getFilename());
                    $addedCount++;
                }
            }
        }

        $zip->close();

        if ($addedCount === 0) {
            @unlink($zipPath);
            $this->addFlash('error', 'Aucune photo valide trouvée.');
            return $this->redirectToRoute('app_events_photos', ['id' => $event->getId()]);
        }

        return $this->file($zipPath, 'photos_selectionnees_' . $event->getId() . '.zip')->deleteFileAfterSend(true);
    }

    #[Route('/events/{id}/photos/download-selected-public', name: 'event_photos_download_selected_zip_public', methods: ['POST'])]
    public function downloadSelectedZipPublic(Event $event, Request $request, EntityManagerInterface $em): Response
    {
        $photoIds = $request->request->get('photo_ids');
        if (!$photoIds) {
            return new Response('Aucune photo sélectionnée.', 400);
        }

        $ids = explode(',', $photoIds);
        $photosDir = $this->getParameter('kernel.project_dir') . '/public/uploads/event_photos/';
        $zipPath = sys_get_temp_dir() . '/event_' . $event->getId() . '_selected_photos_public_' . time() . '.zip';

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \Exception('Impossible de créer le fichier zip.');
        }

        $addedCount = 0;
        foreach ($ids as $photoId) {
            $photo = $em->getRepository(EventPhoto::class)->find(trim($photoId));
            if ($photo && $photo->getEvent()->getId() === $event->getId()) {
                $filePath = $photosDir . $photo->getFilename();
                if (file_exists($filePath)) {
                    $zip->addFile($filePath, $photo->getFilename());
                    $addedCount++;
                }
            }
        }

        $zip->close();

        if ($addedCount === 0) {
            @unlink($zipPath);
            return new Response('Aucune photo valide trouvée.', 400);
        }

        return $this->file($zipPath, 'photos_selectionnees_evenement.zip')->deleteFileAfterSend(true);
    }
}
