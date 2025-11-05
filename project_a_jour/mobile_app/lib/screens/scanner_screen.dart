import 'package:flutter/material.dart';
import 'dart:convert';
import 'package:provider/provider.dart';
import 'package:qr_code_scanner/qr_code_scanner.dart';

import '../services/auth_service.dart';
import '../services/api_service.dart';
import '../models/participant.dart';
import '../widgets/custom_button.dart';

class ScannerScreen extends StatefulWidget {
  @override
  _ScannerScreenState createState() => _ScannerScreenState();
}

class _ScannerScreenState extends State<ScannerScreen> {
  final GlobalKey qrKey = GlobalKey(debugLabel: 'QR');
  QRViewController? controller;
  bool isProcessing = false;
  String? lastScannedCode;

  @override
  void reassemble() {
    super.reassemble();
    if (controller != null) {
      controller!.pauseCamera();
      controller!.resumeCamera();
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Scanner un QR Code'),
        backgroundColor: Colors.transparent,
        elevation: 0,
        actions: [
          IconButton(
            icon: Icon(Icons.flash_on),
            onPressed: () async {
              await controller?.toggleFlash();
            },
          ),
          IconButton(
            icon: Icon(Icons.flip_camera_ios),
            onPressed: () async {
              await controller?.flipCamera();
            },
          ),
        ],
      ),
      extendBodyBehindAppBar: true,
      body: Column(
        children: [
          Expanded(
            flex: 4,
            child: Stack(
              children: [
                QRView(
                  key: qrKey,
                  onQRViewCreated: _onQRViewCreated,
                  overlay: QrScannerOverlayShape(
                    borderColor: Theme.of(context).primaryColor,
                    borderRadius: 16,
                    borderLength: 30,
                    borderWidth: 8,
                    cutOutSize: 250,
                  ),
                ),
                if (isProcessing)
                  Container(
                    color: Colors.black54,
                    child: Center(
                      child: Card(
                        child: Padding(
                          padding: EdgeInsets.all(24.0),
                          child: Column(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              CircularProgressIndicator(),
                              SizedBox(height: 16),
                              Text('Traitement en cours...'),
                            ],
                          ),
                        ),
                      ),
                    ),
                  ),
              ],
            ),
          ),
          Expanded(
            flex: 1,
            child: Container(
              padding: EdgeInsets.all(24.0),
              child: Column(
                children: [
                  Text(
                    'Placez le QR code dans le cadre',
                    style: Theme.of(context).textTheme.titleMedium,
                    textAlign: TextAlign.center,
                  ),
                  SizedBox(height: 8),
                  Text(
                    'La caméra scannera automatiquement lorsqu’un QR code est détecté',
                    style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                      color: Colors.grey[600],
                    ),
                    textAlign: TextAlign.center,
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  void _onQRViewCreated(QRViewController controller) {
    this.controller = controller;
    controller.scannedDataStream.listen((scanData) {
      if (!isProcessing && scanData.code != null && scanData.code != lastScannedCode) {
        lastScannedCode = scanData.code;
        _processQRCode(scanData.code!);
      }
    });
  }

  Future<void> _processQRCode(String qrCode) async {
    if (isProcessing) return;

    setState(() {
      isProcessing = true;
    });

    try {
      final authService = Provider.of<AuthService>(context, listen: false);
      final apiService = ApiService(token: authService.token);
// Debug print for token
print('Token sent for check-in: ${authService.token}');
if (authService.token == null || authService.token!.isEmpty) {
  _showErrorDialog('Authentication token is missing. Please log in again.');
  setState(() {
    isProcessing = false;
    lastScannedCode = null;
  });
  return;
}

      // First verify the QR code
      // Normalize QR code: extract only the code if it's a URL
String cleanQr = qrCode.trim();
if (cleanQr.contains('/')) {
  cleanQr = cleanQr.split('/').last;
}
final verificationResult = await apiService.verifyQrCode(cleanQr);
      
      if (verificationResult['valid'] == true) {
        final participant = Participant.fromJson(verificationResult['participant']);
        final alreadyCheckedIn = verificationResult['alreadyCheckedIn'] ?? false;

        if (alreadyCheckedIn) {
          _showParticipantDialog(
            participant: participant,
            isAlreadyCheckedIn: true,
          );
        } else {
          _showCheckInDialog(participant, qrCode, apiService);
        }
      }
    } catch (e) {
      _showErrorDialog(e.toString());
    } finally {
      setState(() {
        isProcessing = false;
        lastScannedCode = null;
      });
    }
  }

  void _showCheckInDialog(Participant participant, String qrCode, ApiService apiService) {
    final notesController = TextEditingController();
    
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => AlertDialog(
        title: Text('Enregistrement du participant'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              participant.fullName,
              style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                fontWeight: FontWeight.bold,
              ),
            ),
            SizedBox(height: 8),
            Text('Email: ${participant.email}'),
            if (participant.company != null) ...[
              SizedBox(height: 4),
              Text('Company: ${participant.company}'),
            ],
            if (participant.position != null) ...[
              SizedBox(height: 4),
              Text('Position: ${participant.position}'),
            ],
            SizedBox(height: 16),
            TextField(
              controller: notesController,
              decoration: InputDecoration(
                labelText: 'Notes (optionnel)',
                border: OutlineInputBorder(),
              ),
              maxLines: 2,
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () {
              Navigator.pop(context);
              controller?.resumeCamera();
            },
            child: Text('Annuler'),
          ),
          ElevatedButton(
            onPressed: () async {
              Navigator.pop(context);
              await _performCheckIn(participant, participant.qrCode, apiService, notesController.text);
            },
            child: Text('Enregistrer'),
          ),
        ],
      ),
    );
  }
  Future<void> _performCheckIn(
    Participant participant,
    String qrCode,
    ApiService apiService,
    String notes,
  ) async {
    try {
      final authService = Provider.of<AuthService>(context, listen: false);
      /*ity ligne ity no miantso an'ilay api *//*appelle fonction/service*//*maintso ny services hoe mba ataovy check in ity *//*ilay service dia api_service.dart*/
      //resaka changement de evenement au moment scan qr code na resaka participant deja enregistré arakaraka ny retour avy any amin'ny api backend
      await apiService.checkInParticipant(
        qrCode,
        checkedInBy: authService.user?.fullName ?? 'Mobile App',
        notes: notes.isNotEmpty ? notes : null,
      );
      //raha tsy misy erreur lors de scan satria ilay participant mbola tsy enregistré dia manao success izy 
      //donc ity ilay antsoina any ambany hoe afficheo eo amin'ny ecran 
      _showSuccessDialog(participant);
      
    } catch (e) {
      //raha misy erreur lors du scan dia manao catch mandray ny reception ilay valiny erreur iny izay avy any amin'ny backend na ilay erreur 403 changement de evenement na 409 participant deja enregistré
      //dia manao showErrorDialog dia ity aseho any ambany any amin'ny void_showError(String error)
      _showErrorDialog(e.toString());
    }
  }

  void _showParticipantDialog({
    required Participant participant,
    required bool isAlreadyCheckedIn,
  }) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Row(
          children: [
            Icon(
              isAlreadyCheckedIn ? Icons.check_circle : Icons.person,
              color: isAlreadyCheckedIn ? Colors.green : Colors.blue,
            ),
            SizedBox(width: 8),
            Text(isAlreadyCheckedIn ? 'Déjà enregistré' : 'Informations du participant'),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              participant.fullName,
              style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                fontWeight: FontWeight.bold,
              ),
            ),
            SizedBox(height: 8),
            Text('Email: ${participant.email}'),
            if (participant.company != null) ...[
              SizedBox(height: 4),
              Text('Company: ${participant.company}'),
            ],
            if (participant.position != null) ...[
              SizedBox(height: 4),
              Text('Position: ${participant.position}'),
            ],
            if (isAlreadyCheckedIn) ...[
              SizedBox(height: 16),
              Container(
                padding: EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: Colors.green.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: Colors.green),
                ),
                child: Row(
                  children: [
                    Icon(Icons.check_circle, color: Colors.green),
                    SizedBox(width: 8),
                    Text(
                      'Ce participant est déjà enregistré',
                      style: TextStyle(color: Colors.green),
                    ),
                  ],
                ),
              ),
            ],
          ],
        ),
        actions: [
          TextButton(
            onPressed: () {
              Navigator.pop(context);
              controller?.resumeCamera();
            },
            child: Text('OK'),
          ),
        ],
      ),
    );
  }
