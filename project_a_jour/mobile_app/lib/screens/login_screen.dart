import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../services/auth_service.dart';
import '../widgets/custom_button.dart';
import '../widgets/custom_text_field.dart';

class LoginScreen extends StatefulWidget {
  @override
  _LoginScreenState createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen>
    with SingleTickerProviderStateMixin {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  late AnimationController _animationController;
  late Animation<double> _fadeAnimation;
  late Animation<Offset> _slideAnimation;

  @override
  void initState() {
    super.initState();
    _animationController = AnimationController(
      duration: Duration(milliseconds: 1500),
      vsync: this,
    );
    _fadeAnimation = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(parent: _animationController, curve: Curves.easeInOut),
    );
    _slideAnimation = Tween<Offset>(
      begin: Offset(0, 0.5),
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
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _login() async {
    print('LOGIN BUTTON PRESSED');
    if (_formKey.currentState!.validate()) {
      //miantso ny service authService izy mba handefa ilay api any amin'ny backend symfonny hanaovana
      //an'ilay connexion
      final authService = Provider.of<AuthService>(context, listen: false);
      final success = await authService.login(
        _emailController.text.trim(),
        _passwordController.text,
      );

      if (!mounted) return;
      if (success) {
        //raha ohatra ka succes ilay izy dia dirigé aty amin'ny page principale home tsika
        //tonga dia dirigé automatique ato amin'ny home
        authService.clearError();
        Navigator.of(context).pushReplacementNamed('/home');
      } else {
        if (!mounted) return;
        print('LOGIN FAIL: ${authService.error}');
        // Ne rien faire ici, l'erreur sera affichée dans le widget build()

      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Container(
        decoration: BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
            colors: [
              Theme.of(context).primaryColor,
              Theme.of(context).primaryColor.withOpacity(0.8),
            ],
          ),
        ),
        child: SafeArea(
          child: Center(
            child: SingleChildScrollView(
              padding: EdgeInsets.all(24.0),
              child: FadeTransition(
                opacity: _fadeAnimation,
                child: SlideTransition(
                  position: _slideAnimation,
                  child: Card(
                    elevation: 8,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(16),
                    ),
                    child: Padding(
                      padding: EdgeInsets.all(32.0),
                      child: Form(
                        key: _formKey,
                        child: Column(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            // Manomboka eto ilay templAite ilay application mobile ilay flutter 
                            //eto ny logo
                            Container(
                              width: 80,
                              height: 80,
                              decoration: BoxDecoration(
                                color: Theme.of(context).primaryColor,
                                borderRadius: BorderRadius.circular(16),
                              ),
                              child: Icon(
                                Icons.qr_code_scanner,
                                size: 40,
                                color: Colors.white,
                              ),
                            ),
                            SizedBox(height: 24),

                            // Eto ilay titre
                            Text(
                              'Echeck-in Event',
                              style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                                fontWeight: FontWeight.bold,
                                color: Theme.of(context).primaryColor,
                              ),
                            ),
                            SizedBox(height: 8),
                            Text(
                              'Connexion Agent',
                              style: Theme.of(context).textTheme.bodyLarge?.copyWith(
                                color: Colors.grey[600],
                              ),
                            ),
                            SizedBox(height: 32),

                            // Manomboka eto ilay formulaire hampiidrany agent ilay login
                            CustomTextField(
                              //Champ email 
                              controller: _emailController,
                              label: 'Adresse e-mail',
                              hint: 'Adresse e-mail',
                              keyboardType: TextInputType.emailAddress,
                              prefixIcon: Icons.email_outlined,
                              validator: (value) {
                                if (value == null || value.isEmpty) {
                                  return 'Veuillez saisir votre adresse e-mail';
                                }
                                if (!RegExp(r'^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$')
                                    .hasMatch(value)) {
                                  return 'Veuillez saisir une adresse e-mail valide';
                                }
                                return null;
                              },
                            ),
                            SizedBox(height: 16),

                           //Champ mot de passe
                            CustomTextField(
                              controller: _passwordController,
                              label: 'Mot de passe',
                              hint: 'Mot de passe',
                              obscureText: true,
                              prefixIcon: Icons.lock_outlined,
                              validator: (value) {
                                if (value == null || value.isEmpty) {
                                  return 'Veuillez saisir votre mot de passe';
                                }
                                return null;
                              },
                            ),
                            SizedBox(height: 32),

                           // ato ny champ se connecter rehefa hiconnecter ilay agent bouton se connecter
                            Consumer<AuthService>(
                              builder: (context, authService, child) {
                                return Column(
                                  children: [
                                    CustomButton(
                                      text: 'Se connecter',
                                      onPressed: authService.isLoading ? null : _login,
                                      isLoading: authService.isLoading,
                                      width: double.infinity,
                                    ),
             //ity no maneho amin'ny ecran ireo erreur avy any amin'ny ialy backend symfony 
             //satria avy nanao io se connecter ambony io izy dia miseho ny retour avy any amin'ny api 
             //backend ilay symfony ato 
                                    if (authService.error != null && authService.error!.isNotEmpty)
                                      Padding(
                                        padding: const EdgeInsets.only(top: 16.0),
                                        child: Text(
                                          authService.error!,
                                          //message mena rehefa erreur
                                          style: TextStyle(color: Colors.red, fontWeight: FontWeight.bold),
                                          textAlign: TextAlign.center,
                                        ),
                                      ),
                                  ],
                                );
                              },
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}