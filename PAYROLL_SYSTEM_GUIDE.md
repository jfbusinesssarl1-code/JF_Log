# SYSTÈME DE GESTION DE PAIE ET PRÉSENCES DES OUVRIERS

## ✅ Implémentation Complète

Ce document résume la mise en place du système de gestion de la paie et des présences pour les ouvriers journaliers.

---

## 📁 Fichiers Créés

### 1. **Modèles MongoDB** (5 fichiers)

#### `app/models/SiteModel.php`
- Gère les chantiers (sites de travail)
- Méthodes : create(), getAll(), getActive(), getById(), update(), delete(), getStats()
- Stocke : nom, localisation, ingénieur, magasinier, description, statut

#### `app/models/WorkerModel.php`
- Gère les ouvriers
- Méthodes : create(), getBySite(), getById(), update(), delete(), archive(), getTTBySite(), getMCBySite()
- Stocke : site_id, nom, catégorie (T.T ou M.C), statut

#### `app/models/SalaryConfigModel.php`
- Gère la configuration des salaires par chantier et catégorie
- Méthodes : create(), getBySiteAndCategory(), getBySite(), updateBySiteAndCategory()
- Calcule automatiquement les tarifs de demi-journée (50% du tarif journalier)
- Tarifs :
  - **T.T (Tout Travaux)** : 3$ par jour (défaut), 1.5$ demi-journée
  - **M.C (Maçon)** : Configurable - 6$, 7$, ou autre selon le chantier

#### `app/models/AttendanceModel.php`
- Gère les eregistrements de présence hebdomadaire
- Méthodes : upsert(), getBySiteAndWeek(), getByWorkerAndWeek(), getByWorker(), deleteByS iteAndWeek(), getDailyStats()
- Champs : présence par jour (0, 0.5, ou 1), jours travaillés, signature

#### `app/models/PayslipModel.php`
- Génère et gère les fiches de paie
- Méthodes : upsert(), getBySiteAndWeek(), getBySite(), generatePayslip(), savePayslip(), delete()
- Génère automatiquement les 3 tableaux requis

### 2. **Contrôleur** (1 fichier)

#### `app/controllers/PayrollController.php`
- Gère tous les flux du module payroll
- Nécessite le rôle **admin** pour toutes les actions
- Sections :
  1. **Gestion des Chantiers** : sites(), createSite(), editSite(), siteDetail()
  2. **Gestion des Ouvriers** : workers(), createWorker(), editWorker()
  3. **Configuration Salaires** : salaryConfig()
  4. **Saisie Présences** : attendance()
  5. **Fiches de Paie** : payslip(), savePayslip(), exportPayslipPDF(), payslips()

### 3. **Vues** (8 fichiers)

#### `app/views/admin/payroll_sites.php`
- Liste tous les chantiers avec statistiques
- Affiche : nom, localisation, ingénieur, magasinier, nombre d'ouvriers

#### `app/views/admin/payroll_site_form.php`
- Formulaire de création/édition de chantier
- Champs : nom, localisation, ingénieur, magasinier, description, statut

#### `app/views/admin/payroll_site_detail.php`
- Tableau de bord d'un chantier
- Affiche : infos du chantier, statistiques, raccourcis vers les modules
- Boutons d'accès : Gestion Ouvriers, Configuration Salaires, Saisir Présences, Fiches de Paie

#### `app/views/admin/payroll_workers.php`
- Liste les ouvriers d'un chantier
- Affiche : No, Nom, Catégorie (badge), Statut

#### `app/views/admin/payroll_worker_form.php`
- Formulaire d'ajout/édition d'ouvrier
- Champs : nom, catégorie (T.T ou M.C)

#### `app/views/admin/payroll_salary_config.php`
- Configure les tarifs journaliers par catégorie
- Affiche : tarif journalier et demi-journée (calculée)
- Sections pour T.T et M.C avec explications

#### `app/views/admin/payroll_attendance.php`
- Saisit les présences pour chaque ouvrier et chaque jour
- Tableau avec :
  - Colonnes : No, Nom, Catégorie, Lund, Mard, Merc, Jeud, Vend, Sam, Signature
  - Valeurs possibles : - (absent), ½ (demi-journée), ✓ (journée complète)
- Navigation par semaine

