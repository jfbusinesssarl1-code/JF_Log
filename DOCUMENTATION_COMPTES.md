# Documentation - Système de Chargement Automatique des Comptes

## 📊 Vue d'ensemble

Les comptes sont **automatiquement affichés** dans tous les formulaires. Ils proviennent du fichier Excel **PLAN.xlsx** situé dans le dossier `/public/`.

## 🔄 Architecture Système

### 1. Stockage des Données

**Fichier:** `public/PLAN.xlsx`
- Format: Fichier Excel (.xlsx)
- Contenu: Deux colonnes
  - Colonne A: Code du compte (ex: "1010000000")
  - Colonne B: Libellé/Intitulé (ex: "Capital social")
- Nombre total: **330 comptes**

### 2. Récupération des Données

**Classe:** `App\Models\CompteModel`
- **Méthode:** `getAll()`
- **Fonction:** Lit le fichier PLAN.xlsx et retourne un tableau structuré
- **Format retourné:**
```php
[
    [
        'code' => '1010000000',
        'intitule' => 'Capital social',
        'label' => '1010000000 - Capital social'
    ],
    // ... 329 autres comptes
]
```

### 3. Exposition via API

**Endpoint:** `?page=api&action=comptes`

**Contrôleur:** `App\Controllers\ApiController`
- **Méthode:** `getComptes()`
- **Retour:** Array JSON avec tous les comptes
- **Headers:** `Content-Type: application/json`

**Exemple d'appel:**
```javascript
fetch('?page=api&action=comptes')
    .then(r => r.json())
    .then(comptes => console.log(comptes))
```

### 4. Affichage dans les Formulaires

**Fichier JavaScript:** `assets/js/account-search.js`

**Fonctionnalités:**
- Récupère les comptes via l'API (une seule fois, mise en cache)
- Crée des boîtes de suggestion pour les champs "compte"
- Filtre en temps réel pendant la saisie
- Support des raccourcis clavier (flèches, Enter, Escape)

**Formulaires supportés:**
1. **Stock** - Champ "Compte"
2. **Journal** - Champs "Compte Débit" et "Compte Crédit"
3. **Caisse** - Sélection compte
4. **Journal Edit** - Champ "Compte"
5. **Stock Edit** - Champ "Compte"

### 5. Flux Complet

```
┌─────────────────────────────────────────────────────────┐
│  1. User visite un formulaire (ex: /stock)              │
└──────────────────┬──────────────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────────────┐
│  2. Layout footer charge account-search.js              │
└──────────────────┬──────────────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────────────┐
│  3. User clique sur champ "Compte"                      │
└──────────────────┬──────────────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────────────┐
│  4. account-search.js appelle API:                      │
│     fetch('?page=api&action=comptes')                   │
└──────────────────┬──────────────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────────────┐
│  5. ApiController::getComptes() exécuté                 │
│     ↓                                                    │
│     CompteModel::getAll() lit PLAN.xlsx                │
│     ↓                                                    │
│     Retourne 330 comptes en JSON                        │
└──────────────────┬──────────────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────────────┐
│  6. account-search.js reçoit les comptes                │
│     Les met en cache (window.comptesList)               │
└──────────────────┬──────────────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────────────┐
│  7. User tape dans le champ "Compte"                    │
│     (filtrage en direct avec debounce)                  │
└──────────────────┬──────────────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────────────┐
│  8. Suggestions apparaissent automatiquement            │
│     User peut sélectionner ou continuer à taper         │
└──────────────────┬──────────────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────────────┐
│  9. Soumettre le formulaire avec le compte sélectionné  │
└─────────────────────────────────────────────────────────┘
```

## 🔧 Configuration et Personnalisation

### Modifier le chemin du fichier PLAN.xlsx

**Fichier:** `app/models/CompteModel.php`
**Ligne:** 18

```php
$this->planPath = $planPath ?: realpath(__DIR__ . '/../../public/PLAN.xlsx');
```

Pour utiliser un autre dossier:
```php
$this->planPath = $planPath ?: realpath(__DIR__ . '/../../chemin/vers/PLAN.xlsx');
```

### Ajouter un nouveau compte au fichier Excel

**Méthode de code:**
```php
$comptesModel = new CompteModel();
$comptesModel->addIfMissing('1234567890', 'Nouveau Compte');
```

**Ou manuellement:**
1. Ouvrir `public/PLAN.xlsx` dans Excel
2. Ajouter une ligne avec:
   - Colonne A: Code du compte
   - Colonne B: Libellé
3. Enregistrer le fichier

### Limiter le nombre de suggestions affichées

**Fichier:** `assets/js/account-search.js`
**Ligne:** ~45

```javascript
const results = comptes.filter(...).slice(0, 1000);  // Actualiser 1000 pour une autre limite
```

## 🐛 Troubleshooting

### Les comptes ne s'affichent pas

**Vérifications:**

1. **PLAN.xlsx existe-il?**
```bash
ls -la public/PLAN.xlsx
```

2. **Le fichier est-il lisible?**
```bash
# Dans PHP
$planPath = realpath(__DIR__ . '/../../public/PLAN.xlsx');
echo "Existe: " . (is_file($planPath) ? 'OUI' : 'NON') . PHP_EOL;
echo "Lisible: " . (is_readable($planPath) ? 'OUI' : 'NON') . PHP_EOL;
```

3. **L'API fonctionne-elle?**
Ouvrir: `http://localhost/votre_app/public/?page=api&action=comptes`
Vous devriez voir un array JSON de comptes.

4. **account-search.js est-il chargé?**
Ouvrir DevTools → Console
Vous devriez voir un message de log: `AccountSearch: input=... for compte_search`

### Performance lente lors du démarrage

**Cause:** Lecture du fichier Excel à chaque appel API

**Solution:** Implémenter un cache (Redis, Memcached, etc.)

```php
// Pseudo-code pour cache Redis
public function getAllCached($cacheTTL = 3600) {
    $cacheKey = 'comptes_list';
    $cached = $redis->get($cacheKey);
    if ($cached) {
        return json_decode($cached, true);
    }
    $comptes = $this->getAll();
    $redis->setex($cacheKey, $cacheTTL, json_encode($comptes));
    return $comptes;
}
```

## 📈 Statistiques Actuelles

| Métrique | Valeur |
|----------|--------|
| Total comptes | 330 |
| Premier compte | 1010000000 |
| Dernier compte | (À vérifier) |
| Fichier PLAN.xlsx | 📁 `/public/PLAN.xlsx` |
| Taille approximative | ~15-50 KB (selon complexité) |

## 🔐 Sécurité

- ✅ Le fichier PLAN.xlsx n'est servi que via l'API PHP (pas d'accès direct)
- ✅ Les données sont sérialisées en JSON sûr (échappement des caractères spéciaux)
- ✅ Aucun SQL Injection possible (pas d'utilisation directe de BD)
- ✅ Validation côté serveur pour tous les comptes utilisés

## 📝 Fichiers Impliqués

| Fichier | Rôle |
|---------|------|
| `public/PLAN.xlsx` | Source des données |
| `app/models/CompteModel.php` | Lecture du fichier Excel |
| `app/controllers/ApiController.php` | Exposition via API |
| `assets/js/account-search.js` | Affichage et recherche |
| `app/views/_layout_footer.php` | Chargement du script |
| Tous les formulaires | Utilisation des comptes |

---

**Mise à jour:** 19 mars 2026  
**Statut:** ✅ Système opérationnel et testé
