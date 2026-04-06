# DEVIS DÉTAILLÉ
## Système de Gestion Comptable et Logistique Intégré

**Référence** : DEVIS-2026-04-001  
**Valide jusqu'au** : 31 mai 2026  
**Maître d'Ouvrage** : [À remplir]  
**Date de création** : 1er avril 2026

---

## 1. RÉSUMÉ COMMERCIAL

| Élément | Montant |
|---------|---------|
| **Services de déploiement** | 4,500$ |
| **Licences logicielles** | 0$ (open-source) |
| **Infrastructure 1ère année** | 3,600$ |
| **Formation initiale** | 2,400$ |
| **Documentation** | 1,200$ |
| **Support 1ère année** | 2,000$ |
| ─────────────────── | ───────── |
| **TOTAL ANNÉE 1** | **13,700$** |
| **Coûts récurrents année 2+** | 7,500$/an |

---

## 2. DÉTAIL DES SERVICES

### 2.1 DÉPLOIEMENT ET INSTALLATION (4,500$ - 30 jours)

#### Phase 1 : Préparation Infrastructure (3 jours - 450$)
| Tâche | Durée | Coût |
|-------|-------|------|
| Spécifications serveur finales | 0,5j | 75$ |
| Configuration serveur (OS, réseau, sécurité) | 1j | 150$ |
| Installation PHP 8.0, Apache/Nginx | 1j | 150$ |
| Installation MongoDB 4.2+ | 0,5j | 75$ |
| **Sous-total Phase 1** | **3j** | **450$** |

#### Phase 2 : Installation Application (4 jours - 600$)
| Tâche | Durée | Coût |
|-------|-------|------|
| Récupération code source + dépendances | 0,5j | 75$ |
| Configuration BD et migration initiale | 1,5j | 225$ |
| Configuration sécurité (HTTPS, firewall) | 1j | 150$ |
| Tests fonctionnels complets | 1j | 150$ |
| **Sous-total Phase 2** | **4j** | **600$** |

#### Phase 3 : Initialisation et Tests (2 jours - 300$)
| Tâche | Durée | Coût |
|-------|-------|------|
| Création compte admin initial | 0,5j | 75$ |
| Configuration authentification LDAP (optionnel) | 1j | 150$ |
| Tests charges (50 utilisateurs) | 0,5j | 75$ |
| **Sous-total Phase 3** | **2j** | **300$** |

#### Phase 4 : Documentation Technique (2 jours - 300$)
| Tâche | Durée | Coût |
|-------|-------|------|
| Doc architecture système | 0,5j | 75$ |
| Doc maintenance et sauvegardes | 0,5j | 75$ |
| Runbook procédures d'urgence | 0,5j | 75$ |
| Doc accès et credentials sécurisé | 0,5j | 75$ |
| **Sous-total Phase 4** | **2j** | **300$** |

#### Phase 5 : Mise en Production (1 jour - 150$)
| Tâche | Durée | Coût |
|-------|-------|------|
| Basculement production (cutover) | 0,5j | 75$ |
| Vérification intégrité données | 0,5j | 75$ |
| **Sous-total Phase 5** | **1j** | **150$** |

#### Phase 6 : Support Go-Live (4 jours - 1,200$)
| Tâche | Durée | Coût |
|-------|-------|------|
| Support direct en production (semaine 1) | 4j | 1,200$ |
| Hotline pour bugs critiques | | Inclus |
| Optimisations initiales | | Inclus |
| **Sous-total Phase 6** | **4j** | **1,200$** |

**TOTAL DÉPLOIEMENT** : 16 jours × 300$/jour = **4,800$**  
(Ajustement : 4,500$ pour forfait)

---

### 2.2 FORMATION UTILISATEURS (2,400$ - 10 jours)

#### Formation Admin (2 jours - 600$)
**Participants** : 1-2 personnes IT  
**Contenu** :
- Installation et configuration système
- Gestion des utilisateurs et rôles
- Gestion des sauvegardes et restaurations
- Monitoring et logs
- Procédures d'urgence
- Maintenance préventive

