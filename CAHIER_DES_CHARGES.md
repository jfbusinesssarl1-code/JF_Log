# CAHIER DES CHARGES
## Système de Gestion Comptable et Logistique Intégré

**Date de création** : Avril 2026  
**Version** : 1.0  
**Statut** : À valider par le Maître d'Ouvrage

---

## 1. CONTEXTE ET OBJECTIFS

### 1.1 Contexte du projet
Le projet vise à mettre en place une solution complète de gestion comptable et logistique pour l'entreprise. Cette application web doit centraliser tous les processus de gestion financière, de gestion de stock, de paie, ainsi que le suivi des opérations commerciales.

### 1.2 Objectifs généraux
- ✅ Automatiser et centraliser la gestion comptable
- ✅ Assurer un suivi en temps réel des stocks et des inventaires
- ✅ Gérer l'administration de la paie et des présences du personnel ouvrier
- ✅ Faciliter les exports et la génération de rapports (PDF, HTML)
- ✅ Sécuriser l'accès aux données sensibles via un système de rôles
- ✅ Assurer la conformité auditable des opérations comptables

### 1.3 Périmètre fonctionnel
L'application couvre :
- **Comptabilité** : Journal, Balance, Grand Livre
- **Logistique** : Gestion de stocks et inventaires
- **Ressources Humaines** : Gestion de paie et présences (ouvriers journaliers)
- **Administration** : Gestion des utilisateurs, rôles et permissions
- **Reporting** : Génération d'états et d'exports

---

## 2. DESCRIPTION GÉNÉRALE DU SYSTÈME

### 2.1 Vision fonctionnelle
Le système est une **application web intranet** permettant la gestion intégrée des opérations de l'entreprise. Elle offre une interface conviviale basée sur Bootstrap, sécurisée par authentification et autorisation par rôles.

### 2.2 Architecture générale
```
Interface Web (Bootstrap)
    ↓
Contrôleurs d'application (PHP MVC)
    ↓
Modèles métier (MongoDB)
    ↓
Base de données (MongoDB)
```

### 2.3 Accès et déploiement
- **Type** : Application web en accès intranet
- **Accès** : http://[serveur]:[port]
- **Navigateurs supportés** : Chrome, Firefox, Edge, Safari (versions récentes)
- **Authentification** : Identifiants uniques par utilisateur
- **Sécurité** : HTTPS fortement recommandée en production

---

## 3. MODULES FONCTIONNELS

### 3.1 MODULE COMPTABILITÉ

#### 3.1.1 Journal Comptable
**Objectif** : Saisie et gestion des écritures comptables selon le principe de la partie double.

**Fonctionnalités** :
- Saisie des écritures (débit/crédit)
- Validation automatique de l'équilibre débit/crédit
- Recherche et filtrage par compte, date, libellé
- Modification et suppression d'écritures (avec traçabilité)
- Export en PDF et HTML

**Rôles autorisés** : Comptables, Gestionnaires, Administrateurs

#### 3.1.2 Grand Livre
**Objectif** : Consultation détaillée de tous les comptes et leurs opérations.

**Fonctionnalités** :
- Visualisation de tous les comptes avec détails des écritures
- Soldes et cumuls par période
- Filtrage par compte, date
- Export en PDF/HTML
- Impression

**Rôles autorisés** : Comptables, Gestionnaires, Administrateurs

#### 3.1.3 Balance Comptable
**Objectif** : Vérification de l'équilibre comptable et détection d'anomalies.

**Fonctionnalités** :
- Affichage des débits/crédits par compte
- Calcul automatique des soldes
- Vérification de l'équilibre global (Σ débits = Σ crédits)
- Identification des comptes non équilibrés
- Export en PDF

**Rôles autorisés** : Comptables, Gestionnaires, Administrateurs

#### 3.1.4 Relevé
**Objectif** : Consultation des opérations filtrées par critères.

**Fonctionnalités** :
- Filtrage par compte, date, montant, libellé
- Pagination des résultats
- Affichage des soldes cumulés
- Export en PDF/HTML

