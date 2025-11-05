import 'dart:convert';
import 'package:http/http.dart' as http;

import '../models/participant.dart';
import '../models/check_in.dart';

import '../env.dart';

class ApiService {
  // Pour un téléphone physique, mets ici l'IP locale de ton PC (trouvé avec ipconfig)
// Exemple : 'http://192.168.1.10:8000'
// Pour l'émulateur Android : 'http://10.0.2.2:8000'

  
  final String? token;

  ApiService({this.token}) {
    print('ApiService initialized with token:');
    print(token);
  }

  Map<String, String> get headers => {
    'Content-Type': 'application/json',
    if (token != null) 'Authorization': 'Bearer $token',
  };

  Future<List<CheckIn>> getRecentScansPerEvent() async {
    print('getRecentScansPerEvent: token = $token');
    try {
      final response = await http.get(
        Uri.parse('$backendBaseUrl/api/checkin/recent-per-event'),
        headers: headers,
      );
      print('API /api/checkin/recent-per-event status: [33m[1m${response.statusCode}[0m');
      print('API /api/checkin/recent-per-event body: ${response.body}');
      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        final List<dynamic> checkInsJson = data['checkIns'];
        return checkInsJson.map((json) => CheckIn.fromJson(json)).toList();
      } else if (response.statusCode == 401) {
        throw Exception('Non autorisé : jeton invalide ou manquant');
      } else {
        throw Exception('Impossible de récupérer les derniers scans pour cet événement');
      }
    } catch (e) {
      print('Exception in getRecentScansPerEvent: $e');
      throw Exception('Network error: ${e.toString()}');
    }
  }

  Future<Map<String, dynamic>> verifyQrCode(String qrCode) async {
    try {
      final response = await http.get(
        Uri.parse('$backendBaseUrl/api/checkin/verify/$qrCode'),
        headers: headers,
      );
      print('verifyQrCode status: [33m${response.statusCode}[0m');
      print('verifyQrCode body: ${response.body}');
      if (response.statusCode == 200) {
        return json.decode(response.body);
      } else if (response.statusCode == 404) {
        throw Exception('QR code invalide');
      } else {
        try {
          final data = json.decode(response.body);
          throw Exception(data['reason'] ?? data['erreur'] ?? data['statut'] ?? 'Erreur serveur');
        } catch (e) {
          throw Exception('Erreur serveur');
        }
      }
    } catch (e) {
      // Si le message d'erreur contient déjà le motif précis du backend, ne pas l'envelopper
      if (e is Exception && e.toString().contains('Exception:') && !e.toString().contains('Network error')) {
        rethrow;
      }
      throw Exception('Network error: ${e.toString()}');
    }
  }

  Future<Map<String, dynamic>> checkInParticipant(
    String qrCode, {
    String? checkedInBy,
    String? notes,
  }) async {
    try {
      /*ity no miantso an'ilay HTTP POST amin'ny Symfony  rehefa en cas de changement evenement tampoka nefa mbola manao an'ilay scan ilay qr code ilay agent*/
      final response = await http.post(
        Uri.parse('$backendBaseUrl/api/checkin/$qrCode'),
        headers: headers,
        body: json.encode({
          'checkedInBy': checkedInBy ?? 'Mobile App',
          'notes': notes,
        }),
      );
      print('checkInParticipant status: [33m${response.statusCode}[0m');
      print('checkInParticipant body: ${response.body}');
      if (response.statusCode == 200) {
        //eto no mandray ilay retour vy any amin'ny backend symfony ialy json hoe success ilay izy 
        return json.decode(response.body);//mandray an'ialy json
      } else {
        try {
          final data = json.decode(response.body);
          throw Exception(data['reason'] ?? data['erreur'] ?? data['statut'] ?? 'Erreur serveur');
        } catch (e) {
          throw Exception('Erreur serveur');
        }
      }
      //raha erreur dia mitsanga ny exception
    } catch (e) {
      // Si le message d'erreur contient déjà le motif précis du backend, ne pas l'envelopper
      if (e is Exception && e.toString().contains('Exception:') && !e.toString().contains('Network error')) {
        rethrow;
      }
      throw Exception('Network error: ${e.toString()}');
    }
  }

  Future<List<Participant>> getEventParticipants(int eventId) async {
    try {
      final response = await http.get(
        Uri.parse('$backendBaseUrl/api/events/$eventId/participants'),
        headers: headers,
      );
      print('getEventParticipants status: [33m${response.statusCode}[0m');
      print('getEventParticipants body: ${response.body}');
      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        final List<dynamic> participantsJson = data['participants'];
        return participantsJson.map((json) => Participant.fromJson(json)).toList();
      } else {
        throw Exception('Impossible de charger les participants');
      }
    } catch (e) {
      // Si le message d'erreur contient déjà le motif précis du backend, ne pas l'envelopper
      if (e is Exception && e.toString().contains('Exception:') && !e.toString().contains('Network error')) {
        rethrow;
      }
      throw Exception('Network error: ${e.toString()}');
    }
  }

  Future<List<CheckIn>> getEventCheckIns(int eventId) async {
    try {
      final response = await http.get(
        Uri.parse('$backendBaseUrl/api/events/$eventId/checkins'),
        headers: headers,
      );
      print('getEventCheckIns status: [33m${response.statusCode}[0m');
      print('getEventCheckIns body: ${response.body}');
      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        final List<dynamic> checkInsJson = data['checkIns'];
        return checkInsJson.map((json) => CheckIn.fromJson(json)).toList();
      } else {
        throw Exception('Failed to load check-ins');
      }
    } catch (e) {
      // Si le message d'erreur contient déjà le motif précis du backend, ne pas l'envelopper
      if (e is Exception && e.toString().contains('Exception:') && !e.toString().contains('Network error')) {
        rethrow;
      }
      throw Exception('Network error: ${e.toString()}');
    }
  }

  Future<Map<String, dynamic>> getEventStatistics(int eventId) async {
    try {
      final response = await http.get(
        Uri.parse('$backendBaseUrl/api/events/$eventId/statistics'),
        headers: headers,
      );
      print('getEventStatistics status: [33m${response.statusCode}[0m');
      print('getEventStatistics body: ${response.body}');
      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return data['statistics'];
      } else {
        throw Exception('Failed to load statistics');
      }
    } catch (e) {
      // Si le message d'erreur contient déjà le motif précis du backend, ne pas l'envelopper
      if (e is Exception && e.toString().contains('Exception:') && !e.toString().contains('Network error')) {
        rethrow;
      }
      throw Exception('Network error: ${e.toString()}');
    }
  }
}