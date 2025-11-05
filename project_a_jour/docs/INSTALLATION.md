# Installation Guide - Echeck-in Event

## Prérequis système

### Backend (Symfony)
- PHP 8.2.0 ou supérieur
- Composer 2.x
- MySQL 8.0.31
- Serveur web (Apache/Nginx) ou Symfony CLI

### Application mobile (Flutter)
- Flutter 3.22.0
- Dart 3.4.0
- Android Studio / VS Code
- SDK Android (pour Android)
- Xcode (pour iOS, macOS uniquement)

## Installation Backend

### 1. Cloner le projet
```bash
git clone <repository-url>
cd echeck-in-event/backend
```

### 2. Installer les dépendances
```bash
composer install
```

### 3. Configuration de l'environnement
```bash
cp .env.example .env
```

Modifier le fichier `.env` avec vos paramètres :
```env
# Database
DATABASE_URL="mysql://username:password@127.0.0.1:3306/echeck_in?serverVersion=8.0.31&charset=utf8mb4"

# JWT Configuration
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=your_jwt_passphrase

# Mailer Configuration
MAILER_DSN=smtp://username:password@smtp.example.com:587

# Frontend URL for CORS
FRONTEND_URL=http://localhost:3000

# Application URL for QR codes
APP_URL=http://localhost:8000
```

### 4. Créer la base de données
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 5. Générer les clés JWT
```bash
php bin/console lexik:jwt:generate-keypair
```

### 6. Charger les données de test (optionnel)
```bash
php bin/console doctrine:fixtures:load
```

### 7. Démarrer le serveur
```bash
# Avec Symfony CLI (recommandé)
symfony server:start

# Ou avec le serveur PHP intégré
php -S localhost:8000 -t public/
```

## Installation Application Mobile

### 1. Naviguer vers le dossier mobile
```bash
cd ../mobile-app
```

### 2. Installer les dépendances Flutter
```bash
flutter pub get
```

### 3. Configuration de l'API
Modifier le fichier `lib/services/api_service.dart` :
```dart
static const String baseUrl = 'http://your-server-url:8000';
```

### 4. Permissions Android
Le fichier `android/app/src/main/AndroidManifest.xml` doit contenir :
```xml
<uses-permission android:name="android.permission.CAMERA" />
<uses-permission android:name="android.permission.INTERNET" />
```

### 5. Lancer l'application
```bash
# Vérifier les appareils connectés
flutter devices

# Lancer sur un appareil/émulateur
flutter run
```

## Configuration du serveur web

### Apache
```apache
<VirtualHost *:80>
    ServerName echeck-in.local
    DocumentRoot /path/to/echeck-in-event/backend/public
    
    <Directory /path/to/echeck-in-event/backend/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/echeck-in_error.log
    CustomLog ${APACHE_LOG_DIR}/echeck-in_access.log combined
</VirtualHost>
```

### Nginx
```nginx
server {
    listen 80;
    server_name echeck-in.local;
    root /path/to/echeck-in-event/backend/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }

    location ~ ^/index\.php(/|$) {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
    }

    location ~ \.php$ {
        return 404;
    }
}
```

## Configuration SMTP

### Gmail
```env
MAILER_DSN=gmail://username:password@default
```

### Mailgun
```env
MAILER_DSN=mailgun://key:domain@default?region=us
```

### SMTP générique
```env
MAILER_DSN=smtp://username:password@smtp.example.com:587
```

## Vérification de l'installation

### Backend
1. Accéder à `http://localhost:8000`
2. Créer un compte administrateur
3. Tester la création d'un événement
4. Vérifier l'envoi d'emails

### Application mobile
1. Lancer l'application
2. Se connecter avec les identifiants créés
3. Tester le scan QR code
4. Vérifier la communication avec l'API

## Dépannage

### Erreurs communes

**Erreur JWT**
```bash
php bin/console lexik:jwt:generate-keypair --overwrite
```

**Erreur de permissions**
```bash
chmod -R 755 var/
chown -R www-data:www-data var/
```

**Erreur de base de données**
```bash
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

**Erreur Flutter**
```bash
flutter clean
flutter pub get
flutter doctor
```

## Logs

### Symfony
- Logs d'application : `var/log/`
- Logs de développement : `var/log/dev.log`
- Logs de production : `var/log/prod.log`

### Flutter
```bash
flutter logs
```

## Support

Pour toute assistance technique :
1. Vérifier les logs d'erreur
2. Consulter la documentation Symfony/Flutter
3. Vérifier la configuration des services
4. Tester la connectivité réseau