<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include __DIR__ . '/../_layout_head.php'; ?>
    <title>Gestion du Bilan Initial</title>
    <style>
        .bilan-section {
            margin-bottom: 2rem;
        }
        .bilan-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        .account-row {
            padding: 0.5rem;
            border-bottom: 1px solid #eee;
        }
        .account-row:hover {
            background-color: #f8f9fa;
        }
        .total-row {
            background-color: #e9ecef;
            font-weight: bold;
            padding: 1rem;
            border-top: 2px solid #dee2e6;
        }
        .grand-total {
            background-color: #007bff;
            color: white;
            font-size: 1.2rem;
            padding: 1rem;
            margin-top: 1rem;
        }
        .btn-action {
            margin: 0.25rem;
        }
        .btn-xs {
            padding: 0.15rem 0.3rem;
            font-size: 0.75rem;
            line-height: 1.2;
            border-radius: 0.2rem;
        }
        .card {
            border: 1px solid #d8d8ec;
            border-radius: 12px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
        }
        .card-header {
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
        }
        .btn-warning {
            color: #fff;
            background-color: #ff8c00;
            border-color: #ff8c00;
        }
        .btn-warning:hover {
            color: #fff;
            background-color: #e77f00;
            border-color: #e77f00;
        }
        .negative-value {
            color: #dc3545;
            font-weight: 600;
        }
        .bilan-section table th:nth-child(2),
        .bilan-section table th:nth-child(3),
        .bilan-section table td:nth-child(2),
        .bilan-section table td:nth-child(3) {
            text-align: left;
            white-space: nowrap;
            width: 1%;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../navbar.php'; ?>

    <?php
    function formatValue($value) {
        if ($value < 0) {
            return '<span class="negative-value">- ' . number_format(abs($value), 2) . '</span>';
        }
        return number_format($value, 2);
    }
    ?>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3">
                        <i class="fas fa-edit"></i> Gestion du Bilan Initial
                    </h1>
                    <div>
                        <button id="openAddAccountButton" class="btn btn-success btn-action" data-bs-toggle="modal" data-bs-target="#addAccountModal">
                            <i class="fas fa-plus"></i> Ajouter un Compte
                        </button>
                        <a href="?page=bilan" class="btn btn-primary btn-action">
                            <i class="fas fa-eye"></i> Voir Bilan en Cours
                        </a>
                        <!-- Export PDF Button -->
                        <a class="btn btn-export-pdf d-none d-md-inline-flex btn-action"
                           href="?page=bilan&amp;action=export&amp;format=pdf&amp;type=initial"
                           title="Exporter PDF">
                            <i class="bi bi-file-earmark-pdf me-2"></i> Exporter PDF
                        </a>
                        <a class="btn btn-export-pdf-mobile d-md-none btn-action"
                           href="?page=bilan&amp;action=export&amp;format=pdf&amp;type=initial"
                           title="Exporter PDF">
                            <i class="bi bi-file-earmark-pdf"></i>
                        </a>
                    </div>
                </div>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($_SESSION['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <div class="row">
                    <!-- ACTIF -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bilan-header">
                                <h4 class="mb-0"><i class="fas fa-plus-circle"></i> ACTIF</h4>
                            </div>
                            <div class="card-body">
                                <!-- Actif ImmobilisÃ© -->
                                <div class="bilan-section">
                                    <h5 class="text-primary">Actif ImmobilisÃ©</h5>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Compte</th>
                                                    <th>Valeur</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($structure['actif']['immobilise']['accounts'] as $account): ?>
                                                    <tr class="account-row">
                                                        <td><?php echo htmlspecialchars($account['code'] . ' - ' . $account['name']); ?></td>
                                                        <td><?php echo formatValue($account['value'] ?? 0); ?></td>
                                                        <td>
                                                            <a href="?page=bilan&amp;action=edit_account&amp;code=<?php echo urlencode($account['code']); ?>" class="btn btn-xs btn-warning" title="Modifier le compte">
                                                                <i class="fas fa-edit"></i> Modifier
                                                            </a>
                                                            <a href="?page=bilan&amp;action=remove_account&amp;code=<?php echo urlencode($account['code']); ?>"
                                                               class="btn btn-xs btn-danger"
                                                               onclick="return confirm('ÃŠtes-vous sÃ»r de vouloir supprimer ce compte ?')">
                                                                <i class="fas fa-trash"></i> Supprimer
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                <tr class="total-row">
                                                    <td><strong>Total Actif ImmobilisÃ©</strong></td>
                                                    <td colspan="2"><strong><?php echo formatValue($structure['actif']['immobilise']['total']); ?></strong></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Actif Circulant -->
                                <div class="bilan-section">
                                    <h5 class="text-primary">Actif Circulant</h5>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Compte</th>
                                                    <th>Valeur</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($structure['actif']['circulant']['accounts'] as $account): ?>
                                                    <tr class="account-row">
                                                        <td><?php echo htmlspecialchars($account['code'] . ' - ' . $account['name']); ?></td>
                                                        <td><?php echo formatValue($account['value'] ?? 0); ?></td>
                                                        <td>
                                                            <a href="?page=bilan&amp;action=edit_account&amp;code=<?php echo urlencode($account['code']); ?>" class="btn btn-xs btn-warning" title="Modifier le compte">
                                                                <i class="fas fa-edit"></i> Modifier
                                                            </a>
                                                            <a href="?page=bilan&amp;action=remove_account&amp;code=<?php echo urlencode($account['code']); ?>"
                                                               class="btn btn-xs btn-danger"
                                                               onclick="return confirm('ÃŠtes-vous sÃ»r de vouloir supprimer ce compte ?')">
                                                                <i class="fas fa-trash"></i> Supprimer
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                <tr class="total-row">
                                                    <td><strong>Total Actif Circulant</strong></td>
                                                    <td colspan="2"><strong><?php echo formatValue($structure['actif']['circulant']['total']); ?></strong></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- TrÃ©sorerie Actif -->
                                <div class="bilan-section">
                                    <h5 class="text-primary">TrÃ©sorerie Actif</h5>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Compte</th>
                                                    <th>Valeur</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($structure['actif']['tresorerie']['accounts'] as $account): ?>
                                                    <tr class="account-row">
                                                        <td><?php echo htmlspecialchars($account['code'] . ' - ' . $account['name']); ?></td>
                                                        <td><?php echo formatValue($account['value'] ?? 0); ?></td>
                                                        <td>
                                                            <a href="?page=bilan&amp;action=edit_account&amp;code=<?php echo urlencode($account['code']); ?>" class="btn btn-xs btn-warning" title="Modifier le compte">
                                                                <i class="fas fa-edit"></i> Modifier
                                                            </a>
                                                            <a href="?page=bilan&amp;action=remove_account&amp;code=<?php echo urlencode($account['code']); ?>"
                                                               class="btn btn-xs btn-danger"
                                                               onclick="return confirm('ÃŠtes-vous sÃ»r de vouloir supprimer ce compte ?')">
                                                                <i class="fas fa-trash"></i> Supprimer
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                <tr class="total-row">
                                                    <td><strong>Total TrÃ©sorerie Actif</strong></td>
                                                    <td colspan="2"><strong><?php echo formatValue($structure['actif']['tresorerie']['total']); ?></strong></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Total Actif -->
                                <div class="grand-total">
                                    <strong>TOTAL ACTIF: <?php echo formatValue($structure['actif']['total']); ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PASSIF -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bilan-header">
                                <h4 class="mb-0"><i class="fas fa-minus-circle"></i> PASSIF</h4>
                            </div>
                            <div class="card-body">
                                <!-- Capitaux Propres -->
                                <div class="bilan-section">
                                    <h5 class="text-success">Capitaux Propres</h5>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Compte</th>
                                                    <th>Valeur</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($structure['passif']['capitaux_propres']['accounts'] as $account): ?>
                                                    <tr class="account-row">
                                                        <td><?php echo htmlspecialchars($account['code'] . ' - ' . $account['name']); ?></td>
                                                        <td><?php echo formatValue($account['value'] ?? 0); ?></td>
                                                        <td>
                                                            <a href="?page=bilan&amp;action=edit_account&amp;code=<?php echo urlencode($account['code']); ?>" class="btn btn-xs btn-warning" title="Modifier le compte">
                                                                <i class="fas fa-edit"></i> Modifier
                                                            </a>
                                                            <a href="?page=bilan&amp;action=remove_account&amp;code=<?php echo urlencode($account['code']); ?>"
                                                               class="btn btn-xs btn-danger"
                                                               onclick="return confirm('ÃŠtes-vous sÃ»r de vouloir supprimer ce compte ?')">
                                                                <i class="fas fa-trash"></i> Supprimer
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                <tr class="total-row">
                                                    <td><strong>Total Capitaux Propres</strong></td>
                                                    <td colspan="2"><strong><?php echo formatValue($structure['passif']['capitaux_propres']['total']); ?></strong></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Passif Non Courant -->
                                <div class="bilan-section">
                                    <h5 class="text-warning">Passif Non Courant</h5>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Compte</th>
                                                    <th>Valeur</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($structure['passif']['non_courant']['accounts'] as $account): ?>
                                                    <tr class="account-row">
                                                        <td><?php echo htmlspecialchars($account['code'] . ' - ' . $account['name']); ?></td>
                                                        <td><?php echo formatValue($account['value'] ?? 0); ?></td>
                                                        <td>
                                                            <a href="?page=bilan&amp;action=edit_account&amp;code=<?php echo urlencode($account['code']); ?>" class="btn btn-xs btn-warning" title="Modifier le compte">
                                                                <i class="fas fa-edit"></i> Modifier
                                                            </a>
                                                            <a href="?page=bilan&amp;action=remove_account&amp;code=<?php echo urlencode($account['code']); ?>"
                                                               class="btn btn-xs btn-danger"
                                                               onclick="return confirm('ÃŠtes-vous sÃ»r de vouloir supprimer ce compte ?')">
                                                                <i class="fas fa-trash"></i> Supprimer
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                <tr class="total-row">
                                                    <td><strong>Total Passif Non Courant</strong></td>
                                                    <td colspan="2"><strong><?php echo formatValue($structure['passif']['non_courant']['total']); ?></strong></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Passif Circulant -->
                                <div class="bilan-section">
                                    <h5 class="text-info">Passif Circulant</h5>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Compte</th>
                                                    <th>Valeur</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($structure['passif']['circulant']['accounts'] as $account): ?>
                                                    <tr class="account-row">
                                                        <td><?php echo htmlspecialchars($account['code'] . ' - ' . $account['name']); ?></td>
                                                        <td><?php echo formatValue($account['value'] ?? 0); ?></td>
                                                        <td>
                                                            <a href="?page=bilan&amp;action=edit_account&amp;code=<?php echo urlencode($account['code']); ?>" class="btn btn-xs btn-warning" title="Modifier le compte">
                                                                <i class="fas fa-edit"></i> Modifier
                                                            </a>
                                                            <a href="?page=bilan&amp;action=remove_account&amp;code=<?php echo urlencode($account['code']); ?>"
                                                               class="btn btn-xs btn-danger"
                                                               onclick="return confirm('ÃŠtes-vous sÃ»r de vouloir supprimer ce compte ?')">
                                                                <i class="fas fa-trash"></i> Supprimer
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                <tr class="total-row">
                                                    <td><strong>Total Passif Circulant</strong></td>
                                                    <td colspan="2"><strong><?php echo formatValue($structure['passif']['circulant']['total']); ?></strong></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- TrÃ©sorerie Passif -->
                                <div class="bilan-section">
                                    <h5 class="text-danger">TrÃ©sorerie Passif</h5>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Compte</th>
                                                    <th>Valeur</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($structure['passif']['tresorerie']['accounts'] as $account): ?>
                                                    <tr class="account-row">
                                                        <td><?php echo htmlspecialchars($account['code'] . ' - ' . $account['name']); ?></td>
                                                        <td><?php echo formatValue($account['value'] ?? 0); ?></td>
                                                        <td>
                                                            <a href="?page=bilan&amp;action=edit_account&amp;code=<?php echo urlencode($account['code']); ?>" class="btn btn-xs btn-warning" title="Modifier le compte">
                                                                <i class="fas fa-edit"></i> Modifier
                                                            </a>
                                                            <a href="?page=bilan&amp;action=remove_account&amp;code=<?php echo urlencode($account['code']); ?>"
                                                               class="btn btn-xs btn-danger"
                                                               onclick="return confirm('ÃŠtes-vous sÃ»r de vouloir supprimer ce compte ?')">
                                                                <i class="fas fa-trash"></i> Supprimer
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                <tr class="total-row">
                                                    <td><strong>Total TrÃ©sorerie Passif</strong></td>
                                                    <td colspan="2"><strong><?php echo formatValue($structure['passif']['tresorerie']['total']); ?></strong></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Total Passif -->
                                <div class="grand-total">
                                    <strong>TOTAL PASSIF: <?php echo formatValue($structure['passif']['total']); ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for adding account -->
    <div class="modal fade" id="addAccountModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addAccountModalLabel">Ajouter un Compte au Bilan Initial</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="?page=bilan&amp;action=add_account" id="accountForm">
                    <input type="hidden" id="old_account_code" name="old_account_code" value="">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Titre du Bilan</label>
                                    <input type="text" class="form-control" id="title" name="title"
                                           value="<?php echo htmlspecialchars($bilan['title'] ?? 'Bilan Initial'); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date" class="form-label">Date d'Ã©laboration</label>
                                    <input type="date" class="form-control" id="date" name="date"
                                           value="<?php echo htmlspecialchars($bilan['date'] ?? date('Y-m-d')); ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="type" class="form-label">Type (ACTIF ou PASSIF)</label>
                            <select class="form-select" id="type" name="type" required onchange="updateCategories()">
                                <option value="">SÃ©lectionnez un type...</option>
                                <option value="actif">ACTIF</option>
                                <option value="passif">PASSIF</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="category" class="form-label">CatÃ©gorie</label>
                            <select class="form-select" id="category" name="category" required>
                                <option value="">SÃ©lectionnez d'abord un type...</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="account_code" class="form-label">Compte</label>
                            <select class="form-select" id="account_code" name="account_code" required>
                                <option value="">SÃ©lectionnez un compte...</option>
                                <?php
                                $compteModel = new \App\Models\CompteModel();
                                $comptes = $compteModel->getAll();
                                foreach ($comptes as $compte) {
                                    echo '<option value="' . htmlspecialchars($compte['code']) . '">' . htmlspecialchars($compte['label']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="value" class="form-label">Valeur</label>
                            <input type="number" step="0.01" class="form-control" id="value" name="value" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-success" id="submitAccountButton">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function updateCategories() {
            const type = document.getElementById('type').value;
            const categorySelect = document.getElementById('category');

            categorySelect.innerHTML = '';

            if (type === '') {
                const opt = document.createElement('option');
                opt.value = '';
                opt.textContent = 'SÃ©lectionnez d\'abord un type...';
                categorySelect.appendChild(opt);
                return;
            }

            const options = [{ value: '', text: 'SÃ©lectionnez une catÃ©gorie...' }];

            if (type === 'actif') {
                options.push({ value: 'actif_immobilise', text: 'ACTIF IMMOBILISÃ‰' });
                options.push({ value: 'stocks', text: 'STOCKS (Actif Circulant)' });
                options.push({ value: 'creances', text: 'CRÃ‰ANCES (Actif Circulant)' });
                options.push({ value: 'tresorerie_actif', text: 'TRÃ‰SORERIE ACTIF' });
            } else if (type === 'passif') {
                options.push({ value: 'capitaux_propres', text: 'CAPITAUX PROPRES' });
                options.push({ value: 'emprunts', text: 'EMPRUNTS (Passif Non Courant)' });
                options.push({ value: 'passif_circulant', text: 'FOURNISSEURS / DETTES (Passif Circulant)' });
                options.push({ value: 'tresorerie_passif', text: 'TRÃ‰SORERIE PASSIF' });
            }

            options.forEach(option => {
                const opt = document.createElement('option');
                opt.value = option.value;
                opt.textContent = option.text;
                categorySelect.appendChild(opt);
            });
        }

        function setModalForAdd() {
            const form = document.getElementById('accountForm');
            form.action = '?page=bilan&amp;action=add_account';
            document.getElementById('addAccountModalLabel').textContent = 'Ajouter un Compte au Bilan Initial';
            document.getElementById('submitAccountButton').textContent = 'Ajouter';
            document.getElementById('old_account_code').value = '';
            document.getElementById('type').value = '';
            updateCategories();
        }

        function setModalForEdit(account) {
            const form = document.getElementById('accountForm');
            form.action = '?page=bilan&amp;action=update_account';
            document.getElementById('addAccountModalLabel').textContent = 'Modifier un Compte du Bilan Initial';
            document.getElementById('submitAccountButton').textContent = 'Mettre Ã  jour';

            document.getElementById('old_account_code').value = account.code;
            document.getElementById('type').value = account.type || 'actif';
            updateCategories();
            document.getElementById('category').value = account.category;
            document.getElementById('account_code').value = account.code;
            document.getElementById('value').value = account.value;
        }

        function openEditModal(code, type, category, value) {
            setModalForEdit({ code, type, category, value });
            const modal = new bootstrap.Modal(document.getElementById('addAccountModal'));
            modal.show();
        }

        document.addEventListener('DOMContentLoaded', function() {
            const addAccountModal = document.getElementById('addAccountModal');
            addAccountModal.addEventListener('hidden.bs.modal', function() {
                const form = document.querySelector('#addAccountModal form');
                form.reset();
                setModalForAdd();
            });

            document.getElementById('openAddAccountButton').addEventListener('click', setModalForAdd);
            setModalForAdd();

            <?php if (!empty($selectedAccount)): ?>
                setModalForEdit({
                    code: '<?php echo htmlspecialchars($selectedAccount['code'], ENT_QUOTES); ?>',
                    type: '<?php echo htmlspecialchars($selectedAccount['type'] ?? 'actif', ENT_QUOTES); ?>',
                    category: '<?php echo htmlspecialchars($selectedAccount['category'] ?? '', ENT_QUOTES); ?>',
                    value: '<?php echo htmlspecialchars($selectedAccount['value'] ?? 0, ENT_QUOTES); ?>'
                });
                new bootstrap.Modal(document.getElementById('addAccountModal')).show();
            <?php endif; ?>
        });
    </script>

    <?php include __DIR__ . '/../_layout_footer.php'; ?>
</body>
</html>
