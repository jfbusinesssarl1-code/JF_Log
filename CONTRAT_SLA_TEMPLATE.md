# SERVICE LEVEL AGREEMENT (SLA)
## Système de Gestion Comptable et Logistique Intégré

**Effectif** : À partir de la mise en production  
**Durée** : 1 an (renouvelable)  
**Version** : 1.0

---

## 1. DÉFINITIONS

### 1.1 Termes
- **Service** : L'application web complète (comptabilité, stock, paie)
- **Maître d'Ouvrage (MO)** : L'entreprise cliente
- **Prestataire** : L'équipe de support/maintenance
- **Disponibilité (Uptime)** : Temps où le service est accessible et fonctionnel
- **Temps d'arrêt (Downtime)** : Période où le service est inaccessible ou dégradé
- **Incident** : Interruption non planifiée du service
- **Maintenance planifiée** : Arrêt préalablement communiqué
- **Jour ouvrable** : Lundi-Vendredi, hors jours fériés

### 1.2 Severité des Incidents

| Niveau | Description | Exemples |
|--------|-------------|----------|
| **CRITIQUE (P1)** | Service complètement indisponible | - Application down - Données corrompues - Authentification impossible |
| **HAUTE (P2)** | Perte de fonctionnalité majeure | - Module journal HS - Stock inaccessible - Exports cassés |
| **NORMALE (P3)** | Dysfonctionnement mineur | - Lenteur > 5 sec - UI bug mineur - Export lent |
| **BASSE (P4)** | Demande d'amélioration/Question | - Comment faire... - Suggestion feature |

---

## 2. NIVEAUX DE SERVICE

### 2.1 Disponibilité Garantie

#### Système Standard (INCLUS)
- **Disponibilité cible** : **99.0%** par mois (maximum 7h30 downtime)
- **Jours mesurés** : Jours ouvrables 08h00-18h00 (CET)
- **Maintenance exclue** : Jusqu'à 4h par mois (24h prédévis)

#### Système Premium (OPTIONNEL +500$/an)
- **Disponibilité cible** : **99.5%** par mois (maximum 3h45 downtime)
- **Jours mesurés** : 24/7/365
- **Maintenance exclue** : Jusqu'à 2h par mois (48h prédévis)

#### Calcul Uptime
```
Disponibilité % = (Temps total - Downtime) / Temps total × 100

Exemple:
- Mois avril : 30 jours = 720 heures
- Downtime : 4 heures
- Uptime : (720 - 4) / 720 = 99.44%
- Résultat : ✅ Conforme 99.0% SLA
```

### 2.2 Temps de Réponse Garantis

| Severité | Standard | Premium |
|----------|----------|---------|
| **P1 (Critique)** | < 4 heures | < 1 heure |
| **P2 (Haute)** | < 1 jour ouvrable | < 4 heures |
| **P3 (Normale)** | < 2 jours ouvrables | < 1 jour ouvrable |
| **P4 (Basse)** | < 5 jours ouvrables | < 2 jours ouvrables |

**Temps de réponse** = Intervalle entre réception du ticket et première réponse technique

### 2.3 Temps de Résolution Cibles

| Severité | Standard | Premium |
|----------|----------|---------|
| **P1 (Critique)** | 4-8 heures | 2-4 heures |
| **P2 (Haute)** | 1-2 jours | 4-8 heures |
| **P3 (Normale)** | 5-10 jours | 2-5 jours |
| **P4 (Basse)** | 15-30 jours | 10-15 jours |

**Objectif de résolution**, pas garantie stricte. Dépend complexité du problème.

---

## 3. SUPPORT TECHNIQUE

### 3.1 Heures d'Ouverture

#### Support Standard
- **Heures** : Lundi-Vendredi 08h00-17h00 (CET)
- **Jours fériés** : Fermé
- **Urgences after-hours** : Escalade possible (sur-coût ou plan premium)

#### Support Premium
- **Heures** : 24/7/365
- **On-call team** : Disponible après-heures
- **Escalade** : Immédiate

### 3.2 Canaux de Contact

