# Manuel Utilisateur - Echeck-in Event

## Vue d'ensemble

Echeck-in Event est un système complet de gestion d'événements avec check-in via QR Code. Il se compose de :
- Une interface web pour les organisateurs
- Une application mobile pour les agents de check-in

## Interface Web - Organisateurs

### 1. Connexion et inscription

#### Première utilisation
1. Accéder à l'URL de l'application
2. Cliquer sur "Create Account"
3. Remplir le formulaire d'inscription
4. Le premier utilisateur devient automatiquement administrateur

#### Connexion
1. Saisir votre email et mot de passe
2. Cliquer sur "Sign In"

### 2. Tableau de bord

Le tableau de bord affiche :
- **Statistiques générales** : nombre total d'événements, événements actifs, participants, check-ins
- **Événements récents** : liste des 5 derniers événements créés
- **Actions rapides** : création d'événement, accès aux participants

### 3. Gestion des événements

#### Créer un événement
1. Cliquer sur "New Event" depuis le tableau de bord ou la page événements
2. Remplir les informations :
   - **Titre** : nom de l'événement (obligatoire)
   - **Description** : détails de l'événement (optionnel)
   - **Date de début** : date et heure de début (obligatoire)
   - **Date de fin** : date et heure de fin (optionnel)
   - **Lieu** : adresse ou nom du lieu (obligatoire)
   - **Statut** : Draft, Active, Completed, Cancelled
3. Cliquer sur "Create Event"

#### Modifier un événement
1. Accéder à la liste des événements
2. Cliquer sur l'icône "Edit" (crayon)
3. Modifier les informations souhaitées
4. Cliquer sur "Update Event"

#### Supprimer un événement
1. Accéder aux détails de l'événement
2. Cliquer sur le menu "..." puis "Delete Event"
3. Confirmer la suppression

### 4. Gestion des participants

#### Ajouter un participant manuellement
1. Accéder à l'événement
2. Cliquer sur "Manage Participants"
3. Cliquer sur "Add Participant"
4. Remplir le formulaire :
   - **Prénom** et **Nom** (obligatoires)
   - **Email** (obligatoire)
   - **Téléphone** (optionnel)
   - **Entreprise** (optionnel)
   - **Poste** (optionnel)
5. Cocher "Send invitation email" pour envoyer l'invitation automatiquement
6. Cliquer sur "Add Participant"

#### Importer des participants via CSV
1. Accéder à la gestion des participants
2. Cliquer sur "Import CSV"
3. Télécharger le modèle CSV si nécessaire
4. Préparer votre fichier CSV avec les colonnes :
   - `firstName` (obligatoire)
   - `lastName` (obligatoire)
   - `email` (obligatoire)
   - `phone` (optionnel)
   - `company` (optionnel)
   - `position` (optionnel)
5. Sélectionner votre fichier CSV
6. Cliquer sur "Import Participants"

#### Envoyer des invitations
1. **Invitation individuelle** : cocher "Send invitation" lors de l'ajout
2. **Invitations groupées** : cliquer sur "Send Invitations" dans la liste des participants

### 5. Suivi des check-ins

#### Visualiser les statistiques
- **Page événement** : taux de participation, nombre de check-ins
- **Liste des participants** : statut de chaque participant
- **Historique** : liste des check-ins récents avec horodatage

#### Exporter les données
1. Accéder aux détails de l'événement
2. Cliquer sur "Export Data"
3. Le fichier Excel sera téléchargé automatiquement

### 6. QR Codes

Chaque participant reçoit un QR Code unique :
- **Génération automatique** lors de l'ajout du participant
- **Envoi par email** avec l'invitation
- **Visualisation** depuis la liste des participants
- **Sécurité** : codes non falsifiables et uniques

## Application Mobile - Agents

### 1. Installation et connexion

#### Installation
1. Télécharger l'APK ou installer depuis le store
2. Autoriser l'accès à la caméra
3. Lancer l'application

#### Connexion
1. Saisir les identifiants fournis par l'organisateur
2. Cliquer sur "Login"

### 2. Interface principale

L'écran d'accueil affiche :
- **Informations utilisateur** : nom, statut (admin/agent)
- **Actions rapides** :
  - Scan QR Code
  - Historique des scans
  - Statistiques
  - Paramètres

### 3. Scanner QR Code

#### Processus de scan
1. Cliquer sur "Scan QR Code"
2. Pointer la caméra vers le QR Code du participant
3. Le scan se fait automatiquement
4. Vérifier les informations du participant
5. Ajouter des notes si nécessaire (optionnel)
6. Confirmer le check-in

#### Gestion des erreurs
- **QR Code invalide** : message d'erreur avec possibilité de réessayer
- **Participant déjà enregistré** : affichage des informations avec statut
- **Problème réseau** : message d'erreur et suggestion de réessayer

### 4. Fonctionnalités avancées

#### Scan hors ligne
- Les scans sont mis en cache localement
- Synchronisation automatique lors de la reconnexion
- Indicateur de statut de synchronisation

#### Historique
- Liste des derniers check-ins effectués
- Recherche par nom ou email
- Filtrage par date

## Flux de travail type

### Préparation de l'événement
1. **Organisateur** : créer l'événement dans l'interface web
2. **Organisateur** : ajouter les participants (manuel ou CSV)
3. **Organisateur** : envoyer les invitations avec QR Codes
4. **Organisateur** : configurer les comptes agents pour le check-in

### Jour de l'événement
1. **Agents** : se connecter à l'application mobile
2. **Participants** : présenter leur QR Code (email ou impression)
3. **Agents** : scanner les QR Codes pour enregistrer les présences
4. **Organisateur** : suivre les statistiques en temps réel

### Après l'événement
1. **Organisateur** : exporter les données de présence
2. **Organisateur** : analyser les statistiques de participation
3. **Organisateur** : archiver ou supprimer l'événement

## Conseils d'utilisation

### Bonnes pratiques
- **Tester le système** avant l'événement avec quelques participants test
- **Former les agents** à l'utilisation de l'application mobile
- **Prévoir une connexion internet stable** pour les check-ins
- **Imprimer quelques QR Codes de secours** en cas de problème mobile
- **Vérifier les emails d'invitation** avant l'envoi massif

### Optimisation des performances
- **Limiter le nombre d'agents simultanés** selon la capacité du serveur
- **Utiliser le mode hors ligne** en cas de connexion instable
- **Synchroniser régulièrement** les données mobiles

### Sécurité
- **Changer les mots de passe par défaut**
- **Utiliser HTTPS en production**
- **Limiter l'accès aux comptes agents**
- **Sauvegarder régulièrement** la base de données

## Dépannage

### Problèmes courants

#### Interface web
- **Connexion impossible** : vérifier email/mot de passe, contacter l'administrateur
- **Emails non reçus** : vérifier la configuration SMTP, dossier spam
- **Import CSV échoue** : vérifier le format du fichier, colonnes obligatoires

#### Application mobile
- **Scan ne fonctionne pas** : vérifier permissions caméra, éclairage
- **Connexion API échoue** : vérifier connexion internet, URL du serveur
- **Participant non trouvé** : vérifier validité du QR Code, synchronisation

### Support technique
En cas de problème persistant :
1. Noter le message d'erreur exact
2. Vérifier les logs système
3. Tester avec un autre appareil/navigateur
4. Contacter l'équipe technique avec les détails