# PLANNING DE MISE EN ŒUVRE
## Système de Gestion Comptable et Logistique Intégré

**Période** : Avril - Juin 2026  
**Durée totale** : 12 semaines (3 mois)  
**Go-Live estimé** : Fin semaine 3-4 (mi-mai 2026)

---

## 1. CALENDRIER GLOBAL

```
AVRIL 2026                    MAI 2026                      JUIN 2026
┌─────────────────────────┬─────────────────────────┬───────────────────┐
│                         │                         │                   │
│ PHASE 1                 │ PHASE 2                 │ PHASE 3           │
│ PRÉPARATION             │ IMPLÉMENTATION          │ STABILISATION     │
│ & DÉPLOIEMENT           │ & FORMATION             │ & SUPPORT         │
│                         │                         │                   │
│ (2-3 semaines)          │ (4 semaines)            │ (4 semaines)      │
│                         │                         │                   │
└─────────────────────────┴─────────────────────────┴───────────────────┘
```

---

## 2. PHASE 1 : PRÉPARATION ET DÉPLOIEMENT (Semaines 1-3)

### 2.1 Semaine 1 : Préparation & Kick-off

```
LUNDI 1er avril
├─ 09h00-10h00 : Kick-off général (40 min)
│   ├─ Présente équipe projet
│   ├─ Overview timeline
│   ├─ Points de contact
│   └─ Q&A
├─ 10h00-12h00 : Architecture & Infrastructure review (2h)
│   ├─ Visite data center / infrastructure cloud
│   ├─ Vérification connectivité réseau
│   ├─ Validation specs MongoDB
│   └─ Planification domaine/SSL
├─ 14h00-17h00 : Configuration serveur (3h)
│   ├─ Installation OS + mise à jour
│   ├─ Configuration réseau & firewall
│   ├─ Préparation user/directories
│   └─ Première sauvegarde
└─ 17h00-18h00 : Rapport journalier

MARDI 2 avril
├─ 08h00-12h00 : Installation stack technique (4h)
│   ├─ Installation PHP 8.0+ & modules
│   ├─ Installation Apache/Nginx
│   ├─ Installation MongoDB 4.2+
│   ├─ Installation Composer
│   └─ Tests élémentaires
├─ 13h00-17h00 : Continuación (4h)
│   ├─ Configuration SSL/HTTPS
│   ├─ Configuration Virtual Hosts
│   ├─ Backup configuration
│   └─ Monitore setup
└─ 17h00-18h00 : Rapport du jour

MERCREDI 3 avril
├─ 08h00-09h00 : Validation phase 1 (1h)
├─ 09h00-10h30 : Réunion validation IT (1,5h)
│   ├─ Vérification checklist infra
│   ├─ Performance baseline tests
│   ├─ Sécurité initiale (scan)
│   └─ Sign-off technique
├─ 10h30-12h00 : Préparation donnees initiales (1,5h)
│   ├─ Audit données anciennes (si migration)
│   ├─ Planification import
│   ├─ Créate mapping comptes
│   └─ Prepare test data
├─ 13h00-17h00 : Application deployment (4h)
│   ├─ Clone code source
│   ├─ Installer dependances (composer)
│   ├─ Configure app settings
│   └─ Database initialization
└─ 17h00-18h00 : Rapport

JEUDI 4 avril
├─ 08h00-12h00 : Tests application (4h)
│   ├─ Test authentification & login
│   ├─ Test modules comptabilité (basic)
│   ├─ Test stock (basic)
│   ├─ Test paie (basic)
│   ├─ Report bugs trouvés
│   └─ Fix CRITICAL
├─ 13h00-16h00 : Rectifications (3h)
│   ├─ Fix bugs found
│   ├─ Validate fixes
│   ├─ Regression testing
│   └─ Performance check
└─ 16h00-17h00 : Réunion status + doc (1h)

VENDREDI 5 avril
├─ 08h00-11h00 : Finalisations (3h)
│   ├─ Fix remaining issues
│   ├─ Database backup validation
│   ├─ Documentation checklist
│   └─ Go-live readiness review
├─ 11h00-12h00 : Sign-off phase 1 (1h)
│   ├─ MO validation checklist
│   ├─ IT sign-off
│   └─ Go-decision pour phase 2
├─ 13h00-17h00 : Buffering pour problèmes (4h)
│   ├─ Resolve blocking issues
│   ├─ Document learnings
│   ├─ Prep training materials
│   └─ Brief admin team
└─ 17h00-18h00 : Rapport semaine 1

STATUS WEEK 1 : ✅ INFRASTRUCTURE READY
```