| Canal | Support Std | Support Prem | Temps réponse |
|-------|-------------|-------------|---------------|
| **Email** (support@) | ✅ | ✅ | < 24h |
| **Téléphone** (9-17h) | ✅ | ✅ | < 2h |
| **Chat/Slack** | ✅ (hrs) | ✅ (24/7) | < 1h / < 30min |
| **Ticketing system** | ✅ | ✅ | Auto-track |
| **Escalade urgence** | Voicemail | Direct | ASAP |

### 3.3 Limite du Support

**INCLUS dans le support**:
- ✅ Bugs et dysfonctionnements
- ✅ Troubleshooting
- ✅ Performance issues
- ✅ Sauvegardes/restaurations
- ✅ Manuels utilisateurs
- ✅ Formation utilisateurs (4h/an)
- ✅ Mises à jour sécurité

**NON inclus** (services additionnels payants):
- ❌ Modifications code custom (> 4h/an)
- ❌ Consulting architecture
- ❌ Intégrations API tiers
- ❌ Conversion données massives
- ❌ Déplacements physiques sur site
- ❌ Audit de sécurité
- ❌ Formation métier avancée (> 4h)

---

## 4. SAUVEGARDES ET RÉCUPÉRATION

### 4.1 Politique Sauvegardes

| Aspect | Standard | Premium |
|--------|----------|---------|
| **Fréquence** | Quotidienne | Horaire |
| **Rétention** | 30 jours | 90 jours |
| **Test restore** | Mensuel | Bi-hebdo |
| **RTO¹** | 4 heures | 1 heure |
| **RPO²** | 24 heures | 1 heure |

¹ **RTO** (Recovery Time Objective) = Temps pour récupérer le système  
² **RPO** (Recovery Point Objective) = Perte de données maximale tolérée

### 4.2 Scénarios de Récupération

#### Données Corrompues
- Restauration depuis backup immédiate
- Vérification intégrité (< 1h après restauration)
- Notification utilisateurs

#### Serveur Down
- Basculement sur serveur de secours (premium)
- Restauration depuis backup (standard)
- Temps total : < 4-8h

#### Attaque/Sécurité
- Isolation immédiate
- Restauration depuis backup clean
- Enquête forensique
- Renforcement sécurité

---

## 5. MAINTENANCE ET MISES À JOUR

### 5.1 Maintenance Planifiée

#### Réparties
- **Fréquence** : Jusqu'à 4h/mois
- **Prédévis** : Minimum 24 heures
- **Fenêtre** : De préférence marges (20h-06h ou samedi)
- **Notification** : Email + SMS (if premium)

#### Mises à Jour Sécurité
- **Criticité** : Déployées sous < 48h
- **Notification** : Au moins 24h avant
- **Rollback** : Plan disponible si nécessaire

#### Mises à Jour Mineures
- **Planification** : Mensuelle si applicable
- **Prédévis** : 1 semaine minimum
- **Fenêtre maintenance** : Fin de semaine idéalement

#### Mises à Jour Majeures
- **Planification** : Annuellement (ex: PHP 8.0 → 8.2)
- **Consultation** : Discussion 1 mois avant
- **Période** : Hors périodes critiques
- **Support** : 1 semaine post-update intensif

### 5.2 Vérifications Préventives

| Activité | Fréquence | Coût |
|----------|-----------|------|
| Check-up santé système | Mensuel | Inclus |
| Nettoyage logs/sessions | Trimestriel | Inclus |
| Optimization base données | Trimestriel | Inclus |
| Sécurité scan | Mensuel | Inclus |
| Test restore données | Trimestrial | Inclus |

---

## 6. EXCLUSIONS DU SLA

Le SLA ne s'applique pas :

### 6.1 Maintenance Planifiée
- ❌ Temps d'arrêt prévu (avec prédévis 24h+)
- ❌ Jusqu'à 4h par mois au maximum

### 6.2 Situations Hors Contrôle
- ❌ Pan services réseau (ISP down)
- ❌ Arrêt électricité (si on-prem)
- ❌ Attaques DDoS massives
- ❌ Désastres naturels
- ❌ Conflits armés / situation exceptionnelle

