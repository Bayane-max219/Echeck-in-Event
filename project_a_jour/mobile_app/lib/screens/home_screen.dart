import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../services/auth_service.dart';
import 'settings_screen.dart';
import 'scanner_screen.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({Key? key}) : super(key: key);
  @override
  _HomeScreenState createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> with SingleTickerProviderStateMixin {
  late AnimationController _animationController;
  late Animation<double> _fadeAnimation;
  late Animation<Offset> _slideAnimation;

  @override
  void initState() {
    super.initState();
    _animationController = AnimationController(
      duration: const Duration(milliseconds: 1000),
      vsync: this,
    );
    _fadeAnimation = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(parent: _animationController, curve: Curves.easeInOut),
    );
    _slideAnimation = Tween<Offset>(
      begin: const Offset(0, 0.3),
      end: Offset.zero,
    ).animate(CurvedAnimation(
      parent: _animationController,
      curve: Curves.easeOutCubic,
    ));
    _animationController.forward();
  }

  @override
  void dispose() {
    _animationController.dispose();
    super.dispose();
  }

  void _showLogoutDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Déconnexion'),
        content: const Text('Êtes-vous sûr de vouloir vous déconnecter ?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Annuler'),
          ),
          TextButton(
            onPressed: () {
              Navigator.pop(context);
              Provider.of<AuthService>(context, listen: false).logout();
              Navigator.of(context).pushReplacementNamed('/login');
            },
            child: const Text('Déconnexion'),
          ),
        ],
      ),
    );
  }
  //ITY ILAY TEMPLAITE ILAY MANEHO ILAY CAMERA FANAOVANA SCAN
//ity no mamaly ilay appele ialy on tap ary amin'ny card ilay scan qr ity no manoatra an'ilay
//apapreil hanaovana an'ilay scan 
  Widget _buildActionCard(
    BuildContext context, {
    required String title,
    required String subtitle,
    required IconData icon,
    required Color color,
    required VoidCallback onTap,
    double height = 180,
  }) {
    return AspectRatio(
      aspectRatio: 1/1.1,
      child: Card(
        elevation: 4,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(16),
        ),
        //ity InKwell ity manko ilay recepteur ilay onTap
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(16),
          child: Padding(
            padding: const EdgeInsets.all(16.0),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Container(
                  width: 60,
                  height: 60,
                  decoration: BoxDecoration(
                    color: color.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: Icon(
                    icon,
                    size: 30,
                    color: color,
                  ),
                ),
                const SizedBox(height: 12),
                Text(
                  title,
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    fontWeight: FontWeight.bold,
                    fontSize: 12,
                  ),
                  textAlign: TextAlign.center,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
                const SizedBox(height: 4),
                Text(
                  subtitle,
                  style: Theme.of(context).textTheme.bodySmall?.copyWith(
                    color: Colors.grey[600],
                    fontSize: 10,
                  ),
                  textAlign: TextAlign.center,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
//Manomboka eto ilay home ao amin'ilay page home
  @override
  Widget build(BuildContext context) {
    final authService = Provider.of<AuthService>(context);
    final user = authService.user;
    return Scaffold(
      //ireto le app bar ambony amin'ilay application le eny entete rehefa ao amin'ny page home ilay application iny 
      appBar: AppBar(
        title: const Text('Echeck-in Event'),
        elevation: 0,
        actions: [
          PopupMenuButton<String>(
            onSelected: (value) {
              if (value == 'Déconnexion') {
                _showLogoutDialog();
              }
            },
            itemBuilder: (context) => [
              PopupMenuItem(
                value: 'Déconnexion',
                child: Row(
                  children: const [
                    Icon(Icons.logout, color: Colors.red),
                    SizedBox(width: 8),
                    Text('Déconnexion'),
                  ],
                ),
              ),
            ],
          ),
        ],
      ),
      body: Container(
        decoration: BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: [
              Theme.of(context).primaryColor.withOpacity(0.1),
              Colors.white,
            ],
          ),
        ),
        child: SafeArea(
          child: Padding(
            padding: const EdgeInsets.all(24.0),
            child: FadeTransition(
              opacity: _fadeAnimation,
              child: SlideTransition(
                position: _slideAnimation,
                child: LayoutBuilder(
                  builder: (context, constraints) {
                    return SingleChildScrollView(
                      physics: constraints.maxHeight > 600 ? const NeverScrollableScrollPhysics() : null,
                      child: ConstrainedBox(
                        constraints: BoxConstraints(minHeight: constraints.maxHeight),
                        child: IntrinsicHeight(
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            crossAxisAlignment: CrossAxisAlignment.stretch,
                            children: [
                              //eto no maneho ilay agent hoe bon retour iny le manao welcome iny
                              Card(
                                elevation: 4,
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(16),
                                ),
                                child: Padding(
                                  padding: const EdgeInsets.all(24.0),
                                  child: Row(
                                    children: [
                                      CircleAvatar(
                                        radius: 30,
                                        backgroundColor: Theme.of(context).primaryColor,
                                        child: Text(
                                          user?.firstName.substring(0, 1).toUpperCase() ?? 'U',
                                          style: const TextStyle(
                                            fontSize: 24,
                                            fontWeight: FontWeight.bold,
                                            color: Colors.white,
                                          ),
                                        ),
                                      ),
                                      const SizedBox(width: 16),
                                      Expanded(
                                        child: Column(
                                          crossAxisAlignment: CrossAxisAlignment.start,
                                          children: [
                                            const Text('Bon retour !', style: TextStyle(fontSize: 16, color: Colors.grey)),
                                            const SizedBox(height: 4),
                                            Text(user?.firstName ?? 'User', style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold)),
                                          ],
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ),
                              //Eto ilay actions rapides
                              const SizedBox(height: 32),
                              Text('Actions rapides', style: Theme.of(context).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.bold)),
                              const SizedBox(height: 16),
                              Wrap(
                                spacing: 16,
                                runSpacing: 16,
                                crossAxisAlignment: WrapCrossAlignment.center,
                                children: [
                                  //Eto le maneho card izay ahitana ilay scan QR iny ity zany ilay cate eh
                                  SizedBox(
                                    width: MediaQuery.of(context).size.width / 2 - 40,
                                    child: _buildActionCard(
                                      context,
                                      title: 'Scanner un QR Code',
                                      subtitle: 'Scanner le badge participant',
                                      icon: Icons.qr_code_scanner,
                                      color: Colors.blue,
                                      //vao mikitika an'ilay carte dia manatanteraka an'ialy onTap
                                      onTap: () {
              //eto zany vao mitaper an'ilay scan izy dia miantso navigator.push io alefany any amin'ny scanner_screen.dart
                                        Navigator.push(
                                          context,
                                          MaterialPageRoute(builder: (context) => ScannerScreen()),
                                        );
                                      },
                                      height: 180,
                                    ),
                                  ),
                                  //Eto le maneho card izay ahitana ilay parametres iny
                                  SizedBox(
                                    width: MediaQuery.of(context).size.width / 2 - 40,
                                    child: _buildActionCard(
                                      context,
                                      title: 'Paramètres',
                                      subtitle: 'Préférences de l’application',
                                      icon: Icons.settings,
                                      color: Colors.purple,
                                      //VAO MIKITIKA TAP DIA ALEFANY MAKANY settings_screen.dart
                                      onTap: () {
                                        Navigator.push(
                                          context,
                                          MaterialPageRoute(builder: (context) => SettingsScreen()),
                                        );
                                      },
                                      height: 180,
                                    ),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ),
                      ),
                    );
                  },
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}

                             