**Livrables fin semaine 1** :
- [ ] Serveur 100% opérationnel
- [ ] Application déployée et testée
- [ ] Admin account créé (credentials sécurisé)
- [ ] Sauvegardes configurées et testées
- [ ] Documentation tech préliminaire
- [ ] Sign-off MO phase 1

---

### 2.2 Semaine 2 : Installation Application & Formation Admin

```
LUNDI 8 avril - FORMATION ADMIN JOUR 1
├─ 09h00-10h00 : Installation system deep-dive
│   ├─ Architecture review
│   ├─ Controllers, Models, Views
│   ├─ Database schema
│   └─ Security model (RBAC)
├─ 10h00-11h00 : Configuration & Customisation
│   ├─ app/core/Database.php
│   ├─ Paramètres MongoDB
│   ├─ SSL/HTTPS setup
│   ├─ User authentication
│   └─ Logs & monitoring
├─ 11h00-12h00 : Gestion Utilisateurs & Rôles
│   ├─ Création utilisateurs
│   ├─ Assignation rôles
│   ├─ Modifier permissions
│   └─ Demo live interface
├─ 13h00-15h00 : Sauvegardes & Restauration
│   ├─ MongoDB backup strategies
│   ├─ Full backup procedure
│   ├─ Incremental backups
│   ├─ Test restore scenario
│   └─ Disaster recovery plan
├─ 15h00-16h00 : Monitoring & Logs
│   ├─ System logs review
│   ├─ Performance metrics
│   ├─ Alert configuration
│   ├─ Troubleshooting guide
│   └─ Q&A
└─ 16h00-17h00 : Documentation & Lab

MARDI 9 avril - FORMATION ADMIN JOUR 2
├─ 09h00-10h00 : Procédures d'Urgence
│   ├─ Application down scenarios
│   ├─ Database corruption recovery
│   ├─ Security breach protocol
│   ├─ Runbooks & checklists
│   └─ Escalation procedures
├─ 10h00-12h00 : Maintenance Préventive
│   ├─ Nettoyage logs & sessions
│   ├─ Database optimization
│   ├─ Index management
│   ├─ Updates & Patches
│   └─ Upgrade procedures
├─ 13h00-14h00 : Support & Service Level
│   ├─ Support procedures
│   ├─ Bug reporting
│   ├─ SLA management
│   ├─ Escalation
│   └─ Documentation process
├─ 14h00-15h30 : Hands-on Lab
│   ├─ Create 10 test users
│   ├─ Assign different roles
│   ├─ Test access controls
│   ├─ Create backup
│   ├─ Restore from backup
│   └─ Troubleshooting exercise
├─ 15h30-16h30 : Certification & Q&A
│   ├─ Admin skills assessment
│   ├─ Review key points
│   ├─ Q&A
│   └─ Training certificate
└─ 16h30-17h00 : Feedback & Next Steps

MERCREDI 10 avril - PRÉPARATION FORMATION UTILISATEURS
├─ 08h00-10h00 : Préparation materials (2h)
│   ├─ Créer exemple data (journal entries, stock, paie)
│   ├─ Tester scénarios training
│   ├─ Préparer accounts test pour participants
│   ├─ Print documents & guides
│   └─ Setup training classroom
├─ 10h00-12h00 : Final systems checks (2h)
│   ├─ Verify all features working
│   ├─ Performance baseline
│   ├─ Stress test (20 concurrent users)
│   ├─ Document any issues
│   └─ Final firewall adjustments
├─ 13h00-14h00 : Admin assessment (1h)
│   ├─ Quiz admin knowledge
│   ├─ Test procedures
│   ├─ Verify competency
│   └─ Provide feedback
├─ 14h00-16h00 : Data import (if needed) (2h)
│   ├─ Import historical data (if applicable)
│   ├─ Validate data integrity
│   ├─ Balance verification
│   └─ Document mapping
└─ 16h00-17h00 : Rapport semaine

JEUDI 11 avril - FORMATION COMPTABLES
├─ 08h00-09h00 : Introduction & Login (1h)
│   ├─ System overview
│   ├─ Interface navigation
│   ├─ Login & profile
│   └─ Role-based features tour
├─ 09h00-11h00 : Journal Comptable (2h)
│   ├─ Journal interface & features
│   ├─ Créer écritures (step-by-step)
│   ├─ Validation partie double
│   ├─ Correction & modification
│   ├─ Recherche & filtrage
│   └─ Demo & hands-on practice
├─ 11h00-12h00 : Balance & Soldes (1h)
│   ├─ Balance interface
│   ├─ Vérification débit/crédit
│   ├─ Identification anomalies
│   ├─ Export PDF
│   └─ Practice exercise
├─ 13h00-14h00 : Grand Livre (1h)
│   ├─ Grand livre consultation
│   ├─ Détail par compte
│   ├─ Historique opérations
│   ├─ Filtres & recherche
│   └─ Reports
├─ 14h00-15h00 : Relevé & Exports (1h)
│   ├─ Relevé interface
│   ├─ Filtres avancés
│   ├─ Export fonctionnalités
│   ├─ PDF generation
│   └─ Impression
├─ 15h00-16h30 : Lab & Practice (1,5h)
│   ├─ Saisir 5 écritures test
│   ├─ Vérifier balance
│   ├─ Consulter grand livre
│   ├─ Générer rapports
│   └─ Q&A
└─ 16h30-17h00 : Recap & Next Steps

VENDREDI 12 avril - FORMATION STOCK & PAIE (Part 1)
├─ 09h00-10h00 : Stock Manager Training (1h)
│   ├─ Module Stock overview
│   ├─ Gestion articles
│   ├─ Opérations (entrée, sortie, inventaire)
│   ├─ Fiche de stock
│   ├─ Rapports
│   └─ Q&A
├─ 10h00-11h30 : Stock Lab (1,5h)
│   ├─ Créer articles
│   ├─ Enregistrer opérations
│   ├─ Consulter stock
│   ├─ Export rapports
│   └─ Practice
├─ 11h30-12h00 : Wrap up
├─ 13h00-14h00 : Paie Overview (1h)
│   ├─ Module payroll structure
│   ├─ Chantiers & ouvriers
│   ├─ Configuration salaires
│   ├─ Saisie présences
│   ├─ Génération fiches
│   └─ Overview process
├─ 14h00-16h00 : Paie Demo & Lab Part 1 (2h)
│   ├─ Create chantiers
│   ├─ Create workers
│   ├─ Configure salary rates
│   ├─ Practice entries
│   └─ First hands-on
└─ 16h00-17h00 : Questions & Recap

STATUS WEEK 2 :  ✅ FORMATION ADMIN + COMPTABLES ✅
```