#### `app/views/admin/payroll_payslip.php`
- Affiche la fiche de paie hebdomadaire avec **3 TABLEAUX REQUIS**:

  **Tableau 1 : Tableau de Paie des Ouvriers**
  - Colonnes : No, Noms, Catégorie, Lund, Mard, Merc, Jeud, Vend, Sam, Jrs Prestés, Sal Hebdo
  - Calcule automatiquement le salaire hebdomadaire

  **Tableau 2 : Synthèse Journalière T.T (Tout Travaux)**
  - Colonnes : Jour, Volume Journalier, Équivalence
  - Somme les préstat ions par jour

  **Tableau 3 : Synthèse Journalière M.C (Maçon)**
  - Colonnes : Jour, Volume Journalier, Équivalence
  - Somme les prestations par jour

- Boutons : Enregistrer, Exporter PDF

#### `app/views/admin/payroll_payslips_list.php`
- Liste toutes les fiches de paie générées
- Affiche : Semaine du, Total Salaires, Nombre Ouvriers, Date Création

---

## 🔐 Contrôle d'Accès

- **Restriction** : Seul le rôle **admin** peut accéder au module payroll
- **Utilisation** : Méthode `$this->requireAdmin()` dans chaque action du contrôleur
- **Navigation** : Un lien "Gestion de Paie" est ajouté au dropdown menu pour les administrateurs

---

## 🛣️ Routing

Ajout dans `/public/index.php` :
```php
case 'payroll':
    $controller = new App\Controllers\PayrollController();
    // Actions routées selon $_GET['action']
```

Routes disponibles :
- `?page=payroll` → Liste des chantiers
- `?page=payroll&action=sites` → Liste des chantiers
- `?page=payroll&action=createSite` → Créer un chantier
- `?page=payroll&action=editSite&id={id}` → Éditer un chantier
- `?page=payroll&action=siteDetail&id={id}` → Détail d'un chantier
- `?page=payroll&action=workers&site_id={id}` → Liste des ouvriers
- `?page=payroll&action=createWorker&site_id={id}` → Ajouter un ouvrier
- `?page=payroll&action=editWorker&id={id}&site_id={site_id}` → Éditer un ouvrier
- `?page=payroll&action=salaryConfig&site_id={id}` → Configuration des salaires
- `?page=payroll&action=attendance&site_id={id}&week_of={date}` → Saisir les présences
- `?page=payroll&action=payslip&site_id={id}&week_of={date}` → Voir la fiche de paie
- `?page=payroll&action=payslips&site_id={id}` → Liste des fiches de paie

---

## 📊 Structure des Données MongoDB

### Collection `sites`
```json
{
  "_id": ObjectId,
  "name": "Chantier A",
  "location": "Localisation",
  "engineer_name": "Nom Ingénieur",
  "warehouse_manager_name": "Nom Magasinier",
  "description": "Description",
  "status": "active|inactive",
  "created_at": "YYYY-MM-DD HH:MM:SS",
  "updated_at": "YYYY-MM-DD HH:MM:SS"
}
```

### Collection `workers`
```json
{
  "_id": ObjectId,
  "site_id": ObjectId,
  "name": "Nom Ouvrier",
  "category": "T.T|M.C",
  "status": "active|archived",
  "created_at": "YYYY-MM-DD HH:MM:SS",
  "updated_at": "YYYY-MM-DD HH:MM:SS"
}
```

### Collection `salary_configs`
```json
{
  "_id": ObjectId,
  "site_id": ObjectId,
  "category": "T.T|M.C",
  "daily_rate": 3.0,
  "half_day_rate": 1.5,
  "created_at": "YYYY-MM-DD HH:MM:SS",
  "updated_at": "YYYY-MM-DD HH:MM:SS"
}
```

### Collection `attendances`
```json
{
  "_id": ObjectId,
  "site_id": ObjectId,
  "worker_id": ObjectId,
  "week_of": "YYYY-MM-DD",
  "category": "T.T|M.C",
  "monday": 0|0.5|1,
  "tuesday": 0|0.5|1,
  "wednesday": 0|0.5|1,
  "thursday": 0|0.5|1,
  "friday": 0|0.5|1,
  "saturday": 0|0.5|1,
  "days_worked": 5.5,
  "created_at": "YYYY-MM-DD HH:MM:SS",
  "updated_at": "YYYY-MM-DD HH:MM:SS"
}
```

