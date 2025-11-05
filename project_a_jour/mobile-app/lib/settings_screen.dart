import 'package:flutter/material.dart';
import 'auth_service.dart';
import 'user_model.dart';
import 'main.dart';

class SettingsScreen extends StatefulWidget {
  const SettingsScreen({Key? key}) : super(key: key);

  @override
  State<SettingsScreen> createState() => _SettingsScreenState();
}

class _SettingsScreenState extends State<SettingsScreen> {
  User? _user;
  bool _loading = true;
  bool _darkMode = false;
  String _selectedLang = 'en';

  @override
  void initState() {
    super.initState();
    _loadUser();
    _darkMode = themeNotifier.value == ThemeMode.dark;
    _selectedLang = localeNotifier.value.languageCode;
  }

  Future<void> _loadUser() async {
    final user = await AuthService.getUser();
    setState(() {
      _user = user;
      _loading = false;
    });
  }

  Future<void> _logout() async {
    await AuthService.logout();
    if (mounted) {
      Navigator.of(context).pushNamedAndRemoveUntil('/login', (route) => false);
    }
  }

  void _toggleDarkMode(bool value) {
    setState(() => _darkMode = value);
    MyApp.setThemeMode(value ? ThemeMode.dark : ThemeMode.light);
  }

  void _changeLanguage(String code) {
    setState(() => _selectedLang = code);
    MyApp.setLocale(Locale(code));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Settings')),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _user == null
              ? const Center(child: Text('No agent info found.'))
              : Padding(
                  padding: const EdgeInsets.all(24.0),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          const Icon(Icons.person, size: 40),
                          const SizedBox(width: 16),
                          Text(
                            '${_user!.firstName} ${_user!.lastName}',
                            style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold),
                          ),
                        ],
                      ),
                      const SizedBox(height: 16),
                      Text('Email: ${_user!.email}'),
                      Text('Status: ${_user!.isConfirmed ? 'Confirmed' : 'Not confirmed'}'),
                      Text('Roles: ${_user!.roles.join(", ")}'),
                      Text('Created at: ${_user!.createdAt}'),
                      const Divider(height: 32),
                      SwitchListTile(
                        title: const Text('Dark mode'),
                        value: _darkMode,
                        onChanged: _toggleDarkMode,
                        secondary: Icon(_darkMode ? Icons.dark_mode : Icons.light_mode),
                      ),
                      ListTile(
                        leading: const Icon(Icons.language),
                        title: const Text('Language'),
                        trailing: DropdownButton<String>(
                          value: _selectedLang,
                          onChanged: (v) => _changeLanguage(v ?? 'en'),
                          items: const [
                            DropdownMenuItem(value: 'en', child: Text('English')),
                            DropdownMenuItem(value: 'fr', child: Text('Français')),
                          ],
                        ),
                      ),
                      const Spacer(),
                      SizedBox(
                        width: double.infinity,
                        child: ElevatedButton.icon(
                          onPressed: _logout,
                          icon: const Icon(Icons.logout),
                          label: const Text('Logout'),
                          style: ElevatedButton.styleFrom(backgroundColor: Colors.red),
                        ),
                      ),
                    ],
                  ),
                ),
    );
  }
}