**Formateur** : Senior Developer (300$/jour)  
**Coût** : 2j × 300$ = 600$

#### Formation Comptables (1 jour - 300$)
**Participants** : 3-5 comptables  
**Contenu** :
- Navigation et authentification
- Saisie journal comptable
- Validation partie double
- Balance et Grand Livre
- Relevé et filtres
- Exports PDF

**Formateur** : Functional Expert (300$/jour)  
**Coût** : 1j × 300$ = 300$

#### Formation Gestionnaire Stock (0.5 jour - 150$)
**Participants** : 2-3 personnes  
**Contenu** :
- Accès module Stock
- Saisie opérations (entrée, sortie, inventaire)
- Consultation fiches de stock
- Exports rapports
- Alertes stock minimum

**Formateur** : Functional Expert (300$/jour)  
**Coût** : 0,5j × 300$ = 150$

#### Formation Paie et Présences (2 jours - 600$)
**Participants** : 2-3 personnes RH/Admin  
**Contenu** :
- Gestion chantiers et ouvriers
- Configuration des tarifs
- Saisie hebdomadaire des présences
- Génération fiches de paie
- Signature électronique
- Exports et archivage

**Formateur** : Functional Expert (300$/jour)  
**Coût** : 2j × 300$ = 600$

#### Formation Utilisateurs Finaux (2 jours - 600$)
**Participants** : Tous (15-20 personnes)  
**Format** : Groupe 5-6 personnes, 4 sessions  
**Contenu** : Rôle-spécifique  
- Authentification et profil
- Accès aux fonctionnalités autorisées
- Opérations courantes par rôle
- Support et escalade

**Formateur** : 2 Functional Experts (300$/jour)  
**Coût** : 2j × 2 × 300$ = 1,200$ (amorti sur 4 sessions) = 300$ facturé

#### Matériel Pédagogique (?) (150$)
**Inclus** :
- Slide de présentation personnalisées
- Fiches d'aide rapides (quick start guides)
- Procédures étape par étape
- Vidéos de démonstration (optionnel)

**Coût** : 150$

**TOTAL FORMATION** : (600+300+150+600+300+150) = **2,100$**  
(Avec marge : 2,400$)

---

### 2.3 DOCUMENTATION (1,200$ - 8 jours)

| Document | Durée | Coût | Description |
|----------|-------|------|-------------|
| **Manuel Utilisateur Admin** | 2j | 300$ | Installation, config, maintenance, troubleshooting |
| **Manuel Comptable** | 1,5j | 225$ | Journal, balance, grand livre, exports |
| **Manuel Gestionnaire Stock** | 1j | 150$ | Stock, opérations, rapports |
| **Manuel Paie** | 1,5j | 225$ | Chantiers, ouvriers, présences, fiches paie |
| **Guide Dépannage** | 1j | 150$ | Procédures urgence, FAQ, logs |
| **API/Tech Docs** | 1j | 150$ | Pour futurs développements |
| **Glossaire & Processus** | 0,5j | 75$ | Termes métier et flux |
| ────────────────── | ───── | ───── | ─────────────────────── |
| **TOTAL** | **8j** | **1,200$** | Documents en PDF + web |

**Livrables** :
- [ ] Manuals en PDF (imprimables)
- [ ] Manuals en HTML (consultables en ligne)
- [ ] Guidesd'aide rapides (A4 plastifiée pour bureaux)
- [ ] Video tutorials (YouTube privée ou serveur)
- [ ] FAQ consultable dans l'app

---

### 2.4 INFRASTRUCTURE 1ÈRE ANNÉE (3,600$)

#### Option A : Cloud VPS (RECOMMANDÉE)

