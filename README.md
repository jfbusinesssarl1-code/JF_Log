# Template PHP Comptabilité (MVC)

Ce projet est une base pour une application de gestion comptable en PHP, utilisant une structure MVC, Bootstrap pour le design, et MongoDB pour la base de données.

## 🚀 Démarrage rapide

### 1. Installation des dépendances
```bash
composer install
```

### 2. Configuration MongoDB
Assurez-vous que MongoDB est configuré dans `app/core/Database.php` :
```php
const MONGO_URI = 'mongodb://localhost:27017';
const DB_NAME = 'comptabilite';
```

### 3. Initialisation du système
Exécutez le script d'initialisation pour créer le compte administrateur :
```bash
php init.php
```

Cela créera :
- **Nom d'utilisateur**: admin
- **Mot de passe**: admin123
- **Rôle**: admin

### 4. Lancer le serveur
```bash
php -S localhost:8000 -t public
```

Accédez à : http://localhost:8000

### 5. Se connecter
1. Utilisez les identifiants créés par `init.php`
2. Changez votre mot de passe immédiatement
3. Allez dans "Gestion Utilisateurs" (⚙️ menu admin) pour créer d'autres comptes

## 📋 Rôles et permissions

| Rôle | Permissions |
|------|------------|
| **admin** | ✅ Accès complet + gestion des utilisateurs |
| **manager** | ✅ Stock, Journal, Balance, Grand Livre |
| **accountant** | ✅ Journal, Balance, Grand Livre, Relevé |
| **user** | ✅ Dashboard uniquement |

## 📁 Structure
- `app/core` : Classes de base (Controller, Model, Database)
- `app/controllers` : Contrôleurs pour chaque page
- `app/models` : Modèles pour la gestion des données
- `app/views` : Vues Bootstrap pour login, journal, grand-livre, balance
- `public` : Point d'entrée (`index.php`)

## 🔐 Système de sécurité

- **Hachage des mots de passe** : PASSWORD_DEFAULT (bcrypt)
- **Token CSRF** : Protection contre les attaques CSRF
- **Contrôle d'accès par rôle (RBAC)** : Chaque service est protégé selon les rôles
- **Session sécurisée** : Les utilisateurs sont authentifiés via session PHP

## 📝 Fonctionnalités

- 📔 **Journal Comptable** : Gestion des écritures (partie double)
- 📊 **Grand Livre** : Consultation des comptes
- ⚖️ **Balance** : Vérification des débits/crédits
- 📦 **Fiche de Stock** : Gestion des inventaires
- 📋 **Relevé** : Consultation des opérations filtrées
- �️ **Exports** : Tous les exports sont désormais au format **PDF**. Pour des exports serveur fiables, installez `mpdf/mpdf` :
  ```bash
  composer require mpdf/mpdf
  ```
- �🔒 **Gestion des utilisateurs** : Création et attribution de rôles (admin only)

## 🆘 Dépannage

### Les identifiants ne sont pas reconnus
1. Vérifiez que MongoDB est en cours d'exécution
2. Exécutez `php init.php` pour créer le compte admin
3. Consultez les logs d'erreur dans la console

### Problèmes de connexion à MongoDB
Vérifiez la configuration dans `app/core/Database.php` et assurez-vous que le serveur MongoDB est actif.

## 📚 Documentation complète
Voir [ROLE_SYSTEM.md](ROLE_SYSTEM.md) pour la documentation du système de rôles.


## Installation
1. Installer les dépendances avec Composer :
   ```bash
   composer install
   ```
2. Configurer MongoDB (par défaut : `mongodb://localhost:27017`, base : `compta`)
3. Accéder à l'application via `public/index.php`

## Pages
- **Login** : Authentification utilisateur
- **Journal** : Liste des écritures comptables
- **Grand-Livre** : Détail par compte
- **Balance** : Synthèse des soldes

## Personnalisation
Ajoutez vos modèles et contrôleurs pour la logique métier et la connexion réelle à MongoDB.
