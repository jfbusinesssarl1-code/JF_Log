<style>
.users-section {
    --primary-color: #1a5490;
}

.user-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card-compact {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    border-left: 4px solid;
    transition: all 0.3s ease;
}

.stat-card-compact:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
}

.stat-card-compact.total { border-color: #1a5490; }
.stat-card-compact.admins { border-color: #dc3545; }
.stat-card-compact.managers { border-color: #ffc107; }
.stat-card-compact.others { border-color: #28a745; }

.stat-icon-compact {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
    margin-bottom: 16px;
}

.stat-icon-compact.total { background: linear-gradient(135deg, #1a5490, #2a7bc0); }
.stat-icon-compact.admins { background: linear-gradient(135deg, #dc3545, #c82333); }
.stat-icon-compact.managers { background: linear-gradient(135deg, #ffc107, #ff9800); }
.stat-icon-compact.others { background: linear-gradient(135deg, #28a745, #27ae60); }

.stat-value-compact {
    font-size: 32px;
    font-weight: 700;
    color: #333;
    line-height: 1;
}

.stat-label-compact {
    font-size: 14px;
    color: #666;
    margin-top: 8px;
}

.user-avatar-mini {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(135deg, #1a5490, #2a7bc0);
    color: white;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 18px;
}

.role-badge-modern {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.role-badge-modern.admin { background: linear-gradient(135deg, #dc3545, #c82333); color: white; }
.role-badge-modern.manager { background: linear-gradient(135deg, #ffc107, #ff9800); color: #000; }
.role-badge-modern.accountant { background: linear-gradient(135deg, #17a2b8, #138496); color: white; }
.role-badge-modern.caissier { background: linear-gradient(135deg, #6f42c1, #5a32a3); color: white; }
.role-badge-modern.stock_manager { background: linear-gradient(135deg, #e83e8c, #bd2d87); color: white; }
.role-badge-modern.user { background: linear-gradient(135deg, #28a745, #218838); color: white; }

.modern-card {
    background: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    margin-bottom: 30px;
}

.modern-card-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 2px solid #f0f0f0;
}

.modern-card-header i {
    font-size: 28px;
    color: var(--primary-color);
}

.modern-card-header h5 {
    margin: 0;
    font-weight: 600;
}
</style>

<div class="users-section">
    <div class="section-header">
        <h1><i class="bi bi-people-fill"></i> Gestion des Utilisateurs</h1>
        <p>Administrez les comptes utilisateurs et leurs rôles</p>
    </div>

    <?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <!-- Statistiques -->
    <?php
    $totalUsers = count($users ?? []);
    $adminCount = 0;
    $managerCount = 0;
    $otherCount = 0;
    
    foreach ($users ?? [] as $u) {
        $role = $u['role'] ?? 'user';
        if ($role === 'admin') $adminCount++;
        elseif ($role === 'manager') $managerCount++;
        else $otherCount++;
    }
    ?>
    
    <div class="user-stats">
        <div class="stat-card-compact total">
            <div class="stat-icon-compact total"><i class="bi bi-people"></i></div>
            <div class="stat-value-compact"><?= $totalUsers ?></div>
            <div class="stat-label-compact">Total Utilisateurs</div>
        </div>
        
        <div class="stat-card-compact admins">
            <div class="stat-icon-compact admins"><i class="bi bi-shield-fill-check"></i></div>
            <div class="stat-value-compact"><?= $adminCount ?></div>
            <div class="stat-label-compact">Administrateurs</div>
        </div>
        
        <div class="stat-card-compact managers">
            <div class="stat-icon-compact managers"><i class="bi bi-person-badge"></i></div>
            <div class="stat-value-compact"><?= $managerCount ?></div>
            <div class="stat-label-compact">Gestionnaires</div>
        </div>
        
        <div class="stat-card-compact others">
            <div class="stat-icon-compact others"><i class="bi bi-person-check"></i></div>
            <div class="stat-value-compact"><?= $otherCount ?></div>
            <div class="stat-label-compact">Autres</div>
        </div>
    </div>

    <!-- Liste des utilisateurs -->
    <div class="modern-card">
        <div class="modern-card-header">
            <i class="bi bi-list-ul"></i>
            <h5>Liste des utilisateurs (<?= $totalUsers ?>)</h5>
        </div>
        
        <?php if (!empty($users)): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th style="width: 60px;"></th>
                        <th>Utilisateur</th>
                        <th style="width: 150px;">Rôle</th>
                        <th style="width: 150px;">Créé le</th>
                        <th style="width: 200px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): 
                        $username = htmlspecialchars($user['username']);
                        $role = $user['role'] ?? 'user';
                        $initials = strtoupper(substr($username, 0, 2));
                    ?>
                    <tr>
                        <td>
                            <div class="user-avatar-mini"><?= $initials ?></div>
                        </td>
                        <td>
                            <strong><?= $username ?></strong><br>
                            <small class="text-muted">ID: <?= htmlspecialchars((string)$user['_id']) ?></small>
                        </td>
                        <td>
                            <span class="role-badge-modern <?= $role ?>"><?= htmlspecialchars($role) ?></span>
                        </td>
                        <td class="text-muted small">
                            <i class="bi bi-calendar3"></i> <?= htmlspecialchars($user['created_at'] ?? 'N/A') ?>
                        </td>
                        <td>
                            <a href="?page=admin&section=users&action=edit&user_id=<?= urlencode((string)$user['_id']) ?>"
                               class="btn btn-sm btn-warning me-1">
                                <i class="bi bi-pencil-fill"></i> Modifier
                            </a>
                            <a href="?page=admin&section=users&action=delete&user_id=<?= urlencode((string)$user['_id']) ?>&token=<?= urlencode(\App\Core\Csrf::getToken()) ?>"
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('⚠️ Supprimer cet utilisateur ?')">
                                <i class="bi bi-trash-fill"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="text-center py-5">
            <i class="bi bi-people" style="font-size: 80px; color: #ddd;"></i>
            <p class="text-muted mt-3">Aucun utilisateur</p>
        </div>
        <?php endif; ?>
</div>

    <!-- Formulaire -->
    <div class="modern-card">
        <div class="modern-card-header">
            <i class="bi bi-<?= !empty($editUser) ? 'pencil-square' : 'person-plus-fill' ?>"></i>
            <h5><?= !empty($editUser) ? 'Modifier l\'utilisateur' : 'Créer un nouvel utilisateur' ?></h5>
        </div>
        <form method="post" action="?page=admin&section=users" onsubmit="return validateRegisterForm();" novalidate>
            <input type="hidden" name="csrf_token" value="<?= \App\Core\Csrf::generateToken() ?>">
            
            <?php if (!empty($editUser)): ?>
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="user_id" value="<?= htmlspecialchars($userId) ?>">
            <?php endif; ?>

            <div class="row g-3 mb-3">
                <?php if (empty($editUser)): ?>
                <div class="col-md-6">
                    <label for="username" class="form-label fw-semibold">
                        <i class="bi bi-person"></i> Nom d'utilisateur <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control" id="username" name="username" required 
                           minlength="3" maxlength="32" pattern="[a-zA-Z0-9_]+" placeholder="ex: jdupont">
                    <small class="text-muted">3-32 caractères, lettres, chiffres et _ uniquement</small>
                </div>
                <?php else: ?>
                <div class="col-md-6">
                    <label class="form-label fw-semibold"><i class="bi bi-person"></i> Nom d'utilisateur</label>
                    <input type="text" class="form-control" disabled value="<?= htmlspecialchars($editUser['username']) ?>">
                    <small class="text-muted">Le nom d'utilisateur ne peut pas être modifié</small>
                </div>
                <?php endif; ?>

                <div class="col-md-6">
                    <label for="password" class="form-label fw-semibold">
                        <i class="bi bi-key"></i> Mot de passe 
                        <?= !empty($editUser) ? '' : '<span class="text-danger">*</span>' ?>
                    </label>
                    <input type="password" class="form-control" id="password" name="password"
                           <?= empty($editUser) ? 'required' : '' ?> minlength="6" placeholder="••••••••">
                    <small class="text-muted">
                        <?= !empty($editUser) ? 'Laisser vide pour ne pas changer' : 'Minimum 6 caractères' ?>
                    </small>
                </div>
            </div>

            <div class="mb-4">
                <label for="role" class="form-label fw-semibold">
                    <i class="bi bi-shield-check"></i> Rôle <span class="text-danger">*</span>
                </label>
                <select class="form-select" id="role" name="role" required>
                    <option value="user" <?= ($editUser['role'] ?? '') === 'user' ? 'selected' : '' ?>>👤 Utilisateur</option>
                    <option value="caissier" <?= ($editUser['role'] ?? '') === 'caissier' ? 'selected' : '' ?>>💰 Caissier</option>
                    <option value="accountant" <?= ($editUser['role'] ?? '') === 'accountant' ? 'selected' : '' ?>>📊 Comptable</option>
                    <option value="stock_manager" <?= ($editUser['role'] ?? '') === 'stock_manager' ? 'selected' : '' ?>>📦 Gestionnaire Stock</option>
                    <option value="manager" <?= ($editUser['role'] ?? '') === 'manager' ? 'selected' : '' ?>>👔 Gestionnaire</option>
                    <option value="admin" <?= ($editUser['role'] ?? '') === 'admin' ? 'selected' : '' ?>>🛡️ Administrateur</option>
                </select>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-lg px-4">
                    <i class="bi bi-<?= !empty($editUser) ? 'check-circle' : 'plus-circle' ?>"></i>
                    <?= !empty($editUser) ? 'Enregistrer' : 'Créer' ?>
                </button>
                <?php if (!empty($editUser)): ?>
                <a href="?page=admin&section=users" class="btn btn-secondary btn-lg px-4">
                    <i class="bi bi-x-circle"></i> Annuler
                </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<script>
function validateRegisterForm() {
    const usernameField = document.getElementById('username');
    const passwordField = document.getElementById('password');
    
    // Si on est en édition, le username n'est pas visible
    if (!usernameField) {
        return true;
    }
    
    const username = usernameField.value.trim();
    const password = passwordField.value;
    let errors = [];
    
    if (username.length < 3 || username.length > 32 || !/^[a-zA-Z0-9_]+$/.test(username)) {
        errors.push("Nom d'utilisateur invalide (3-32 caractères, lettres/chiffres/_ uniquement)");
    }
    if (password.length < 6) {
        errors.push("Mot de passe trop court (min. 6 caractères)");
    }
    
    if (errors.length) {
        alert(errors.join('\n'));
        return false;
    }
    return true;
}
</script>
