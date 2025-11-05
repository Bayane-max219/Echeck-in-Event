import 'package:flutter/material.dart';
import 'recent_scans_screen.dart';
import 'statistics_screen.dart';
import 'settings_screen.dart';
import 'utils/theme.dart';
import 'package:flutter_localizations/flutter_localizations.dart';

final ValueNotifier<ThemeMode> themeNotifier = ValueNotifier(ThemeMode.light);
final ValueNotifier<Locale> localeNotifier = ValueNotifier(const Locale('en'));

void main() {
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({Key? key}) : super(key: key);

  static void setThemeMode(ThemeMode mode) => themeNotifier.value = mode;
  static void setLocale(Locale locale) => localeNotifier.value = locale;

  @override
  Widget build(BuildContext context) {
    return ValueListenableBuilder<ThemeMode>(
      valueListenable: themeNotifier,
      builder: (context, themeMode, _) {
        return ValueListenableBuilder<Locale>(
          valueListenable: localeNotifier,
          builder: (context, locale, _) {
            return MaterialApp(
              debugShowCheckedModeBanner: false,
              title: 'Event Check-in',
              theme: AppTheme.lightTheme,
              darkTheme: AppTheme.darkTheme,
              themeMode: themeMode,
              locale: locale,
              supportedLocales: const [Locale('en'), Locale('fr')],
              localizationsDelegates: const [
                GlobalMaterialLocalizations.delegate,
                GlobalWidgetsLocalizations.delegate,
                GlobalCupertinoLocalizations.delegate,
              ],
              home: const HomeScreen(),
              routes: {
                '/recent-scans': (_) => const RecentScansScreen(),
                '/statistics': (_) => const StatisticsScreen(),
                '/settings': (_) => const SettingsScreen(),
              },
            );
          },
        );
      },
    );
  }
}

class HomeScreen extends StatelessWidget {
  const HomeScreen({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Echeck-in Event')),
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            ElevatedButton(
              onPressed: () => Navigator.pushNamed(context, '/recent-scans'),
              child: const Text('Recent Scans'),
            ),
            ElevatedButton(
              onPressed: () => Navigator.pushNamed(context, '/statistics'),
              child: const Text('Statistics'),
            ),
            ElevatedButton(
              onPressed: () => Navigator.pushNamed(context, '/settings'),
              child: const Text('Settings'),
            ),
          ],
        ),
      ),
    );
  }
}