**Livrables fin semaine 2** :
- [ ] Admin formé et certifié
- [ ] Comptables formés et autonomes
- [ ] Gestionnaire stock formé
- [ ] Début formation paie
- [ ] Test users créés
- [ ] Exemple données chargées
- [ ] Training guides remis
- [ ] Sign-off formation

---

### 2.3 Semaine 3 : Formation Paie & Utilisateurs Finaux

```
LUNDI 15 avril - FORMATION PAIE (Part 2)
├─ 09h00-11h00 : Paie Lab continuation (2h)
│   ├─ Saisir présences complètes
│   ├─ Générer fiches de paie
│   ├─ Vérifier calculs automatiques
│   ├─ Signer électroniquement
│   ├─ Exporter en PDF
│   └─ Practice exercise
├─ 11h00-12h00 : Archivage & Rapports (1h)
│   ├─ Archivage fiches
│   ├─ Historique worker
│   ├─ Rapports statistiques
│   ├─ Masse salariale
│   └─ Exports
├─ 13h00-14h00 : Cas réels & Troubleshooting (1h)
│   ├─ Gestion corrections
│   ├─ Erreur dans présence
│   ├─ Régularisation paie
│   ├─ Cas spéciaux
│   └─ Q&A
├─ 14h00-15h30 : Lab final (1,5h)
│   ├─ Scénario complet: chantier neuf
│   ├─ 10 ouvriers, 2 semaines
│   ├─ Générer fiches complètes
│   ├─ Signature & archivage
│   └─ Export rapports
└─ 15h30-17h00 : Certification (1,5h)

MARDI 16 avril - FORMATION UTILISATEURS FINAUX - GROUP 1
(Comptables + Gestionnaires comptables, 6-8 personnes)

09h00-10h30 : Module 1 - Introduction & Navigation (1,5h)
├─ Bienvenue message
├─ System overview 10 min
├─ Login et profil 10 min
├─ Interface navigation 15 min
├─ Rôles et permissions 10 min
├─ Demo live 10 min
└─ Q&A 10 min

10h45-12h15 : Module 2 - Comptabilité (1,5h)
├─ Journal comptable 30 min
│  ├─ Interface overview
│  ├─ Créer écritures
│  ├─ Validation
│  └─ Modification
├─ Balance 20 min
│  ├─ Consultation
│  ├─ Vérification soldes
│  └─ Rapports
├─ Grand livre 20 min
└─ Practice hands-on 20 min

13h15-14h45 : Module 3 - Opérations courantes (1,5h)
├─ Recherche & filtrage 20 min
├─ Exports PDF 15 min
├─ Impression 10 min
├─ Consultation relevés 20 min
├─ Archivage 10 min
└─ Procédures troubleshoot 15 min

15h00-16h30 : Module 4 - Practice & Lab (1,5h)
├─ Saisir 3 écritures guidées
├─ Vérifier balance
├─ Générer balance PDF
├─ Consulter grand livre
├─ Export recherche
└─ Final Q&A

16h30-17h00 : Feedback & Certificat
├─ Recap points clés
├─ Q&A final
├─ Training certificate
└─ Feedback form

MERCREDI 17 avril - FORMATION UTILISATEURS - GROUP 2
(Stock Manager + Gestionnaires logistique, 3-5 personnes)

09h00-10h30 : Module 1 - Introduction à Stock (1,5h)
├─ Overview module
├─ Navigation interface
├─ Concepts: articles, opérations, solde
├─ Rôles stock manager
└─ Demo

10h45-12h15 : Module 2 - Gestion articles (1,5h)
├─ Créer articles 30 min
├─ Catégories 20 min
├─ Propriétés (prix, quantité) 20 min
├─ Modification 20 min

13h15-14h45 : Module 3 - Opérations (1,5h)
├─ Entrée en stock 30 min
├─ Sortie en stock 30 min
├─ Inventaire / ajustement 30 min

15h00-16h30 : Module 4 - Fiche stock & Rapports (1,5h)
├─ Consultation fiche 20 min
├─ Alertes minimum 15 min
├─ Historique mouvements 20 min
├─ Exports rapports 20 min
├─ Lab practice 25 min

16h30-17h00 : Feedback & Cert

JEUDI 18 avril - FORMATION UTILISATEURS - GROUP 3
(Paie + RH, 2-3 personnes)

09h00-10h30 : Module 1 - Introduction Paie (1,5h)
├─ Overview module
├─ Concepts: chantiers, ouvriers, paie
├─ Structure données
└─ Navigation

10h45-12h15 : Module 2 - Gestion opérationnelle (1,5h)
├─ Chantiers 30 min
├─ Ouvriers 30 min
├─ Configuration salaires 30 min

13h15-14h45 : Module 3 - Processus paie (1,5h)
├─ Saisie presences 40 min
├─ Génération fiches 30 min
├─ Signature 10 min
├─ Export PDF 10 min

15h00-16h30 : Module 4 - Lab complet (1,5h)
├─ Créer chantier
├─ Ajouter ouvriers
├─ Configurer tarifs
├─ Saisir presences semaine
├─ Générer fiches
├─ Export PDFs
└─ Practice scenario

16h30-17h00 : Feedback

VENDREDI 19 avril - TESTS & PRÉPARATION GO-LIVE

09h00-11h00 : Système global test (2h)
├─ Vérification toutes fonctionnalités
├─ Performance test avec charges
├─ Backup/restore validation
├─ Security check
└─ Document any issues

11h00-12h00 : Sign-off meeting (1h)
├─ Checklist everything working
├─ MO validation
├─ IT approval
├─ Go-decision pour go-live

13h00-14h00 : Buffer for critical fixes (1h)
├─ Address any CRITICAL issues
├─ Final patches
├─ Retest

14h00-15h00 : Documentation finales & handover (1h)
├─ Compléter docs
├─ Transfer de files & access
├─ Contact list
├─ Emergency procedures

15h00-17h00 : Préparation semaine 4 (2h)
├─ Briefing équipe go-live
├─ Test user access
├─ Review disaster plan
├─ Standby list

STATUS WEEK 3 : ✅ READY FOR GO-LIVE ✅
```