**Rôles autorisés** : Comptables, Gestionnaires, Administrateurs

---

### 3.2 MODULE LOGISTIQUE

#### 3.2.1 Gestion de Stock
**Objectif** : Suivi complet des stocks et des opérations d'inventaire.

**Fonctionnalités** :
- Enregistrement des articles (référence, description, prix unitaire)
- Opérations de stock :
  - Entrée (achat, transfert)
  - Sortie (vente, consommation, transfert)
  - Inventaire (ajustement)
- Fiche de stock détaillée :
  - Quantités en stock
  - Valeur du stock
  - Mouvements historiques
  - Alertes de stock minimum
- Gestion des catégories d'articles
- Traçabilité des opérations (utilisateur, date, motif)
- Export en PDF et HTML
- Rapport d'inventaire

**Rôles autorisés** : Gestionnaires de stock, Gestionnaires, Administrateurs

---

### 3.3 MODULE RESSOURCES HUMAINES

#### 3.3.1 Gestion des Chantiers
**Objectif** : Organisation et suivi des chantiers de travail.

**Fonctionnalités** :
- Création et gestion des chantiers (sites de travail)
- Attribution des responsables :
  - Ingénieur responsable
  - Magasinier
- Suivi des effectifs par chantier
- Statuts (actif, suspendu, fermé)
- Localisation et contacts

**Rôles autorisés** : Administrateurs

#### 3.3.2 Gestion des Ouvriers
**Objectif** : Gestion du personnel ouvrier des chantiers.

**Fonctionnalités** :
- Enregistrement des ouvriers par chantier
- Classification :
  - T.T (Tout Travaux)
  - M.C (Maçons)
  - Autres catégories configurables
- Gestion du statut (actif, inactif, archivé)
- Historique des affectations
- Archivage sans suppression

**Rôles autorisés** : Administrateurs

#### 3.3.3 Configuration des Salaires
**Objectif** : Définition des tarifs de rémunération.

**Fonctionnalités** :
- Configuration par chantier et catégorie d'ouvrier
- Tarifs journaliers et demi-journaliers (automatiquement calculés à 50%)
- Tarifs par défaut :
  - **T.T** : 3$ par jour (1,5$ demi-journée) - configurable
  - **M.C (Maçons)** : 6$ à 7$ par jour - configurable par chantier
- Modification des tarifs avec historique

**Rôles autorisés** : Administrateurs

#### 3.3.4 Saisie des Présences
**Objectif** : Enregistrement hebdomadaire des présences.

**Fonctionnalités** :
- Saisie par semaine et par chantier
- Champs de présence par jour :
  - 0 = Absent
  - 0,5 = Demi-journée
  - 1 = Journée complète
- Calcul automatique des jours travaillés
- Signature électronique
- Historique des saisies
- Validation avec signature de l'ingénieur

**Rôles autorisés** : Administrateurs

#### 3.3.5 Génération et Gestion des Fiches de Paie
**Objectif** : Génération automatisée des bulletins de paie.

**Fonctionnalités** :
- Génération automatique basée sur les présences saisies
- Calcul autom​atique :
  - Total jours travaillés
  - Montant brut = jours × tarif
  - Déductions (cotisations sociales si applicables)
  - Net à payer
- Trois tableaux sur chaque fiche :
  - Tableau 1 : Données personnelles et d'identification
  - Tableau 2 : Données de paie (détails de calcul)
  - Tableau 3 : Résumé (montants clés)
- Signature de l'ingénieur et magasinier
- Export en PDF
- Archivage numérique
- Édition et rectification

**Rôles autorisés** : Administrateurs

#### 3.3.6 Rapports RH
**Objectif** : Suivi et analyse des données de paie et présences.

**Fonctionnalités** :
- Rapport par chantier (jours travaillés, masse salariale)
- Rapport par ouvrier (historique de paie)
- Statistiques (présence moyenne, coût par chantier)
- Export en PDF/HTML
- Période configurable

**Rôles autorisés** : Administrateurs

---

### 3.4 MODULE ADMINISTRATION

