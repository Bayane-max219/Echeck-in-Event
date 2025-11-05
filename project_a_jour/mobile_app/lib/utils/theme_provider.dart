import 'package:flutter/material.dart';

class ThemeProvider extends ChangeNotifier {
  ThemeMode _themeMode = ThemeMode.system;
  MaterialColor _mainColor = Colors.blue;

  ThemeMode get themeMode => _themeMode;
  MaterialColor get mainColor => _mainColor;

  ThemeData get lightTheme => ThemeData(
        useMaterial3: true,
        colorScheme: ColorScheme.fromSeed(
          seedColor: _mainColor,
          brightness: Brightness.light,
        ),
        primaryColor: _mainColor,
      );

  ThemeData get darkTheme => ThemeData(
        useMaterial3: true,
        colorScheme: ColorScheme.fromSeed(
          seedColor: _mainColor,
          brightness: Brightness.dark,
        ),
        primaryColor: _mainColor,
      );

  void setThemeMode(ThemeMode mode) {
    _themeMode = mode;
    notifyListeners();
  }

  void setMainColor(MaterialColor color) {
    _mainColor = color;
    notifyListeners();
  }
}

