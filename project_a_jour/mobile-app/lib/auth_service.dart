import 'package:shared_preferences/shared_preferences.dart';
import 'dart:convert';
import 'user_model.dart';

class AuthService {
  static const String _tokenKey = 'auth_token';
  static const String _userKey = 'user_info';

  // Save token and user info
  static Future<void> saveAuthData(String token, User user) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_tokenKey, token);
    await prefs.setString(_userKey, jsonEncode({
      'id': user.id,
      'email': user.email,
      'firstName': user.firstName,
      'lastName': user.lastName,
      'roles': user.roles,
      'createdAt': user.createdAt,
      'is_confirmed': user.isConfirmed,
    }));
  }

  // Get token
  static Future<String?> getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(_tokenKey);
  }

  // Get user info
  static Future<User?> getUser() async {
    final prefs = await SharedPreferences.getInstance();
    final userJson = prefs.getString(_userKey);
    if (userJson == null) return null;
    try {
      final map = jsonDecode(userJson);
      return User.fromJson(map);
    } catch (_) {
      return null;
    }
  }

  // Logout
  static Future<void> logout() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_tokenKey);
    await prefs.remove(_userKey);
  }
}