#### 3.4.1 Gestion des Utilisateurs
**Objectif** : Administration des accès et des permissions.

**Fonctionnalités** :
- Création d'utilisateurs
- Attribution de rôles :
  - **Admin** : Accès complet
  - **Manager** : Stock, Comptabilité (lecture/écriture)
  - **Comptable** : Comptabilité uniquement
  - **Gestionnaire de Stock** : Stock uniquement
  - **Utilisateur** : Dashboard limité
- Modification des identifiants
- Réinitialisation des mots de passe
- Désactivation/suppression de comptes
- Historique des actions

**Rôles autorisés** : Administrateurs uniquement

#### 3.4.2 Gestion des Rôles
**Objectif** : Configuration fine du contrôle d'accès.

**Fonctionnalités** :
- 5 rôles prédéfinis
- Matrice des permissions par rôle
- Audit des accès

| Module | Admin | Manager | Comptable | Stock Manager | User |
|--------|:-----:|:-------:|:---------:|:-------------:|:----:|
| Journal | ✅ | ✅ | ✅ | ❌ | ❌ |
| Balance | ✅ | ✅ | ✅ | ❌ | ❌ |
| Grand Livre | ✅ | ✅ | ✅ | ❌ | ❌ |
| Stock | ✅ | ✅ | ❌ | ✅ | ❌ |
| Relevé | ✅ | ✅ | ✅ | ❌ | ❌ |
| Paie | ✅ | ❌ | ❌ | ❌ | ❌ |
| Gestion Utilisateurs | ✅ | ❌ | ❌ | ❌ | ❌ |
| Dashboard | ✅ | ✅ | ✅ | ❌ | ✅ |

---

### 3.5 MODULE DASHBOARD

#### 3.5.1 Tableau de Bord
**Objectif** : Vue synthétique adaptée au rôle de l'utilisateur.

**Fonctionnalités** :
- **Pour Admin** : Statistiques globales (soldes, stocks, paies)
- **Pour Manager** : Stocks et comptabilité
- **Pour Comptable** : Résumés comptables
- **Pour Gestionnaire de Stock** : Stock et mouvements récents
- **Pour Utilisateur** : Vue limitée des éléments pertinents

**Rôles autorisés** : Tous les utilisateurs

---

## 4. FONCTIONNALITÉS TRANSVERSALES

### 4.1 Reporting et Exports
- Export en **PDF** pour tous les états comptables et logistiques
- Export en **HTML** pour consultation web
- Impression directe depuis l'interface
- Horodatage et signataires (où applicable)
- Conformité auditable

### 4.2 Recherche et Filtrage
- Recherche full-text sur comptes, articles, ouvriers
- Filtres multiples (dates, montants, statuts)
- Pagination des résultats
- Tri par colonne

### 4.3 Traçabilité et Audit
- Enregistrement de toutes les opérations
- Identification de l'utilisateur responsable
- Horodatage (date et heure)
- Historique modifiable dans le journal
- Pistes d'audit complètes

### 4.4 Gestion des Erreurs
- Validation des saisies côté client et serveur
- Messages d'erreur explicites
- Logs détaillés en cas de problème
- Mécanismes de récupération

---

## 5. SÉCURITÉ

### 5.1 Authentification
- Authentification par **identifiant/mot de passe**
- Hachage des passwords avec **bcrypt** (PASSWORD_DEFAULT PHP)
- Minimum 6 caractères requis
- Recommandation : forcer un changement au premier accès