| Composant | Fournisseur | Coût/mois | Coût annuel |
|-----------|-------------|-----------|------------|
| **Serveur VPS** (2CPU, 4GB RAM, 50GB SSD) | DigitalOcean/Scaleway | 20$ | 240$ |
| **Base MongoDB** (managed) | MongoDB Atlas (3GB free → $15/mois après) | 10$ (moy) | 120$ |
| **Nom de domaine** (.com/.net) | GoDaddy/Namecheap | 12$ | 12$ |
| **Certificat SSL** (auto-renew) | Let's Encrypt (gratuit) + gestion | 0$ | 0$ |
| **Sauvegarde cloud** (Wasabi/Backblaze) | Wasabi | 7$ | 84$ |
| **Email support** (helpdesk) | Freshdesk startup | 20$ | 240$ |
| **Monitoring & Alerts** (Uptime Robot, Datadog) | Mixte | 10$ | 120$ |
| ────────────────── | ─────────────────────── | ───── | ──────── |
| **TOTAL INFRASTRUCTURE** | Coût mensuel : **79$** | **Annuel : 948$** | |

**Note** : Avec croissance, risque montée à 150-200$/mois en année 2-3.

#### Option B : Serveur On-Premises (SEMI-GÉRÉ)

| Composant | Coût initial | Coût annuel |
|-----------|--------------|------------|
| **Serveur physique** (2U rack, 2CPU, 16GB RAM) | 2,000$ (amortissement 4 ans) | 500$ |
| **Mise en rack et connectivité** | 500$ | 100$ |
| **Maintenance matériel** (SLA 4h) | | 600$ |
| **MongoDB Pro Support** (optionnel) | | 500$ |
| **Backup appliance (NAS)** | 1,500$ (amortissement 3 ans) | 500$ |
| **Sauvegardes hors-site** | | 200$ |
| **Electricité + Clim** | | 800$ |
| **Assurance & Garantie** | | 400$ |
| ────────────────── | ────────── | ──────────── |
| **TOTAL** | **4,000$** | **3,600$/an** |

**Comparaison** :
- **Cloud** : Meilleure scalabilité, moins de maintenance IT, plus prévisible
- **On-Prem** : Contrôle total, coût long-terme meilleur, nécessite IT interne

**RECOMMANDATION** : Cloud VPS pour yr1-2, on-prem si 50+ users et données critiques.

**DEFAULT** : 948$ cloud en année 1 (Réalité : généralement ~1,500$ avec frais supplémentaires)  
**BUDGETER** : 3,600$ année 1 pour infrastructure (conservative)

---

### 2.5 SUPPORT 1ÈRE ANNÉE (2,000$)

#### Forfait Support Standard (2,000$)

| Service | Détail | Coût |
|---------|--------|------|
| **Tickets support** (email/chat) | Réponse < 24h, résolution < 5j | 1,000$ |
| **Bugs critiques** (hotline) | Réponse < 4h, disponibilité 8-18h lun-ven | Inclus |
| **Consultations techniques** (4h/mois) | Questions architecture, optimisation | Inclus |
| **Mises à jour PHP/MongoDB** | Gestion updates sécurité | 500$ |
| **Rapports mensuels** (performance, logs) | Dashboard de santé du système | 500$ |
| **Formations additionnelles** (4h) | Si nouveaux utilisateurs | Inclus |
| ────────────────── | ───────────────────────────── | ──────── |
| **TOTAL ANNÉE 1** | Support réactif et proactif | **2,000$** |

#### Option : Support Premium (+ 1,000$, total 3,000$)
- Réponse tickets < 4h (24/7)
- 2h/semaine de consulting proactif
- Optimisations régulières
- Escalade prioritaire

#### Option : Support Self-Service (- 500$, total 1,500$)
- Accès documentation seule
- Tickets ayant réponse < 2j (pas de garantie)
- Pas de support proactif
- **Non recommandé pour production**

**RECOMMANDÉ** : Forfait Standard (2,000$) année 1 minimum

---

## 3. RÉCAPITULATIF FINANCIER

### 3.1 Année 1 (Implémentation complète)

