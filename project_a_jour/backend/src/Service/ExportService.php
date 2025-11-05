<?php

namespace App\Service;

use App\Entity\Event;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

use Symfony\Contracts\Translation\TranslatorInterface;

class ExportService
{
    private TranslatorInterface $translator;
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }
    public function exportEventToExcel(Event $event, string $locale = 'en'): string
    {
        $spreadsheet = new Spreadsheet(); //Mamorona objet Excel vaovao
        $sheet = $spreadsheet->getActiveSheet(); // maka ilay “feuille” (sheet) voalohany hiasana.

        // Event information
        $sheet->setCellValue('A1', 'Rapport d’événement : ' . $event->getTitle()); //Manoratra “Rapport d’événement : [titre]” amin’ny cellule A1.
        $sheet->mergeCells('A1:F1');//Mampitambatra ny cellule A1 ka hatramin’ny F1 (titre lehibe).
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A3', 'Détails de l’événement');
        $sheet->getStyle('A3')->getFont()->setBold(true);
        $sheet->setCellValue('A4', 'Date');
        $sheet->setCellValue('B4', $event->getStartDate()->format('Y-m-d H:i'));
        $sheet->setCellValue('A5', 'Lieu');
        $sheet->setCellValue('B5', $event->getLocation());
        $sheet->setCellValue('A6', 'Nombre total de participants');
        $sheet->setCellValue('B6', $event->getParticipantCount());
        $sheet->setCellValue('A7', 'Nombre de présents');
        $sheet->setCellValue('B7', $event->getCheckedInCount());
        $sheet->setCellValue('A8', 'Taux de présence');
        $sheet->setCellValue('B8', round($event->getAttendanceRate(), 2) . '%');

        //table ny participant
        $sheet->setCellValue('A10', 'Liste des participants');
        $sheet->getStyle('A10')->getFont()->setBold(true);

        // Tête tableau exel
        $headers = ['Prénom', 'Nom', 'Adresse e-mail', 'Entreprise', 'Statut', 'Heure d’arrivée'];
$col = 'A';
foreach ($headers as $header) {//teteziny lohateny reny 
    $sheet->setCellValue($col . '11', $header);
    $sheet->getStyle($col . '11')->getFont()->setBold(true);
    $sheet->getStyle($col . '11')->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FFDDDDDD');//gris clair 
    $col++;//mandroso amin'ny colonnne manaraka A,B,C,D
}

        // Donée ny participant 
        $row = 12;//manomboka ligne 12
        foreach ($event->getParticipants() as $participant) { //teteziny participant rehetra
            $sheet->setCellValue('A' . $row, $participant->getFirstName());
            $sheet->setCellValue('B' . $row, $participant->getLastName());
            $sheet->setCellValue('C' . $row, $participant->getEmail());
            $sheet->setCellValue('D' . $row, $participant->getCompany());
            $statusFr = match ($participant->getStatus()) {
    'checked_in' => 'Présent',
    'invited' => 'Invité',
    'pending' => 'En attente',
    'confirmed' => 'Confirmé',
    'cancelled' => 'Annulé',
    default => $participant->getStatus(),
};
$sheet->setCellValue('E' . $row, $statusFr); //Manoratra ny statut amin’ny colonne E.
            
            $checkInTime = '';
            if (!$participant->getCheckIns()->isEmpty()) {
                $checkInTime = $participant->getCheckIns()->first()->getCheckedInAt()->format('Y-m-d H:i:s');
            }
            $sheet->setCellValue('F' . $row, $checkInTime);
            
            $row++;//mandroso amin'ny ligne manaraka
        }

        // Atao automatique ny largeur an’ny colonne rehetra (A hatramin’ny F), mba hifanaraka amin’ny habetsahan’ny texte.
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // 
        $filename = sys_get_temp_dir() . '/event_' . $event->getId() . '_' . time() . '.xlsx';//Mamorona anarana fichier temporaire.
        $writer = new Xlsx($spreadsheet);//Manoratra ilay fichier Excel amin’ny serveur.
        $writer->save($filename);

        return $filename;
    }
//Mbola tsy foncitonnel ity 
    public function exportEventToPdf(Event $event): string
    {
        // Implementation for PDF export using TCPDF or similar
        // This is a placeholder - you would implement PDF generation here
        throw new \Exception('PDF export not implemented yet');
    }
}