### 5.2 Autorisation (RBAC)
- **Role-Based Access Control** (Contrôle d'Access par Rôles)
- Middleware de vérification des rôles dans chaque action
- Impossible de contourner les restrictions de rôle
- Actions admin protégées

### 5.3 Protection des Données
- **Tokens CSRF** : Protection contre les attaques Cross-Site Request Forgery
- **Sessions sécurisées** : Gestion sécurisée des sessions PHP
- **Validation des entrées** : Nettoyage et validation de toutes les saisies
- **Mots de passe** : Jamais stockés en clair
- **Données sensibles** : Chiffrement recommandée en production

### 5.4 HTTPS et Protocole
- **HTTPS obligatoire** en environnement de production
- Certificat SSL/TLS valide
- Pas d'accès HTTP non chiffré aux données sensibles

### 5.5 Conformité
- **Conformité audit** : Traçabilité complète des opérations
- **Principes COSO** : Contrôles internes et prévention de fraude
- Séparation des comptes pour action/vérification
- Équilibre obligatoire des écritures comptables

---

## 6. INFRASTRUCTURE TECHNIQUE

### 6.1 Environnement Requis

#### Serveur
- **Système d'exploitation** : Linux (Ubuntu 20.04+, Debian 10+) ou Windows Server
- **PHP** : Version 7.4 minimum, 8.0+ recommandé
- **Serveur web** : Apache 2.4+ ou Nginx 1.18+
- **Base de données** : MongoDB 4.2+
- **RAM** : Minimum 2GB (4GB recommandé)
- **Disque** : 10GB minimum (extension selon usage)

#### Client
- **Navigateur web** : Chrome, Firefox, Edge, Safari (versions récentes)
- **Connexion** : Intranet stable (LAN)
- **Résolution** : Minimum 1024×768 (optimisé pour 1366×768+)

### 6.2 Stack Technologique
- **Backend** : PHP 7.4+
- **Framework** : Architecture MVC maison
- **Frontend** : Bootstrap 5 + HTML5 + CSS3 + JavaScript
- **Base de données** : MongoDB (NoSQL)
- **Serveur d'application** : PHP intégré (développement) ou Apache/Nginx (production)
- **Gestion des dépendances** : Composer
- **Librairies principales** :
  - `mongodb/mongodb` : Pilote MongoDB
  - `mpdf/mpdf` : Génération de PDF
  - `intervention/image` : Manipulation d'images (si convertisseur d'images utilisé)

### 6.3 Structure des Répertoires
```
/
├── app/                      (Code source)
│   ├── core/                (Classes de base)
│   ├── controllers/          (Contrôleurs)
│   ├── models/              (Modèles métier)
│   └── views/               (Vues HTML)
├── public/                   (Accès web)
│   ├── index.php            (Point d'entrée)
│   ├── uploads/             (Fichiers uploadés)
│   └── assets/              (CSS, JS, images)
├── data/                     (Données de configuration)
├── vendor/                   (Dépendances Composer)
├── scripts/                  (Scripts utilitaires)
└── docs/                     (Documentation)
```

### 6.4 Configuration MongoDB
```php
URI par défaut    : mongodb://localhost:27017
Base de données   : comptabilite
Authentification  : À configurer selon votre infrastructure
Port              : 27017 (standard, modifiable)
```

### 6.5 Performance
- **Temps de réponse** : < 2 secondes pour les pages standards
- **Temps d'export PDF** : < 5 secondes pour 100 écritures
- **Gestion concurrence** : Support de plusieurs utilisateurs simultanés

---

## 7. INSTALLATION ET DÉPLOIEMENT

### 7.1 Installation Initiale

#### Étape 1 : Prérequis
```bash
# Vérifier PHP 7.4+
php -v

# Vérifier MongoDB
mongo --version

# Installer Composer
composer -V
```

#### Étape 2 : Clonage/Déploiement du code
```bash
# Sur le serveur de destination
cd /srv/application
git clone [adresse du repo] .
# ou copier les fichiers directement
```

#### Étape 3 : Installation des dépendances
```bash
composer install
```

#### Étape 4 : Configuration
- Éditer `app/core/Database.php` avec les paramètres MongoDB
- Créer le fichier `.env` si nécessaire
- Configurer les permissions des répertoires

#### Étape 5 : Initialisation
```bash
php init.php
```
Cela crée le compte administrateur initial :
- **Nom d'utilisateur** : `admin`
- **Mot de passe** : `admin123`
- **À changer immédiatement** : IMPORTANT !

#### Étape 6 : Vérification
```bash
# Accéder à http://[serveur]:[port]
# Se connecter avec admin/admin123
# Vérifier l'accès à toutes les sections
```