```
Déploiement & Installation       4,500$
  ├─ Préparation infra          450$
  ├─ Installation app           600$
  ├─ Tests                      300$
  ├─ Documentation tech         300$
  ├─ Mise en production         150$
  └─ Support go-live           1,200$

Formation utilisateurs          2,400$
  ├─ Admin (2j)                600$
  ├─ Comptables (1j)           300$
  ├─ Stock (0,5j)              150$
  ├─ Paie (2j)                 600$
  ├─ Utilisateurs finaux (2j)  600$
  └─ Matériel pédagogique      150$

Documentation                   1,200$
  ├─ Manuels utilisateur       900$
  ├─ Guide technique           200$
  └─ Vidéos + FAQ              100$

Infrastructure                  3,600$
  ├─ Cloud VPS                  948$
  ├─ Domaine + SSL              12$
  ├─ Sauvegarde                 84$
  ├─ Monitoring                120$
  └─ Buffer/imprévus         2,436$

Support année 1                 2,000$
  ├─ Tickets support          1,000$
  ├─ Bugs critiques              0$ (inclus)
  ├─ Mises à jour             500$
  └─ Rapports                  500$

─────────────────────────────────────
TOTAL ANNÉE 1                 13,700$
```

### 3.2 Années 2+ (Maintenance)

```
Infrastructure                  3,600$
  ├─ Cloud VPS                 1,500$ (croissance)
  ├─ MongoDB managed             240$
  ├─ Sauvegardes                 200$
  ├─ Support infra               500$
  └─ Monitoring                  160$

Support technique               3,600$
  ├─ Tickets/hotline           2,000$
  ├─ Mises à jour             1,000$
  └─ Rapports & consulting      600$

Évolutions/Maintenance          1,000-3,000$
  ├─ Petits ajustements         1,000$
  ├─ Nouvelles versions           500$
  └─ Buffer pour imprévus       500-1,500$

─────────────────────────────────────
TOTAL ANNÉE 2+                7,500-9,200$/an
```

### 3.3 Projections 5 ans

| Année | Coût | Notes |
|-------|------|-------|
| **Année 1** | **13,700$** | Implémentation complète |
| **Année 2** | **8,000$** | Maintenance, 1-2 petites évolutions |
| **Année 3** | **9,000$** | Croissance infra, évolutions |
| **Année 4** | **8,500$** | Stabilité |
| **Année 5** | **9,000$** | Modernisation (PHP upgrade) |
| **TOTAL 5 ans** | **48,200$** | Coût de possession |

**Coût annuel moyen** : 9,640$/an

---

## 4. HYPOTHÈSES ET CONDITIONS

### 4.1 Hypothèses de facturation
- ✅ Taux horaire standard 300$/jour (8h)
- ✅ Basé sur équipe en remote
- ✅ Délais incluent communications mais pas déplacements physiques
- ✅ Frais déplacements non inclus (si visite sur site)

### 4.2 Exclusions (NON inclus)
- ❌ Déplacements physiques sur site
- ❌ Hébergement et repas
- ❌ Migration de données complexes (audit archivage)
- ❌ Modifications extensive du code (au-delà roadmap)
- ❌ Formation aux outils tiers (comptes client, banques)
- ❌ Audit de sécurité indépendant (recommandé à part, 2-3k$)
- ❌ Conseils fiscaux/légaux (voir expert-comptable)

### 4.3 Inclusions
- ✅ Installation complète
- ✅ Configuration et sécurisation
- ✅ Formation de base
- ✅ Documentation techniques
- ✅ Support 4 semaines post go-live
- ✅ Manuels utilisateur
- ✅ 1 an de support standard (tickets, hotline bugs)
- ✅ Mises à jour sécurité PHP/MongoDB

### 4.4 Conditions de paiement

| Étape | Montant | Délai |
|-------|---------|-------|
| **À la signature** | 30% | Avant démarrage |
| **Installation complète** | 35% | Fin semaine 2 |
| **Formation + test** | 25% | Fin semaine 3 |
| **Après go-live (30j)** | 10% | 30j après la mise en production |

**Total** : 30% + 35% + 25% + 10% = 100%

**Exemple** (sur 13,700$) :
- À signature : 4,110$
- Fin install : 4,795$
- Fin formation : 3,425$
- Après go-live : 1,370$

---

## 5. SERVICES ADDITIONNELS (À LA CARTE)