//Affichage  ity no maneho ilay amin'ny ecran ilay mobile hoe 200 ilayparticipant enregistré avec succès 
//ity showSuccessDialog ilay tany ambony affichena aty ambany tsy tafiditra ao amin'ilay catch 
  void _showSuccessDialog(Participant participant) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Row(
          children: [
            Icon(Icons.check_circle, color: Colors.green),
            SizedBox(width: 8),
            Text('Succès !'),
          ],
        ),
        content: Text('${participant.fullName} a été enregistré avec succès.'),
        actions: [
          TextButton(
            onPressed: () {
              Navigator.pop(context);
              controller?.resumeCamera();
            },
            child: Text('Continuer le scan'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              Navigator.pop(context);
            },
            child: Text('Terminé'),
          ),
        ],
      ),
    );
  }

//Affichage  ity no maneho ilay amin'ny ecran ilay mobile hoe 403 ve changement evenement ny retour 
//sa ve 409 participant efa deja enregistré
  void _showErrorDialog(String error) {
    try {
      showDialog(
        context: context,
        builder: (context) {
          String rawJson = '';
          String displayError = error;
          try {
            // Extraire le vrai message JSON si possible
            final match = RegExp(r'\{.*\}').firstMatch(error);
            if (match != null) {
              final jsonStr = match.group(0);
              if (jsonStr != null) {
                final data = json.decode(jsonStr);
                rawJson = '\n\n[RAW JSON]\n' + jsonStr;
                if (data['reason'] != null) displayError = data['reason'];
                else if (data['error'] != null) displayError = data['error'];
                else if (data['status'] != null) displayError = data['status'];
              }
            }
          } catch (e, stack) {
            print('[ERROR_DIALOG_JSON_PARSE] $e\n$stack');
            // On garde displayError = error
          }
          return AlertDialog(
            title: Row(
              children: [
                Icon(Icons.error, color: Colors.red),
                SizedBox(width: 8),
                Text('Erreur'),
              ],
            ),
            content: Text(displayError + rawJson),
            actions: [
              TextButton(
                onPressed: () {
                  Navigator.pop(context);
                  controller?.resumeCamera();
                },
                child: Text('Réessayer'),
              ),
            ],
          );
        },
      );
    } catch (e, stack) {
      print('[ERROR_DIALOG_CRASH] $e\n$stack');
      // Si même le showDialog crashe, on fait rien mais on logge
    }
  }

  @override
  void dispose() {
    controller?.dispose();
    super.dispose();
  }
}