### 6.3 Responsabilités MO
- ❌ Problèmes causés par modifications non autorisées
- ❌ Non-paiement de la souscription
- ❌ Abus ou utilisation contraire aux T&C
- ❌ Problèmes réseau/infrastructure client
- ❌ Configuration incorrecte par le MO

### 6.4 Limites de Responsabilité
- ❌ Pertes indirectes (perte de revenus, données client)
- ❌ Dommages consécutifs
- ❌ Responsabilité limitée au coût annuel du service

---

## 7. MÉTRIQUES ET RAPPORTS

### 7.1 Dashboards Disponibilité
- **URL** : [dashboard.support.com/availability]
- **Accès** : 24/7 pour MO
- **Données** : Uptime en temps réel + historique
- **Alertes** : Notifications incident en temps réel

### 7.2 Rapports Mensuels
- **Livré** : Avant le 5 du mois suivant
- **Contenu** :
  - Disponibilité % (vs cible SLA)
  - Incidents P1-P4 (nombre, durée)
  - Temps réponse/résolution moyens
  - Mises à jour déployées
  - Problèmes récurrents
  - Recommandations

### 7.3 Réunions de Revue
- **Fréquence** : Mensuel (jour 1 du mois)
- **Durée** : 30 minutes
- **Participants** : IT Dir MO + Tech Lead
- **Ordre du jour** :
  - Revue SLA/metrics
  - Issues et résolutions
  - Roadmap prochaine période
  - Q&A

---

## 8. CRÉDITS SLA (SERVICE CREDITS)

### 8.1 Calcul des Crédits

Si disponibilité < cible SLA du mois :

| Disponibilité réalisée | Mensuel (Standard) | Mensuel (Premium) |
|------------------------|------------------|------------------|
| 99.0% à 99.5% | 5% remboursement | 2% remboursement |
| 98.5% à 99.0% | 10% remboursement | 5% remboursement |
| 98.0% à 98.5% | 15% remboursement | 10% remboursement |
| < 98.0% | 30% remboursement | 25% remboursement |

**Exemple** :
- SLA cible : 99.0%
- Réalisé : 98.5%
- Remboursement : 10% du coût du mois

### 8.2 Procédure de Reclamation
1. MO contact support par email (dans 30 jours du mois)
2. Prestataire vérifie données SLA
3. Si justifié, crédit appliqué au prochain mois
4. Pas de remboursement en cash (crédit uniquement)

### 8.3 Limitations
- ❌ Pas de cumul crédits > 1 mois
- ❌ Pas de reclamation après 30 jours
- ❌ Maintenance planifiée exclue
- ❌ Incidents externaux exclus
- ❌ Crédit max : 30% du coût mensuel

---

## 9. PROCESSUS INCIDENTS

### 9.1 Signalement Incidents

**Procédure** :
1. **Report immédiat** via canal urgent (phone/chat)
2. **Ticket ouvert** automatiquement
3. **Acknowledge** dans < 1h (P1) ou < 4h (P2)
4. **Investigation** commence
5. **Updates** toutes les 2h (P1) ou 4h (P2)
6. **Résolution** with RCA (root cause analysis)
7. **Clôture** avec documentation

### 9.2 Escalade

**Niveau 1** : Support technique → < 4h (P1)  
**Niveau 2** : Architecte système → < 8h (P1)  
**Niveau 3** : Management IT → < 24h (P1)  

---

## 10. DROITS ET RESPONSABILITÉS

### 10.1 Droits du MO
- ✅ Support réactif selon SLA
- ✅ Accès documentation techniques
- ✅ Formation utilisateurs (4h/an)
- ✅ Transparence sur incidents
- ✅ Crédits SLA si non-respect

### 10.2 Responsabilités du MO
- ✅ Paiement à temps
- ✅ Fourniture infrastructure adéquate (si on-prem)
- ✅ Gestion des identifiants de base
- ✅ Respect des conditions d'usage
- ✅ Notification des bugs aux prestataires
- ✅ Maintien de sauvegardes locales (recommandé)
- ✅ Non-modification du code sans accord
- ✅ Conformité réglementaire données

### 10.3 Droits du Prestataire
- ✅ Maintenance planifiée (< 4h/mois)
- ✅ Exclusion incidents externaux
- ✅ Escalade des abus/usage contraire