### 5.1 Services de Consulting

| Service | Durée | Coût |
|---------|-------|------|
| **Audit de sécurité** | 3j | 900$ |
| **Tuning performance** | 2j | 600$ |
| **Migration données complexe** | 3-5j | 900-1,500$ |
| **Intégration API tiers** | 5j | 1,500$ |
| **Customisation code** (par jour) | 1j | 300$ |
| **Nouveau module** (ex: facturation) | 15-20j | 4,500-6,000$ |

### 5.2 Services de Support Avancés

| Service | Coût |
|---------|------|
| **Support Premium** (24/7, réponse 4h) | +1,000$/an |
| **SLA Uptime 99.9%** | +500$/an |
| **Backup management avancée** | +300$/an |
| **Consulting proactif** (2h/semaine) | +2,000$/an |

### 5.3 Services de Maintenance Étendue

| Service | Coût |
|---------|------|
| **Upgrade vers MongoDB 6+** | 2,000$ |
| **Migration PHP 8 → 8.2** | 1,500$ |
| **Haut-dispo (cluster MongoDB)** | 3,000$ initial + 500$/an |
| **Authentification LDAP/SSO** | 1,500$ |
| **Rapports BI avancés** | 2,000$ |

---

## 6. COMPARAISON AVEC LOGICIELS COMMERCIAUX

### MariaDB vs Solvetech Accounting (logiciel concurrent)

| Critère | Notre solution | Solvetech Std | Solvetech Pro |
|---------|----------------|---------------|---------------|
| **Coût initial** | 13,700$ | 5,000$ | 12,000$ |
| **Coût/an** | 7,500$ | 2,000$ | 5,000$ |
| **TCO 5 ans** | 48,200$ | 15,000$ | 37,000$ |
| **Modules inclus** | ✅ Tous | journal (base) | journal + stock |
| **Paie** | ✅ Inclus | ❌ À +5k$ | ✅ Inclus |
| **Utilisateurs** | Illimité | max 5 | max 20 |
| **Support** | Réactif | Support email | Support phone |
| **Données** | Sur site | Cloud vendor | Cloud vendor |
| **Customisation** | Possible | Non | Limité |
| **Intégrations** | API custom | Non | Limitées |
| **Scalabilité** | Excellente | Moyenne | Bonne |

**Conclusion** : Notre solution meilleure pour :
- Organisations avec besoins paie importants
- Besoin de scalabilité
- Souveraineté des données
- Longévité et indépendance technologique

---

## 7. MODALITÉS DE RÉVISION DU DEVIS

### 7.1 Révisions tarifaires
- ✅ Prix valides jusqu'au **31 mai 2026**
- ✅ Au-delà, revoir avec taux jour actuel
- ✅ Augmentation inflation : ~3% par an

### 7.2 Changements de scope
- ❌ Ajout modules : + 5,000-15,000$ par module
- ❌ Augmentation training : + 300$/jour
- ❌ Infrastructure upgrade : + coût réel

### 7.3 Conditions de révision
- ✅ Délai de démarrage > 3 mois → Révision tarifaire
- ✅ Changement infrastructure → Revoir coûts
- ✅ Équipe projet modifiée → Possible réduction

---

## 8. APPROUVÉ PAR

| Rôle | Nom | Signature | Date |
|------|-----|-----------|------|
| **Directeur Commercial** | | | |
| **Responsable IT (MO)** | | | |

---

## 9. CONDITIONS GÉNÉRALES

- ✅ Contrat selon T&C standard fourni séparément
- ✅ Confidentialité : NDA applicable
- ✅ Propriété intellectuelle : Code source transmis, liberté d'usage
- ✅ Modifications : Écrites seulement (pas verbal)
- ✅ Litige : Résolution amiable puis arbitrage
- ✅ Assurance : RC professionnelle incluse
- ✅ Garantie : 30j post go-live (bugs de production)

---

**Devis valide pour signature jusqu'au 31 mai 2026**

**Pour acceptation, signature ou questions** : [Contact email/tél]

