import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:http/http.dart' as http;
import '../env.dart';
import 'dart:convert';

import '../models/user.dart';
import 'api_service.dart';

class AuthService extends ChangeNotifier {
  static const String _tokenKey = 'auth_token';
  static const String _userKey = 'user_data';

  bool _isAuthenticated = false;
  bool _isLoading = false;
  String? _token;
  User? _user;
  String? _error;

  bool get isAuthenticated => _isAuthenticated;
  bool get isLoading => _isLoading;
  String? get token => _token;
  User? get user => _user;
  String? get error => _error;

  Future<void> checkAuthStatus() async {
    _isLoading = true;
    notifyListeners();

    try {
      final prefs = await SharedPreferences.getInstance();
      _token = prefs.getString(_tokenKey);
      
      if (_token != null) {
        final userData = prefs.getString(_userKey);
        if (userData != null) {
          _user = User.fromJson(json.decode(userData));
          _isAuthenticated = true;
        }
      }
    } catch (e) {
      print('Error checking auth status: $e');
    }

    _isLoading = false;
    notifyListeners();
  }

  Future<bool> login(String email, String password) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final response = await http.post(
        //eto no mandefa ny HTTP POST amin'ny backedn symfony 
        Uri.parse('$backendBaseUrl/api/agent/login'),
        headers: {'Content-Type': 'application/json'},
        body: json.encode({
          //eto anaovana verification email sy password  
          //eto koa mandray valiny token erreur sns
          'email': email,
          'password': password,
        }),
      );
      print('LOGIN RESPONSE: ${response.statusCode} - ${response.body}');
  //eto no mandra ny reponse json avy any amin'ny backend 
      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data is Map && data.containsKey('token')) {
          _token = data['token'];
          // Utilise directement l'objet agent du login (pas besoin d'appel /api/profile)
          try {
            _user = User.fromJson(data['agent']);
          } catch (e) {
            _error = 'Erreur lors de l’analyse du profil agent';
            _isLoading = false;
            notifyListeners();
            return false;
          }
          final prefs = await SharedPreferences.getInstance();
          await prefs.setString(_tokenKey, _token!);
          await prefs.setString(_userKey, json.encode(_user!.toJson()));
          _isAuthenticated = true;
          _isLoading = false;
          notifyListeners();
          return true;
        } else {
          _error = data['error'] ?? data['erreur'] ?? data['message'] ?? 'Identifiants invalides (email ou mot de passe incorrect)';
          _isLoading = false;
          notifyListeners();
          return false;
        }
      } else {
        try {
          final data = json.decode(response.body);
          // Priorité au message 'error' du backend, puis 'erreur', puis 'message'
          _error = data['error'] ?? data['erreur'] ?? data['message'] ?? 'Identifiants invalides (email ou mot de passe incorrect)';
        } catch (e) {
          _error = 'Identifiants invalides (email ou mot de passe incorrect)';
        }
        _isLoading = false;
        notifyListeners();
        return false;
      }
    } catch (e) {
      _error = 'Erreur réseau : ${e.toString()}';
      _isLoading = false;
      notifyListeners();
      return false;
    }

    _isLoading = false;
    notifyListeners();
    return false;
  }

  Future<void> logout() async {
    _isAuthenticated = false;
    _token = null;
    _user = null;
    _error = null;

    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_tokenKey);
    await prefs.remove(_userKey);

    notifyListeners();
  }

  void clearError() {
    _error = null;
    notifyListeners();
  }
}