**Livrables fin semaine 3** :
- [ ] Tous les utilisateurs formés
- [ ] Système 100% testé et validé
- [ ] Documentation complète remise
- [ ] Sauvegardes vérifiées
- [ ] Sign-off final
- [ ] Standby team en place
- [ ] Prêt pour go-live

---

## 3. PHASE 2 : GO-LIVE (Semaine 4)

### 3.1 Lundi 22 avril - Production Cutover

```
22h00 (dimanche soir) : Arrêt ancien système
├─ Notification aux utilisateurs
├─ Last backup ancien système
├─ Notification "system offline"
└─ Team standby active

LUNDI 22 avril - 08h00
├─ 08h00-09h00 : Activation production (1h)
│   ├─ Vérifier serveur prêt
│   ├─ DNS routing verification
│   ├─ Test accès URL
│   ├─ Vérifier backups
│   └─ Notification utilisateurs
├─ 09h00-12h00 : Monitoring intensive (3h)
│   ├─ Monitor logins
│   ├─ Monitor performances
│   ├─ Monitor errors logs
│   ├─ First users support
│   └─ Hotline active
├─ 12h00-13h00 : Pause
├─ 13h00-17h00 : Continuous support (4h)
│   ├─ Ad-hoc support
│   ├─ Issue resolution
│   ├─ Performance tuning
│   ├─ Documentation updates
│   └─ Status reports each hour
└─ 17h00-18h00 : Daily standup + report
```

