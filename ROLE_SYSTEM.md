# Système de Rôles et Contrôle d'Accès

## Vue d'ensemble
Le système utilise un contrôle d'accès basé sur les rôles (RBAC) pour sécuriser les accès aux différents services.

## Rôles disponibles

### 1. **admin** (Administrateur)
- ✅ Accès complet à tous les services
- ✅ Création et gestion des utilisateurs
- ✅ Journal, Balance, Grand Livre, Stock, Relevé
- ✅ Dashboard

### 2. **manager** (Gestionnaire)
- ✅ Stock (gestion complète)
- ✅ Journal, Balance, Grand Livre (lecture/écriture)
- ✅ Relevé
- ✅ Dashboard
- ❌ Gestion utilisateurs

### 3. **accountant** (Comptable)
- ✅ Journal, Balance, Grand Livre (lecture/écriture)
- ✅ Relevé
- ✅ Dashboard
- ❌ Stock
- ❌ Gestion utilisateurs

### 4. **stock_manager** (Gestionnaire de Stock)
- ✅ Stock (gestion complète: ajouter, modifier, supprimer, exporter)
- ❌ Accès exclusif au Stock uniquement
- ❌ Journal, Balance, Grand Livre, Relevé, Dashboard, Gestion utilisateurs

### 5. **user** (Utilisateur)
- ✅ Dashboard (accès limité)
- ❌ Tous les autres services

## Implémentation technique

### Middleware de contrôle d'accès
Le middleware se trouve dans `Controller.php` :

```php
protected function requireRole($allowedRoles = []) {
    // Vérifie l'authentification et le rôle
}

protected function requireAdmin() {
    // Restriction admin uniquement
}
```

### Utilisation dans les Controllers

```php
public function index() {
    $this->requireRole(['accountant', 'manager', 'admin']);
    // Code du service
}
```

## Contrôle d'accès par service

| Service | Rôles autorisés |
|---------|-----------------|
| Journal | accountant, manager, admin |
| Balance | accountant, manager, admin |
| Grand Livre | accountant, manager, admin |
| Stock | manager, admin, stock_manager |
| Relevé | accountant, manager, admin |
| Dashboard | Tous (filtré par rôle) |
| Gestion Utilisateurs | admin, manager |

## Base de données

Structure utilisateur dans MongoDB :
```json
{
  "_id": ObjectId,
  "username": "string",
  "password": "hashed_password",
  "role": "admin|manager|accountant|user",
  "created_at": "2025-12-17 HH:MM:SS"
}
```

## Authentification et session

L'utilisateur connecté est stocké en session :
```php
$_SESSION['user'] = [
    '_id' => ObjectId,
    'username' => 'jdoe',
    'role' => 'manager'
];
```

## Initialisation du système

Pour créer le premier administrateur, utiliser le script d'initialisation ou créer directement en MongoDB :

```php
$userModel = new UserModel();
$userModel->create('admin', 'password123', 'admin');
```

## Sécurité

- ✅ Les mots de passe sont hachés avec `PASSWORD_DEFAULT`
- ✅ Les rôles sont vérifiés côté serveur (pas côté client)
- ✅ Redirection automatique vers le dashboard en cas d'accès non autorisé
- ✅ Les utilisateurs ne peuvent pas accéder directement à des services sans droits