### 10.4 Responsabilités Prestataire
- ✅ Support réactif selon SLA
- ✅ Qualité du code et des services
- ✅ Sécurité des données
- ✅ Conformité réglementaire
- ✅ Documentation et traçabilité
- ✅ Notification des incidents
- ✅ Crédits SLA si défaut

---

## 11. GOUVERNANCE

### 11.1 Comité de Pilotage
- **Réunion** : Mensuelle (1er jour du mois)
- **Durée** : 30-45 minutes
- **Participants** :
  - CTO/Dir IT MO
  - Resp Support Prestataire
  - Product Manager (si applicable)
- **Ordre du jour** :
  - Métriques SLA
  - Escalations issues
  - Roadmap
  - Améliorations proposées

### 11.2 Revue Annuelle
- **Timing** : 30 jours avant renouvellement
- **Sujets** :
  - Achievements vs SLA
  - Issues lessons learned
  - Changements besoins
  - Renouvellement conditions

---

## 12. DURÉE ET RENOUVELLEMENT

### 12.1 Période Initiale
- **Durée** : 12 mois à partir go-live
- **Début** : Date mise en production
- **Fin** : Date - 1 an

### 12.2 Renouvellement
- **Auto-renew** : Oui (12 mois additionnels)
- **Préavis arrêt** : 60 jours avant fin
- **Discussions** : Idéalement 90 jours avant
- **Modifications** : Possible lors renouvellement

---

## 13. RÉSILIATION

### 13.1 Résiliation par MO
- **Pour cause** : Si SLA répétitivement non respecté (> 3x/an)
- **Procédure** : Avertissement écrit (30j) + tentative correction
- **Délai** : 60 jours après avertissement
- **Données** : Copies fournies dans 15j

### 13.2 Résiliation par Prestataire
- **Pour cause** : Non-paiement > 60 jours
- **Procédure** : Avertissement écrit (15j)
- **Délai** : 30 jours après avertissement
- **Données** : Copies fournies, puis suppression après 90j

### 13.3 Résiliation sans cause
- **Par soit partie** : Possible sous préavis 60j
- **Délai** : Fin de période en cours

---

## 14. SIGNATURES

| Rôle | Nom | Signature | Date |
|------|-----|-----------|------|
| **Maître d'Ouvrage** | | | |
| **Prestataire (Manager)** | | | |
| **CTO/Dir IT** | | | |

---

## ANNEXE A : SCHEDULE A - PRICING

### Service Standard

| Element | Coût mensuel | Coût annuel |
|---------|--------------|------------|
| Support technique | 165$ | 1,980$ |
| Infrastructure (cloud) | 150$ | 1,800$ |
| Maintenance | 85$ | 1,020$ |
| **TOTAL/MOIS** | **400$** | **4,800$** |

### Service Premium

| Element | Coût mensuel | Coût annuel |
|---------|--------------|------------|
| Support technique 24/7 | 330$ | 3,960$ |
| Infrastructure premium | 200$ | 2,400$ |
| Maintenance avancée | 170$ | 2,040$ |
| SLA Premium guarantee | 100$ | 1,200$ |
| **TOTAL/MOIS** | **800$** | **9,600$** |

---

## ANNEXE B : SERVICE LEVEL TARGETS (DÉTAIL)

### Performance

| Métrique | Target | Standard | Premium |
|----------|--------|----------|---------|
| Temps page < 2s | 90% | ✅ | ✅ |
| PDF export < 5s | 95% | ✅ | ✅ |
| Login success | 99.5% | ✅ | ✅ |
| Concurrent users | 50+ | ✅ | ✅ |

### Sécurité

| Métrique | Target | Implementation |
|----------|--------|-----------------|
| Patch update response | < 48h critical | Automated |
| Penetration test | Annual | Yes (premium only) |
| Access logs retention | 90 days | Configured |
| Encryption in transit | Required | HTTPS TLS 1.3+ |

---

**Document effectif à partir du** : [Date mise en production]  
**Dernier révision** : Avril 2026  
**Prochain review** : [Date + 1 an]