### 7.2 Configuration Serveur Web

#### Apache (.htaccess)
```apache
RewriteEngine On
RewriteBase /
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]
```

#### Nginx
```nginx
location / {
    try_files $uri $uri/ /index.php?url=$uri&$args;
}
```

### 7.3 Sauvegarde et Restauration

#### Sauvegarde MongoDB
```bash
# Sauvegarde complète
mongodump --uri="mongodb://localhost:27017" --out=/backup/compta

# Sauvegarde incrémentale (recommandée quotidiennement)
mongodump --uri="mongodb://..." --out=/backup/compta-$(date +%Y%m%d)
```

#### Restauration
```bash
mongorestore --uri="mongodb://localhost:27017" /backup/compta
```

#### Sauvegarde des fichiers
```bash
# Sauvegarde quotidienne recommandée
tar -czf /backup/compta-app-$(date +%Y%m%d).tar.gz /srv/application
```

### 7.4 Migration (si applicable)
- Import des données existantes depuis ancien système
- Validation des soldes après migration
- Vérification de l'intégrité des données
- Formation des utilisateurs sur le nouveau système

---

## 8. FORMATION ET ACCOMPAGNEMENT

### 8.1 Formation utilisateurs

#### Formation Admin (2 jours)
- Installation et configuration
- Gestion des utilisateurs et rôles
- Sauvegarde et restauration
- Surveillance et logs

#### Formation Comptables (1 jour)
- Navigation et authentification
- Journal comptable (saisie, modification, recherche)
- Balance et Grand Livre
- Relevé et exports PDF
- Validation des opérations

#### Formation Gestionnaire Stock (0,5 jour)
- Accès au module Stock
- Saisie des opérations (entrée, sortie, inventaire)
- Consultation des fiches de stock
- Export des rapports

#### Formation Gestionnaire Paie (1 jour)
- Gestion des chantiers et ouvriers
- Configuration des salaires
- Saisie des présences
- Génération des fiches de paie
- Signature et archivage

### 8.2 Documentation
- **Manuel utilisateur** : par rôle et par module
- **Guide administrateur** : installation, configuration, maintenance
- **Guide technique** : architecture, API, bases de données
- **Guides rapides** : procédures courantes (5-10 pages chacun)

### 8.3 Support
- **Hotline technique** : Disponibilité 1ère semaine
- **Support par email/chat** : Pour questions post-formation
- **Escalade** : Pour bugs critiques

---

## 9. MAINTENANCE ET SUPPORT

### 9.1 Maintenance Préventive

#### Quotidien
- Vérification des logs de l'application
- Vérification du disque disponible
- Monitoring des connexions

#### Hebdomadaire
- Sauvegarde des bases de données (recommandé quotidien)
- Sauvegarde des fichiers de l'application
- Vérification de l'intégrité des données

#### Mensuel
- Nettoyage des anciennes sessions
- Vérification des mises à jour de sécurité PHP/MongoDB
- Optimization des index MongoDB
- Rapport de performance

#### Annuel
- Audit de sécurité
- Revue des rôles et permissions
- Archivage des données anciennes
- Plan de continuité de service

### 9.2 Gestion des Incidents

#### Criticité 1 (Critique)
- Application indisponible ou données corrompues
- **Délai réponse** : 1 heure
- **Délai résolution** : 4 heures

#### Criticité 2 (Haute)
- Perte de fonctionnalité importante
- **Délai réponse** : 4 heures
- **Délai résolution** : 1 jour

#### Criticité 3 (Normale)
- Dysfonctionnement mineur
- **Délai réponse** : 1 jour
- **Délai résolution** : 5 jours

### 9.3 Logs et Monitoring
- Logs applicatifs : `/var/log/compta-app/`
- Logs PHP : Configuration recommandée en production
- Logs MongoDB : Monitoring via mongostat
- Alertes email : Erreurs critiques

---

## 10. ROADMAP ET ÉVOLUTIONS FUTURES

