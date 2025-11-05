import 'package:flutter/material.dart';

import 'package:provider/provider.dart';
import '../services/auth_service.dart';
import '../utils/theme_provider.dart';

class SettingsScreen extends StatefulWidget {
  @override
  State<SettingsScreen> createState() => _SettingsScreenState();
}

class _SettingsScreenState extends State<SettingsScreen> {

  String _colorName(MaterialColor color) {
    if (color == Colors.blue) return 'Bleu';
    if (color == Colors.green) return 'Vert';
    if (color == Colors.purple) return 'Violet';
    return 'Personnalisé';
  }

  @override
  void initState() {
    super.initState();
    // Ne pas utiliser Theme.of(context) ici
  }
//ato ny manao ny UI na ny tmeplaite ilay parametre
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Paramètres'),
      ),
      body: ListView(
        padding: EdgeInsets.all(24),
        children: [
          ListTile(
            leading: Icon(Icons.dark_mode),
            title: Text('Mode sombre'),
            trailing: Switch(
              value: Provider.of<ThemeProvider>(context).themeMode == ThemeMode.dark,
              onChanged: (val) {
                Provider.of<ThemeProvider>(context, listen: false)
                    .setThemeMode(val ? ThemeMode.dark : ThemeMode.light);
              }
            ),
          ),
          Divider(),
          ListTile(
            leading: Icon(Icons.color_lens),
            title: Text('Couleur principale'),
            subtitle: Text(_colorName(Provider.of<ThemeProvider>(context).mainColor)), // Affichage simple
            trailing: DropdownButton<MaterialColor>(
              value: Provider.of<ThemeProvider>(context).mainColor,
              items: [
                DropdownMenuItem(child: Text('Bleu'), value: Colors.blue),
                DropdownMenuItem(child: Text('Vert'), value: Colors.green),
                DropdownMenuItem(child: Text('Violet'), value: Colors.purple),
              ],
              onChanged: (color) {
                if (color != null) {
                  Provider.of<ThemeProvider>(context, listen: false)
                      .setMainColor(color);
                }
              },
            ),
          ),
          Divider(),
          ListTile(
            leading: Icon(Icons.logout, color: Colors.red),
            title: Text('Se déconnecter', style: TextStyle(color: Colors.red)),
            onTap: () async {
              final confirm = await showDialog<bool>(
                context: context,
                builder: (context) => AlertDialog(
                  title: const Text('Déconnexion'),
                  content: const Text('Êtes-vous sûr de vouloir vous déconnecter ?'),
                  actions: [
                    TextButton(
                      onPressed: () => Navigator.pop(context, false),
                      child: const Text('Annuler'),
                    ),
                    TextButton(
                      onPressed: () => Navigator.pop(context, true),
                      child: const Text('Déconnexion'),
                    ),
                  ],
                ),
              );
              if (confirm == true) {
                await Provider.of<AuthService>(context, listen: false).logout();
                Navigator.of(context).pushReplacementNamed('/login');
              }
            },
          ),
          Divider(),
          ListTile(
            leading: Icon(Icons.info_outline),
            title: Text('À propos'),
            subtitle: Text('Echeck-in Event v1.0'),
          ),
        ],
      ),
    );
  }
}
