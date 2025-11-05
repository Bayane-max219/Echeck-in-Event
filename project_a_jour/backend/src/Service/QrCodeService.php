<?php
//Mampiditra ireo classe avy amin’ny library endroid/qr-code hanaovana QR code.
namespace App\Service;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;

class QrCodeService
{
    public function __construct(
        private string $baseUrl //ampiasaina hanamboarana ny lien ao anaty QR code.
    ) {}
     // Mamerina QR code amin’ny endrika string (data URI) ho an’ny HTML.   
    public function generateQrCode(string $qrCode): string
    {
        $url = $this->baseUrl . '/checkin/' . $qrCode; // Manorina URL feno ohatra https://mon-site.com/checkin/abc123 izay ho encode ao amin’ny QR code.

        $result = Builder::create() // Manomboka manorina QR code.
            ->writer(new PngWriter())
            ->data($url)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::High) //Correction d’erreur ambony (azo vakiana na dia simba aza).
            ->size(300)
            ->margin(10)
            ->roundBlockSizeMode(RoundBlockSizeMode::Margin) //style le qr code natao arrondie
            ->build();

        return $result->getDataUri(); //Mamerina string (data URI) azo aseho mivantana amin’ny HTML <img src="..." />.
    }
    //Mitahiry QR code ho fichier PNG amin’ny disk.
    public function generateQrCodeFile(string $qrCode, string $filePath): void
    {
        //manao hotrany le teo aloha indray 
        $url = $this->baseUrl . '/checkin/' . $qrCode;

        $result = Builder::create()
            ->writer(new PngWriter())
            ->data($url)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::High)
            ->size(300)
            ->margin(10)
            ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->build();

        $result->saveToFile($filePath); 
    }
}