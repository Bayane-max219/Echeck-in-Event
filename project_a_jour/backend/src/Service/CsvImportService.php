<?php

namespace App\Service;

use App\Entity\Event;
use App\Entity\Participant;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CsvImportService
{
    public function __construct(
        private EntityManagerInterface $entityManager,//Mitahiry ny service ilaina ho an’ny base sy ny participant.
        private ParticipantRepository $participantRepository
    ) {}

    public function importParticipants(UploadedFile $file, Event $event): array
    {
        //Manomana variable ho an’ny statistika
        $imported = 0;
        $skipped = 0;
        $errors = [];

        //Sokafana ilay fichier, alaina ny lohateny (header) amin’ny tsipika voalohany.
        if (($handle = fopen($file->getPathname(), 'r')) !== false) {
            $header = fgetcsv($handle);
            
      //ireto no colonne tsy maintsy misy amin’ny CSV
$requiredColumns = [
    'firstName' => ['firstName', 'prénom', 'prenom'],
    'lastName' => ['lastName', 'nom'],
    'email' => ['email', 'adresse e-mail', 'adresse email', 'mail']
];
            $columnMap = $this->mapColumns($header, $requiredColumns);// miantso fonction hanampy hahafantatra hoe colonne faha-x amin’ny CSV dia “firstName”, sns.
            
            if (!$columnMap) {
                // Raha tsy hita ao amin’ny CSV ny colonne “prénom”, “nom”, “email”, dia manome erreur sy manazava hoe inona no colonne ilaina.
                $colNames = [];
                foreach ($requiredColumns as $labels) {
                    if (is_array($labels)) {
                        $colNames[] = $labels[0];
                    } else {
                        $colNames[] = $labels;
                    }
                }
                throw new \InvalidArgumentException('CSV must contain columns: ' . implode(', ', $colNames));
            }

            $rowNumber = 1; //laharana andalana (ligne) amin’ny CSV.
            while (($data = fgetcsv($handle)) !== false) {
                $rowNumber++;
                
                try {
                    $participantData = $this->extractParticipantData($data, $columnMap, $header);
                    
                    // Jereny raha efa misy participant amin’io event io sy email io
                    $existingParticipant = $this->participantRepository->findOneBy([
                        'event' => $event,
                        'email' => $participantData['email']
                    ]);

                    if ($existingParticipant) {
                        $skipped++;
                        continue;
                    }

                    // Mamorona objet Participant Mametraka anarana, email, sns. Ampifandraisina amin’ny event.
                    $participant = new Participant();
                    $participant->setFirstName($participantData['firstName'])
                        ->setLastName($participantData['lastName'])
                        ->setEmail($participantData['email'])
                        ->setPhone($participantData['phone'] ?? null)
                        ->setCompany($participantData['company'] ?? null)
                        ->setPosition($participantData['position'] ?? null)
                        ->setEvent($event);

                    $this->entityManager->persist($participant);
                    $imported++;//manampy isa tafiditra.

                    // Isaky ny 100 tafiditra dia manoratra ao amin’ny base (flush) ho an’ny performance.
                    if ($imported % 100 === 0) {
                        $this->entityManager->flush();
                    }

                } catch (\Exception $e) { //Raha misy erreur amin’ny ligne iray dia mitahiry hafatra erreur amin’ny $errors.
                    $errors[] = "Row {$rowNumber}: " . $e->getMessage();
                }
            }
//Aorian’ny famakiana rehetra dia atao flush farany. Akatona ilay fichier (fclose).
            fclose($handle);
            $this->entityManager->flush();
        }

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors
        ];
    }

    private function mapColumns(array $header, array $requiredColumns): ?array // Tanjon’ity fonction ity Mitady sy mametraka hoe colonne faha-x amin’ny CSV dia “firstName”, “lastName”, “email”, sns.
    // Mandeha amin’ny required columns (ex: “firstName”, “lastName”, “email”).
    //Jereo amin’ny lohateny rehetra ao amin’ny CSV raha misy mitovy amin’ireo anarana azo atao (ex: “prénom”, “firstName”).
    //Raha mahita dia mitahiry hoe “firstName” dia ao amin’ny colonne faha-x.
    //Raha tsy mahita dia mamerina null (midika hoe tsy valide ilay CSV).
    {
        $columnMap = [];
        foreach ($requiredColumns as $field => $possibleLabels) {
            $found = false;
            foreach ($header as $index => $column) {
                foreach ($possibleLabels as $label) {
                    if (strtolower(trim($column)) === strtolower($label)) {
                        $columnMap[$field] = $index;
                        $found = true;
                        break 2;
                    }
                }
            }
            if (!$found) {
                return null;
            }
        }
        //Avy eo, mitady koa ny colonne optionnelle (phone, company, position) amin’ny fomba mitovy.
        $optionalColumns = [
            'phone' => ['phone', 'téléphone', 'telephone'],
            'company' => ['company', 'société', 'societe'],
            'position' => ['position', 'fonction']
        ];
        //mitovy aminn'ny le etape etsy ambony ihany mitady header rah mahita ilay tadiaviny amin'tableau exel dia raisiny fa raha tsia dia avela imposible ialy csv
        foreach ($optionalColumns as $field => $possibleLabels) {
            foreach ($header as $index => $column) {
                foreach ($possibleLabels as $label) {
                    if (strtolower(trim($column)) === strtolower($label)) {
                        $columnMap[$field] = $index;
                        break 2;
                    }
                }
            }
        }
        return $columnMap;
    }

    private function extractParticipantData(array $data, array $columnMap, array $header): array
    {
        $participantData = [];
        
        foreach ($columnMap as $field => $index) {
            $value = isset($data[$index]) ? trim($data[$index]) : null;//Maka ny sandan’ny colonne faha-x amin’ny tsipika CSV (ex: $data[0] = “Jean” ho an’ny “firstName”).
//Raha tsy misy dia null.
            $participantData[$field] = $value;//mitahiry ilay azo t@ tableau
        }

        // Raha tsy feno “firstName”, “lastName”, na “email” dia manome erreur.
        if (empty($participantData['firstName'])) {
            throw new \InvalidArgumentException('First name is required');
        }
        if (empty($participantData['lastName'])) {
            throw new \InvalidArgumentException('Last name is required');
        }
        if (empty($participantData['email']) || !filter_var($participantData['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Valid email is required');
        }

        return $participantData;
    }
}