---

### 3.2 Mardi-Vendredi 23-26 avril - Intensive Support Week

```
Each day: 08h00-17h00
├─ 08h00-09h00 : Daily standup
│   ├─ Review overnight logs
│   ├─ Check critical issues
│   ├─ Plan day
│   └─ Escalations review
├─ 09h00-17h00 : Continuous operations
│   ├─ Monitor & support
│   ├─ Issue resolution (priority order)
│   ├─ Performance monitoring
│   ├─ User help (calls/email)
│   ├─ Documentation updates
│   └─ Training follow-up
└─ 17h00-18h00 : Daily report & escalations
```

**Coverage** :
- Level 1 support (2 people) : 08h00-18h00 live
- Level 2 support (tech) : On-call
- Manager : 08h00-18h00

---

## 4. PHASE 3 : STABILISATION (Semaines 5-8)

### 4.1 Semaine 5 : Support Intensif

```
LUNDI 29 avril
├─ 08h00-09h00 : Weekly review meeting
│   ├─ Issues & resolutions from week 1
│   ├─ Performance metrics
│   ├─ User feedback
│   ├─ Prioritize fixes
│   └─ Plan stabilization items
├─ 09h00-17h00 : Continuous support
│   ├─ Monitor & troubleshoot
│   ├─ Performance tuning
│   ├─ User training (advanced)
│   ├─ Process optimization
│   └─ Documentation updates

Level support: Reduced to business hours (8-17)
but still on-call for critical
```

---

### 4.2 Semaine 6 : Optimisation & Follow-up

```
ACTIONS
├─ Consulting 2h/jour (limited)
├─ Performance tuning
├─ Database optimization
├─ User training refresh (for advanced)
├─ Process improvement workshops
├─ Documentation supplements
└─ Support during business hours only
```

---

### 4.3 Semaine 7-8 : Normalisation

```
Transition vers support standard:
├─ Email support (< 24h réponse)
├─ Tickets system
├─ Documentation complet
├─ Ad-hoc consulting (payant après)
└─ Handover complet IT
```

---

## 5. JALONS ET LIVRABLES

### 5.1 Semaine 1 Livrables
- [ ] Infrastructure 100% operational
- [ ] Application deployed & tested
- [ ] Admin training completed & certified
- [ ] Initial backups configured & validated
- [ ] MO sign-off phase 1

### 5.2 Semaine 2-3 Livrables
- [ ] All users trained on their modules
- [ ] Training materials & certificates issued
- [ ] Test data loaded
- [ ] Final system validation completed
- [ ] Go-live readiness approved

### 5.3 Week 4 Deliverables (Go-Live)
- [ ] System live & accessible
- [ ] <10 critical incidents on day 1
- [ ] 95%+ user login success
- [ ] Hotline operational
- [ ] All modules functional

### 5.4 Week 5-8 Deliverables
- [ ] All critical issues resolved
- [ ] Performance SLA met
- [ ] User satisfaction > 80%
- [ ] Documentation complete
- [ ] Support transferred to operations

---

## 6. RISQUES & MITIGATION