### Court terme (3-6 mois)
- [ ] API REST pour intégrations tierces
- [ ] Notifications par email (alertes stock minimum, paie générée)
- [ ] Authentification LDAP/Active Directory
- [ ] Tableaux de bord personnalisables
- [ ] Sauvegarde cloud

### Moyen terme (6-12 mois)
- [ ] Application mobile (consultation)
- [ ] Signatures électroniques avancées
- [ ] Synchronisation avec logiciels tiers (ERP, compta légale)
- [ ] Analytics avancées
- [ ] Workflow d'approbation

### Long terme (12+ mois)
- [ ] IA pour prédiction et anomalies
- [ ] Intégration bancaire (relevés automatiques)
- [ ] Module de facturation
- [ ] Gestion client/fournisseur
- [ ] Intégration e-commerce

---

## 11. CONDITIONS D'UTILISATION

### 11.1 Responsabilités du Maître d'Ouvrage
- Fournir infrastructure serveur adéquate
- Assurer maintenance technique serveur/réseau
- Garantir sauvegardes régulières
- Assurer la sécurité physique des serveurs
- Notifier les changements organisationnels

### 11.2 Responsabilités de l'Équipe IT
- Installation et configuration système
- Support utilisateurs
- Maintenance du code
- Évolutions et améliorations
- Documentation et formation

### 11.3 Responsabilités des Utilisateurs
- Respect des mots de passe (ne pas les partager)
- Utilisation conforme au périmètre autorisé par rôle
- Signalement des dysfonctionnements
- Respect des procédures documentées
- Validation régulière des opérations (contrôle interne)

### 11.4 Confidentialité et Données
- Les données restent propriété du Maître d'Ouvrage
- Accès strictement réservé aux utilisateurs habilités
- Conformité RGPD/CCPA (si applicable)
- Droit à l'oubli respecté
- Pas de transmission de données à des tiers

---

## 12. COÛTS ET RESSOURCES

### 12.1 Ressources Nécessaires

| Élément | Durée | Note |
|---------|-------|------|
| Déploiement/Installation | 2-3 jours | Infrastructure setup inclus |
| Formation Admin | 2 jours | Customisé à votre contexte |
| Formation Comptables | 1-2 jours | Par groupe ou individuel |
| Formation Stock | 1 jour | Sur site |
| Formation Paie | 1 jour | Avec démonstration live |
| Support initial (1 semaine) | 5 jours | Disponibilité directe |
| Documentation | 2-3 jours | Manuals + guides rapides |

### 12.2 Infrastructure (exemple)
- **Serveur** : VPS 2CPU/4GB RAM/50GB SSD ~ 150-300$/mois
- **Base de données** : MongoDB Atlas managed ~ 10-50$/mois
- **Domaine/Certificat SSL** : ~50-100$/an
- **Sauvegarde cloud** : ~20$/mois

### 12.3 Maintenance Annuelle
- Support technique : ~1000-2000$ (niveau débutant)
- Mises à jour/Évolutions : Variables selon demandes
- Infrastructure hosting : ~3000-5000$/an

---

## 13. CRITÈRES DE SUCCÈS

L'implémentation sera considérée comme réussie lorsque :

✅ **Fonctionnels**
- [ ] Tous les utilisateurs peuvent se connecter avec leurs rôles
- [ ] Journal comptable : 100 écritures/jour saisies sans erreur
- [ ] Balance : Équilibre vérifié quotidiennement
- [ ] Stock : Inventaire à jour et précis
- [ ] Paie : Fiches générées et signées hebdomadairement
- [ ] Exports PDF : < 5 secondes pour 100 lignes

✅ **Performance**
- [ ] Temps de réponse < 2 secondes pour pages standards
- [ ] Disponibilité > 99%
- [ ] Charge supportée : 50 utilisateurs simultanés

✅ **Sécurité**
- [ ] Authentification 100%
- [ ] HTTPS en production
- [ ] Audit trail complet
- [ ] Aucune accès non autorisé

✅ **Formation**
- [ ] 100% des utilisateurs formés
- [ ] Comprennent leurs rôles/permissions
- [ ] Autonomes pour tâches courantes

