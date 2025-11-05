# Documentation API - Echeck-in Event

## Vue d'ensemble

L'API REST de Echeck-in Event utilise :
- **Authentification** : JWT (JSON Web Tokens)
- **Format** : JSON
- **Base URL** : `http://localhost:8000/api`
- **Versioning** : v1 (implicite)

## Authentification

### Obtenir un token JWT

**POST** `/api/login_check`

```json
{
  "username": "user@example.com",
  "password": "password123"
}
```

**Réponse :**
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."
}
```

### Utilisation du token

Inclure le token dans l'en-tête de chaque requête :
```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...
```

## Endpoints

### Authentification

#### Inscription
**POST** `/api/register`

```json
{
  "email": "user@example.com",
  "password": "password123",
  "firstName": "John",
  "lastName": "Doe"
}
```

**Réponse :**
```json
{
  "message": "User created successfully",
  "user": {
    "id": 1,
    "email": "user@example.com",
    "firstName": "John",
    "lastName": "Doe",
    "roles": ["ROLE_USER"],
    "createdAt": "2024-01-01T10:00:00+00:00"
  }
}
```

#### Profil utilisateur
**GET** `/api/profile`

**Réponse :**
```json
{
  "user": {
    "id": 1,
    "email": "user@example.com",
    "firstName": "John",
    "lastName": "Doe",
    "roles": ["ROLE_USER"],
    "createdAt": "2024-01-01T10:00:00+00:00"
  }
}
```

#### Mise à jour du profil
**PUT** `/api/profile`

```json
{
  "firstName": "John",
  "lastName": "Smith",
  "email": "john.smith@example.com"
}
```

### Événements

#### Lister les événements
**GET** `/api/events`

**Réponse :**
```json
{
  "events": [
    {
      "id": 1,
      "title": "Conférence Tech 2024",
      "description": "Événement technologique annuel",
      "startDate": "2024-06-15T09:00:00+00:00",
      "endDate": "2024-06-15T18:00:00+00:00",
      "location": "Centre de congrès",
      "status": "active",
      "createdAt": "2024-01-01T10:00:00+00:00",
      "organizer": {
        "id": 1,
        "firstName": "John",
        "lastName": "Doe"
      }
    }
  ]
}
```

#### Créer un événement
**POST** `/api/events`

```json
{
  "title": "Conférence Tech 2024",
  "description": "Événement technologique annuel",
  "startDate": "2024-06-15T09:00:00+00:00",
  "endDate": "2024-06-15T18:00:00+00:00",
  "location": "Centre de congrès"
}
```

#### Détails d'un événement
**GET** `/api/events/{id}`

#### Modifier un événement
**PUT** `/api/events/{id}`

#### Supprimer un événement
**DELETE** `/api/events/{id}`

#### Statistiques d'un événement
**GET** `/api/events/{id}/statistics`

**Réponse :**
```json
{
  "statistics": {
    "totalParticipants": 150,
    "checkedInParticipants": 120,
    "attendanceRate": 80.0,
    "invitationsSent": 150,
    "confirmedInvitations": 135
  }
}
```

### Participants

#### Lister les participants d'un événement
**GET** `/api/events/{eventId}/participants`

**Réponse :**
```json
{
  "participants": [
    {
      "id": 1,
      "firstName": "Alice",
      "lastName": "Martin",
      "email": "alice.martin@example.com",
      "phone": "+33123456789",
      "company": "TechCorp",
      "position": "Developer",
      "qrCode": "QR_abc123_def456",
      "status": "confirmed",
      "createdAt": "2024-01-01T10:00:00+00:00"
    }
  ]
}
```

#### Ajouter un participant
**POST** `/api/events/{eventId}/participants`

```json
{
  "firstName": "Alice",
  "lastName": "Martin",
  "email": "alice.martin@example.com",
  "phone": "+33123456789",
  "company": "TechCorp",
  "position": "Developer",
  "sendInvitation": true
}
```

#### Détails d'un participant
**GET** `/api/events/{eventId}/participants/{id}`

#### Modifier un participant
**PUT** `/api/events/{eventId}/participants/{id}`

#### Supprimer un participant
**DELETE** `/api/events/{eventId}/participants/{id}`

#### Import CSV
**POST** `/api/events/{eventId}/participants/import`

**Content-Type:** `multipart/form-data`

**Paramètres :**
- `csv_file` : fichier CSV

**Réponse :**
```json
{
  "message": "CSV import completed",
  "imported": 45,
  "skipped": 5,
  "errors": [
    "Row 12: Invalid email format",
    "Row 25: Missing required field"
  ]
}
```

#### Envoyer les invitations
**POST** `/api/events/{eventId}/participants/send-invitations`

**Réponse :**
```json
{
  "message": "Invitations sent to 45 participants"
}
```

### Check-in

#### Vérifier un QR Code
**GET** `/api/checkin/verify/{qrCode}`

**Réponse :**
```json
{
  "valid": true,
  "participant": {
    "id": 1,
    "firstName": "Alice",
    "lastName": "Martin",
    "email": "alice.martin@example.com",
    "company": "TechCorp",
    "position": "Developer",
    "status": "confirmed"
  },
  "event": {
    "id": 1,
    "title": "Conférence Tech 2024",
    "startDate": "2024-06-15T09:00:00+00:00",
    "location": "Centre de congrès"
  },
  "alreadyCheckedIn": false
}
```

#### Effectuer un check-in
**POST** `/api/checkin/{qrCode}`

```json
{
  "checkedInBy": "Agent Mobile",
  "notes": "Arrivé en retard mais présent"
}
```

**Réponse :**
```json
{
  "message": "Check-in successful",
  "participant": {
    "id": 1,
    "firstName": "Alice",
    "lastName": "Martin",
    "status": "checked_in"
  },
  "checkIn": {
    "id": 1,
    "checkedInAt": "2024-06-15T09:15:00+00:00",
    "checkedInBy": "Agent Mobile",
    "notes": "Arrivé en retard mais présent"
  }
}
```

## Codes de statut HTTP

### Succès
- **200 OK** : Requête réussie
- **201 Created** : Ressource créée avec succès
- **204 No Content** : Suppression réussie

### Erreurs client
- **400 Bad Request** : Données invalides
- **401 Unauthorized** : Token manquant ou invalide
- **403 Forbidden** : Accès refusé
- **404 Not Found** : Ressource non trouvée
- **409 Conflict** : Conflit (ex: participant déjà enregistré)
- **422 Unprocessable Entity** : Erreurs de validation

### Erreurs serveur
- **500 Internal Server Error** : Erreur serveur

## Gestion des erreurs

### Format des erreurs
```json
{
  "error": "Description de l'erreur",
  "code": "ERROR_CODE",
  "details": {
    "field": "Message d'erreur spécifique"
  }
}
```

### Erreurs de validation
```json
{
  "errors": "email: This value is not a valid email address.\nfirstName: This value should not be blank."
}
```

## Pagination

Pour les listes importantes, utiliser les paramètres :
- `page` : numéro de page (défaut: 1)
- `limit` : nombre d'éléments par page (défaut: 20, max: 100)

**Exemple :**
```
GET /api/events?page=2&limit=10
```

**Réponse avec métadonnées :**
```json
{
  "events": [...],
  "pagination": {
    "page": 2,
    "limit": 10,
    "total": 150,
    "pages": 15
  }
}
```

## Filtrage et recherche

### Événements
- `status` : filtrer par statut (draft, active, completed, cancelled)
- `search` : recherche dans titre et description

**Exemple :**
```
GET /api/events?status=active&search=tech
```

### Participants
- `status` : filtrer par statut (pending, invited, confirmed, checked_in)
- `search` : recherche dans nom, prénom, email

**Exemple :**
```
GET /api/events/1/participants?status=confirmed&search=martin
```

## Rate Limiting

- **Limite générale** : 1000 requêtes/heure par IP
- **Authentification** : 10 tentatives/minute par IP
- **Check-in** : 100 requêtes/minute par token

**En-têtes de réponse :**
```
X-RateLimit-Limit: 1000
X-RateLimit-Remaining: 999
X-RateLimit-Reset: 1640995200
```

## Webhooks (optionnel)

Configuration possible pour recevoir des notifications :
- Nouveau check-in
- Participant ajouté
- Événement modifié

**Format :**
```json
{
  "event": "participant.checked_in",
  "data": {
    "participant": {...},
    "checkIn": {...},
    "event": {...}
  },
  "timestamp": "2024-06-15T09:15:00+00:00"
}
```

## Exemples d'utilisation

### JavaScript (Fetch)
```javascript
const response = await fetch('http://localhost:8000/api/events', {
  headers: {
    'Authorization': 'Bearer ' + token,
    'Content-Type': 'application/json'
  }
});
const data = await response.json();
```

### cURL
```bash
curl -X GET \
  http://localhost:8000/api/events \
  -H 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...' \
  -H 'Content-Type: application/json'
```

### Flutter/Dart
```dart
final response = await http.get(
  Uri.parse('http://localhost:8000/api/events'),
  headers: {
    'Authorization': 'Bearer $token',
    'Content-Type': 'application/json',
  },
);
```