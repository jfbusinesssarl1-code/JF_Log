# Guide: Création et Gestion des Utilisateurs Stock Manager

## Vue d'ensemble

Le rôle **Stock Manager** (Gestionnaire de Stock) est destiné aux utilisateurs qui gèrent **exclusivement** le module Stock. Ce rôle est créé et géré par le **Gestionnaire** (Assistant comptable) via le panneau d'administration.

## Caractéristiques du rôle Stock Manager

### ✅ Permissions
- **Accès au Stock** : Accès complet et exclusif
- **Enregistrer operation** : Ajouter de nouvelles opérations de stock (entrée/sortie)
- **Modifier opération** : Modifier une opération de stock existante
- **Supprimer opération** : Supprimer une opération de stock
- **Exporter le rapport** : Exporter les rapports de stock en PDF/HTML

### ❌ Restrictions
- **Dashboard** : Pas d'accès
- **Journal** : Pas d'accès
- **Balance** : Pas d'accès
- **Grand Livre** : Pas d'accès
- **Relevé** : Pas d'accès
- **Gestion Utilisateurs** : Pas d'accès

## Procédure: Créer un nouvel utilisateur Stock Manager

### Étape 1: Accéder au panneau d'administration
1. Connectez-vous avec votre compte **Gestionnaire** (Assistant comptable)
2. Cliquez sur **Administration** dans le menu principal
3. Sélectionnez l'onglet **Utilisateurs**

### Étape 2: Remplir le formulaire de création

Dans la section "Créer un nouvel utilisateur", remplissez les champs suivants:

| Champ | Description | Exemple |
|-------|-------------|---------|
| **Nom d'utilisateur** | Identifiant unique (3-32 caractères, lettres/chiffres/_ uniquement) | `stock_user1`, `magasinier01` |
| **Mot de passe** | Minimum 6 caractères, à communiquer de manière sécurisée | `Secure#Pass123` |
| **Rôle** | Sélectionnez **📦 Gestionnaire Stock** | Gestionnaire Stock |

### Étape 3: Enregistrer et communiquer les identifiants

1. Cliquez sur le bouton **Créer**
2. Un message de confirmation apparaîtra
3. Communiquez les identifiants à l'utilisateur de manière sécurisée:
   ```
   Nom d'utilisateur: [username]
   Mot de passe: [password]
   Lien d'accès: [URL de l'application]
   ```

## Exemple d'utilisation

### Exemple 1: Créer un gestionnaire de stock principal
```
Nom d'utilisateur: magasin_principal
Mot de passe: MagStock@2025
Rôle: Gestionnaire Stock
```

Après connexion, cet utilisateur pourra:
- Consulter la liste du stock
- Ajouter des opérations (entrée, sortie)
- Modifier les opérations existantes
- Supprimer les opérations
- Exporter les rapports

### Exemple 2: Créer plusieurs gestionnaires par lieu
```
Utilisateur 1:
  Nom d'utilisateur: stock_dakar
  Rôle: Gestionnaire Stock

Utilisateur 2:
  Nom d'utilisateur: stock_thies
  Rôle: Gestionnaire Stock
```

## Gestion des utilisateurs Stock Manager

### Modifier le rôle d'un utilisateur

1. Dans le panneau Administration → Utilisateurs
2. Cliquez sur le bouton **Modifier** pour l'utilisateur
3. Changez le **Rôle** selon les besoins
4. Enregistrez les modifications

### Réinitialiser le mot de passe

1. Dans le panneau Administration → Utilisateurs
2. Cliquez sur **Modifier** pour l'utilisateur
3. Entrez un nouveau mot de passe
4. Enregistrez les modifications

### Supprimer un utilisateur

1. Dans le panneau Administration → Utilisateurs
2. Cliquez sur le bouton **Supprimer** (icône corbeille)
3. Confirmez la suppression

**⚠️ Attention**: La suppression est permanente et ne peut pas être annulée.

## Flux d'accès du Stock Manager

```
Login (nom d'utilisateur + mot de passe)
    ↓
Dashboard (REFUSÉ - redirection)
    ↓
Page Stock (AUTORISÉ)
    ├── Consulter les opérations
    ├── Ajouter une opération
    ├── Modifier une opération
    ├── Supprimer une opération
    └── Exporter en PDF/HTML
```

## Bonnes pratiques

### Sécurité
- ✅ Changez `stock` en quelque chose de plus spécifique (`stock_dakar`, `magasin_01`)
- ✅ Utilisez des mots de passe robustes (min 6 caractères)
- ✅ Communicatiez les identifiants via un canal sécurisé
- ❌ N'utilisez pas les codes par défaut
- ❌ Ne partagez pas les identifiants par email en clair

### Gestion des accès
- ✅ Créez un utilisateur par personne
- ✅ Documentez qui a accès au stock
- ✅ Supprimez les comptes des anciens employés
- ✅ Examinez régulièrement les accès

## Intégration avec les autres rôles

| Rôle | Peut gérer Stock Manager? | Peut créer Stock Manager? |
|------|--------------------------|--------------------------|
| Admin | ✅ Oui | ✅ Oui |
| Manager (Assistant comptable) | ✅ Oui | ✅ Oui |
| Accountant | ❌ Non | ❌ Non |
| Caissier | ❌ Non | ❌ Non |
| Stock Manager | ❌ Non | ❌ Non |
| User | ❌ Non | ❌ Non |

## Résolution des problèmes

### "L'utilisateur n'accède qu'au stock" - ✅ C'est normal
Le Stock Manager n'a accès que au module Stock par conception.

### "Je ne vois pas l'option 'Gestionnaire Stock'" - Vérifiez:
1. Êtes-vous connecté avec un compte **Manager** ou **Admin**?
2. Vous êtes dans l'onglet **Utilisateurs** de l'Administration?
3. L'application a-t-elle été mise à jour correctement?

### "Comment fonctionne l'export?" 
Les utilisateurs Stock Manager peuvent exporter en:
- **PDF** (si mpdf est installé)
- **HTML** (toujours disponible)

## Support

Pour toute question ou problème, veuillez contacter votre administrateur système.

---
**Dernière mise à jour**: Mars 2025
**Version du système**: 1.0+stock_manager