✅ **Support**
- [ ] Tickets/appels résolus en délais convenus
- [ ] Documentation accessible
- [ ] Pas d'incidents critiques non résolus

---

## 14. PLAN DE TRANSITION

### Phase 1 : Pilote (Semaines 1-2)
- Déploiement sur serveur de test
- Formation restreinte (2-3 utilisateurs clés)
- Tests fonctionnels complets
- Corrections des bugs trouvés

### Phase 2 : Formation (Semaines 3-4)
- Formation de tous les utilisateurs
- Documentation complète
- Support intensif pendant cette période
- Parallélisation avec ancien système

### Phase 3 : Production (Semaine 5)
- Arrêt de l'ancien système
- Basculement complet
- Support disponible immédiatement

### Phase 4 : Consolidation (Mois 2-3)
- Monitoring actif
- Optimisations basées sur retours utilisateurs
- Formation additionnelle si nécessaire
- Validation des processus

---

## 15. ACCEPTATION ET VALIDATION

### Critères d'acceptation
1. ✅ Application installée et accessible
2. ✅ Tous les modules fonctionnels selon spécifications
3. ✅ Utilisateurs formés et autonomes
4. ✅ Documentation remise et validée
5. ✅ Aucun bug critique en production
6. ✅ Procédures de sauvegarde en place et testées
7. ✅ Audit de sécurité effectué

### Signatures et Approbations

| Rôle | Nom | Signature | Date |
|------|-----|-----------|------|
| Maître d'Ouvrage (Responsable) | | | |
| Directeur IT | | | |
| Gestionnaire de Projet | | | |
| Chef Comptable | | | |

---

## 16. CONTACTS ET ESCALADE

### Support Technique
- **Email** : support@exemple.com
- **Téléphone** : +XXX XXXX XXXX
- **Chat/Slack** : #support-compta
- **Heures** : 08h00-17h00, lun-ven

### Escalade
1. Support technique → 2. Chef de projet → 3. Directeur IT → 4. DSI/Maître d'Ouvrage

### Documentation et Ressources
- Wiki interne : [lien]
- Email support : support@exemple.com
- Portail de support : [lien]

---

## ANNEXES

### Annexe A : Glossaire
- **Comptabilité** : Enregistrement des opérations financières
- **Journal** : Registre des écritures comptables
- **Grand Livre** : Ensemble des comptes détaillés
- **Balance** : Vérification de l'équilibre débit/crédit
- **Stock** : Inventaire et gestion des articles
- **Paie** : Rémunération et gestion des ouvriers
- **RBAC** : Control d'accès basé sur les rôles
- **CSRF** : Attaque intersites (forgerie)
- **PDF** : Format de document portable
- **MongoDB** : Base de données NoSQL

### Annexe B : Documents de Référence
- [ROLE_SYSTEM.md](ROLE_SYSTEM.md) - Détails système de rôles
- [PAYROLL_SYSTEM_GUIDE.md](PAYROLL_SYSTEM_GUIDE.md) - Guide paie complet
- [GUIDE_STOCK_MANAGER.md](GUIDE_STOCK_MANAGER.md) - Guide stock
- [README.md](README.md) - Installation rapide

### Annexe C : Procédures d'Urgence
**Si l'application est inaccessible :**
1. Vérifier la connexion réseau
2. Vérifier que le serveur est allumé (ping)
3. Vérifier que MongoDB est démarré
4. Vérifier les logs applicatifs
5. Contacter le support technique

**Si les données sont corrompues :**
1. Arrêter immédiatement l'application
2. Restaurer la sauvegarde la plus récente
3. Contactez le support technique immédiatement
4. Vérifier l'intégrité des données

**Si un utilisateur non autorisé accède :**
1. Réinitialiser le mot de passe
2. Vérifier les logs de connexion
3. Supprimer les comptes inutilisés
4. Signaler au responsable IT

---

**Document préparé par** : [Équipe de projet]  
**À valider par** : [Maître d'Ouvrage]  
**Date de signature** : ___________  
**Version** : 1.0  
**Date de dernière modification** : Avril 2026