### Collection `payslips`
```json
{
  "_id": ObjectId,
  "site_id": ObjectId,
  "week_of": "YYYY-MM-DD",
  "payroll": [
    {
      "worker_id": ObjectId,
      "worker_name": "Nom",
      "category": "T.T|M.C",
      "monday": 0|0.5|1,
      "...": "...",
      "days_worked": 5,
      "daily_rate": 3.0,
      "weekly_salary": 15.0
    }
  ],
  "daily_synthesis_tc": [
    { "day": "Lundi", "daily_volume": 3, "equivalence": 3 }
  ],
  "daily_synthesis_mc": [...],
  "total_salary": 500.0,
  "created_at": "YYYY-MM-DD HH:MM:SS",
  "updated_at": "YYYY-MM-DD HH:MM:SS"
}
```

---

## 🚀 Guide d'Utilisation

### Étape 1 : Créer un Chantier
1. Accédez à : `?page=payroll`
2. Cliquez sur "Nouveau Chantier"
3. Remplissez les informations (nom, localisation, ingénieur, magasinier)
4. Enregistrez

### Étape 2 : Ajouter des Ouvriers
1. Depuis le détail du chantier, cliquez sur "Gestion Ouvriers"
2. Cliquez sur "Ajouter un Ouvrier"
3. Saisissez le nom et la catégorie (T.T ou M.C)
4. Enregistrez

### Étape 3 : Configurer les Salaires
1. Depuis le détail du chantier, cliquez sur "Configuration Salaires"
2. Définissez les tarifs journaliers pour T.T et M.C
3. Les tarifs de demi-journée sont calculés automatiquement (50%)
4. Enregistrez

### Étape 4 : Saisir les Présences Hebdomadaires
1. Depuis le détail du chantier, cliquez sur "Saisir Présences"
2. Pour chaque ouvrier et chaque jour, sélectionnez :
   - "-" pour absent
   - "½" pour demi-journée
   - "✓" pour journée complète
3. Enregistrez

### Étape 5 : Générer la Fiche de Paie
1. Depuis le détail du chantier, cliquez sur "Fiches de Paie"
2. Cliquez sur "Consulter" pour générer la fiche d'une semaine
3. La fiche génère automatiquement les 3 tableaux requis :
   - Tableau de paie
   - Synthèse journalière T.T
   - Synthèse journalière M.C
4. Cliquez "Enregistrer" pour sauvegarder, ou "PDF" pour exporter

---

## 🔧 Configuration des Tarifs

### Tarifs par Défaut
- **T.T (Tout Travaux)** : 3$ par jour → 1.5$ demi-journée
- **M.C (Maçon)** : 6$ par jour → 3$ demi-journée

### Configuration Personnalisée par Chantier
Chaque chantier peut avoir des tarifs différents pour M.C :
- Chantier A : 6$ par jour
- Chantier B : 7$ par jour
- Chantier C : Tarif personnalisé (convention après finissage)

---

## 📋 Calcul du Salaire Hebdomadaire

```
Salaire Hebdomadaire = Σ(jours travaillés × tarif journalier)

Exemple pour T.T :
- Lundi : ✓ (1 jour) → 3$
- Mardi : ½ (demi-jour) → 1.5$
- Mercredi : ✓ (1 jour) → 3$
- ...
- Total = 3 + 1.5 + 3 + ... = Salaire Hebdo
```

---

## ⚙️ Fichiers Modifiés

- `public/index.php` : Ajout du routing pour PayrollController
- `app/views/navbar.php` : Ajout du lien d'accès au module pour les admins

---

## 📝 Notes

- Tous les calculs de salaire sont faits automatiquement à partir des présences
- Les tarifs de demi-journée sont toujours la moitié du tarif journalier
- Les fiches de paie peuvent être régénérées à tout moment
- Les données sont stockées en MongoDB pour la flexibilité
- Les archives soft-delete : les ouvriers demandent archive et non suppression

---

## 🎯 Prochaines Étapes (Optionnel)

1. Implémenter l'export PDF complet avec mPDF
2. Ajouter les signatures numériques
3. Implémenter les rapports mensuels ou trimestriels
4. Ajouter les retenues/bonus par ouvrier
5. Configurer les notifications d'approbation de paie