| Risque | Probabilité | Impact | Mitigation |
|--------|-------------|--------|-----------|
| Infrastructure delay | Moyenne | Alto | Reservation cloud VPS maintenant |
| Data import issues | Basse | Alto | Test migration parallel week 1 |
| User resistance | Media | Média | 1-on-1 follow-up + quick wins |
| Performance issues | Basse | Alto | Load testing week 2 + DB tuning |
| Security breach | Très basse | Crítica | Pen test pre-prod + monitoring 24/7 |
| Key person absence | Muy baja | Alto | Redundancy in team. Cross-training |

---

## 7. COMMUNICATION PLAN

### Daily Updates
- 08h00 : Daily standup (15 min)
- 17h00 : Status report (email)

### Weekly Updates
- Monday 08h00 : Project meeting (30 min)
- Friday 15h00 : Weekly report (email)

### Stakeholder Comms
- Executive steering: Weekly via email
- Users: Announcements as needed
- Board: Monthly summary

---

## 8. SUCCESS CRITERIA BY WEEK

### Week 1
✅ Infrastructure ready  
✅ Admin trained  
✅ Zero critical incidents  
✅ System stable under test load  

### Week 2-3
✅ All users trained  
✅ Zero critical issues  
✅ System passing UAT  
✅ Backups tested & working  

### Week 4 (Go-Live)
✅ < 10 P1 incidents  
✅ 95% user access success  
✅ All modules functional  
✅ Zero data integrity issues  

### Week 5-8
✅ All P1 issues resolved  
✅ < 5 P2 issues remaining  
✅ Performance SLA met  
✅ User satisfaction > 80%  

---

## 9. RESSOURCES ALLOUÉES

| Rôle | Semaine 1-3 | Semaine 4 | Semaine 5-8 |
|------|-----------|----------|-----------|
| Project Manager | 100% | 100% | 50% |
| Senior Dev (Infra) | 100% | 50% | 20% |
| Developer (App) | 100% | 50% | 20% |
| Trainer | 80% | 0% | 10% |
| Support L1 | 0% | 100% | 50% |
| Support L2 | 0% | 100% | 50% |

---

## 10. MERMAID GANTT DIAGRAM

```
gantt
    title Système Comptable - Planning Implémentation
    dateFormat YYYY-MM-DD
    
    section Phase 1: Déploiement
    Infra & Setup        :p1_infra, 2026-04-01, 5d
    Installation App     :p1_app, 2026-04-02, 4d
    Testing & Fixes      :p1_test, 2026-04-03, 3d
    Formation Admin       :p1_admin, 2026-04-08, 2d
    Préparation Training  :p1_prep, 2026-04-10, 2d
    
    section Phase 2: Formation
    Formation Comptables  :p2_compta, 2026-04-11, 1d
    Formation Stock       :p2_stock, 2026-04-12, 1d
    Formation Paie        :p2_paie, 2026-04-11, 3d
    Formation Users Gr1   :p2_u1, 2026-04-16, 1d
    Formation Users Gr2   :p2_u2, 2026-04-17, 1d
    Formation Users Gr3   :p2_u3, 2026-04-18, 1d
    
    section Phase 3: Validation
    System Testing        :p3_test, 2026-04-19, 1d
    Sign-off & Approval   :p3_approval, 2026-04-19, 1d
    UAT                   :p3_uat, 2026-04-21, 1d
    
    section Phase 4: Go-Live
    Production Cutover    :crit, p4_cutover, 2026-04-22, 1d
    Intensive Support W1  :p4_sup1, 2026-04-22, 5d
    
    section Phase 5: Stabilisation
    Support W2            :p5_sup2, 2026-04-29, 5d
    Support W3            :p5_sup3, 2026-05-06, 5d
    Support W4            :p5_sup4, 2026-05-13, 5d
    Handover to Ops       :p5_handover, 2026-05-20, 1d
```

---

## 11. CONTACTS D'URGENCE

### Pendant go-live

| Rôle | Nom | Téléphone | Email |
|------|-----|-----------|-------|
| Project Manager | | | |
| Tech Lead | | | |
| Escalation | | | |
| After-hours | | | +xx |

### Après go-live

| Support Level | Contact | Heures |
|---------------|---------|--------|
| L1 Support | email | 08-18 |
| L2 Support | email | 08-18 |
| Escalation | phone | 08-18 |
| After-hours | On-call | 18-08 |

---

**Planning approuvé par** :

| Rôle | Signature | Date |
|------|-----------|------|
| Project Manager | | |
| MO Responsable IT | | |
| IT Director | | |

**Date document** : 1er avril 2026  
**Prochaine révision** : 15 avril 2026

