<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include __DIR__ . '/../_layout_head.php'; ?>
    <title>Bilan - <?php echo htmlspecialchars($bilan['title']); ?></title>
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
        .negative-value {
            color: #dc3545;
            font-weight: 600;
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
                        <i class="fas fa-balance-scale"></i> <?php echo htmlspecialchars($bilan['title']); ?>
                    </h1>
                    <div>
                        <?php if ($type === 'current'): ?>
                            <button class="btn btn-success btn-action" data-bs-toggle="modal" data-bs-target="#savePeriodicModal">
                                <i class="fas fa-save"></i> Sauvegarder Copie Périodique
                            </button>
                            <a href="?page=bilan&amp;action=initial" class="btn btn-primary btn-action">
                                <i class="fas fa-edit"></i> Gérer Bilan Initial
                            </a>
                            <a href="?page=bilan&amp;action=copies" class="btn btn-info btn-action">
                                <i class="fas fa-history"></i> Voir Copies
                            </a>
                        <?php else: ?>
                            <!-- Export PDF Button pour copies et bilan initial -->
                            <a class="btn btn-export-pdf d-none d-md-inline-flex btn-action"
                               href="?page=bilan&amp;action=export&amp;format=pdf&amp;type=<?php echo $type; ?><?php echo ($type === 'copy' && isset($bilan['_id'])) ? '&amp;copy_id=' . $bilan['_id'] : ''; ?>"
                               title="Exporter PDF">
                                <i class="bi bi-file-earmark-pdf me-2"></i> Exporter PDF
                            </a>
                            <a class="btn btn-export-pdf-mobile d-md-none btn-action"
                               href="?page=bilan&amp;action=export&amp;format=pdf&amp;type=<?php echo $type; ?><?php echo ($type === 'copy' && isset($bilan['_id'])) ? '&amp;copy_id=' . $bilan['_id'] : ''; ?>"
                               title="Exporter PDF">
                                <i class="bi bi-file-earmark-pdf"></i>
                            </a>
                            <a href="?page=bilan" class="btn btn-secondary btn-action">
                                <i class="fas fa-arrow-left"></i> Retour
                            </a>
                        <?php endif; ?>
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
                                <!-- Actif Immobilisé -->
                                <?php if (!empty($structure['actif']['immobilise']['accounts'])): ?>
                                <div class="bilan-section">
                                    <h5 class="text-primary">Actif Immobilisé</h5>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Compte</th>
                                                    <th>Débit</th>
                                                    <th>Crédit</th>
                                                    <th>Solde</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($structure['actif']['immobilise']['accounts'] as $account): ?>
                                                    <tr class="account-row">
                                                        <td><?php echo htmlspecialchars($account['code'] . ' - ' . $account['name']); ?></td>
                                                        <td><?php echo formatValue($account['debit'] ?? 0); ?></td>
                                                        <td><?php echo formatValue($account['credit'] ?? 0); ?></td>
                                                        <td><?php echo formatValue($account['solde'] ?? 0); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                <tr class="total-row">
                                                    <td><strong>Total Actif Immobilisé</strong></td>
                                                    <td colspan="3"><strong><?php echo formatValue($structure['actif']['immobilise']['total']); ?></strong></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <!-- Actif Circulant -->
                                <?php if (!empty($structure['actif']['circulant']['accounts'])): ?>
                                <div class="bilan-section">
                                    <h5 class="text-primary">Actif Circulant</h5>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Compte</th>
                                                    <th>Débit</th>
                                                    <th>Crédit</th>
                                                    <th>Solde</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($structure['actif']['circulant']['accounts'] as $account): ?>
                                                    <tr class="account-row">
                                                        <td><?php echo htmlspecialchars($account['code'] . ' - ' . $account['name']); ?></td>
                                                        <td><?php echo formatValue($account['debit'] ?? 0); ?></td>
                                                        <td><?php echo formatValue($account['credit'] ?? 0); ?></td>
                                                        <td><?php echo formatValue($account['solde'] ?? 0); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                <tr class="total-row">
                                                    <td><strong>Total Actif Circulant</strong></td>
                                                    <td colspan="3"><strong><?php echo formatValue($structure['actif']['circulant']['total']); ?></strong></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <!-- Trésorerie Actif -->
                                <?php if (!empty($structure['actif']['tresorerie']['accounts'])): ?>
                                <div class="bilan-section">
                                    <h5 class="text-primary">Trésorerie Actif</h5>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Compte</th>
                                                    <th>Débit</th>
                                                    <th>Crédit</th>
                                                    <th>Solde</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($structure['actif']['tresorerie']['accounts'] as $account): ?>
                                                    <tr class="account-row">
                                                        <td><?php echo htmlspecialchars($account['code'] . ' - ' . $account['name']); ?></td>
                                                        <td><?php echo formatValue($account['debit'] ?? 0); ?></td>
                                                        <td><?php echo formatValue($account['credit'] ?? 0); ?></td>
                                                        <td><?php echo formatValue($account['solde'] ?? 0); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                <tr class="total-row">
                                                    <td><strong>Total Trésorerie Actif</strong></td>
                                                    <td colspan="3"><strong><?php echo formatValue($structure['actif']['tresorerie']['total']); ?></strong></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <?php endif; ?>

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
                                <?php if (!empty($structure['passif']['capitaux_propres']['accounts'])): ?>
                                <div class="bilan-section">
                                    <h5 class="text-success">Capitaux Propres</h5>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Compte</th>
                                                    <th>Débit</th>
                                                    <th>Crédit</th>
                                                    <th>Solde</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($structure['passif']['capitaux_propres']['accounts'] as $account): ?>
                                                    <tr class="account-row">
                                                        <td><?php echo htmlspecialchars($account['code'] . ' - ' . $account['name']); ?></td>
                                                        <td><?php echo formatValue($account['debit'] ?? 0); ?></td>
                                                        <td><?php echo formatValue($account['credit'] ?? 0); ?></td>
                                                        <td><?php echo formatValue($account['solde'] ?? 0); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                <tr class="total-row">
                                                    <td><strong>Total Capitaux Propres</strong></td>
                                                    <td colspan="3"><strong><?php echo formatValue($structure['passif']['capitaux_propres']['total']); ?></strong></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <!-- Passif Non Courant -->
                                <?php if (!empty($structure['passif']['non_courant']['accounts'])): ?>
                                <div class="bilan-section">
                                    <h5 class="text-warning">Passif Non Courant</h5>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Compte</th>
                                                    <th>Débit</th>
                                                    <th>Crédit</th>
                                                    <th>Solde</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($structure['passif']['non_courant']['accounts'] as $account): ?>
                                                    <tr class="account-row">
                                                        <td><?php echo htmlspecialchars($account['code'] . ' - ' . $account['name']); ?></td>
                                                        <td><?php echo formatValue($account['debit'] ?? 0); ?></td>
                                                        <td><?php echo formatValue($account['credit'] ?? 0); ?></td>
                                                        <td><?php echo formatValue($account['solde'] ?? 0); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                <tr class="total-row">
                                                    <td><strong>Total Passif Non Courant</strong></td>
                                                    <td colspan="3"><strong><?php echo formatValue($structure['passif']['non_courant']['total']); ?></strong></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <!-- Passif Circulant -->
                                <?php if (!empty($structure['passif']['circulant']['accounts'])): ?>
                                <div class="bilan-section">
                                    <h5 class="text-info">Passif Circulant</h5>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Compte</th>
                                                    <th>Débit</th>
                                                    <th>Crédit</th>
                                                    <th>Solde</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($structure['passif']['circulant']['accounts'] as $account): ?>
                                                    <tr class="account-row">
                                                        <td><?php echo htmlspecialchars($account['code'] . ' - ' . $account['name']); ?></td>
                                                        <td><?php echo formatValue($account['debit'] ?? 0); ?></td>
                                                        <td><?php echo formatValue($account['credit'] ?? 0); ?></td>
                                                        <td><?php echo formatValue($account['solde'] ?? 0); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                <tr class="total-row">
                                                    <td><strong>Total Passif Circulant</strong></td>
                                                    <td colspan="3"><strong><?php echo formatValue($structure['passif']['circulant']['total']); ?></strong></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <!-- Trésorerie Passif -->
                                <?php if (!empty($structure['passif']['tresorerie']['accounts'])): ?>
                                <div class="bilan-section">
                                    <h5 class="text-danger">Trésorerie Passif</h5>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Compte</th>
                                                    <th>Débit</th>
                                                    <th>Crédit</th>
                                                    <th>Solde</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($structure['passif']['tresorerie']['accounts'] as $account): ?>
                                                    <tr class="account-row">
                                                        <td><?php echo htmlspecialchars($account['code'] . ' - ' . $account['name']); ?></td>
                                                        <td><?php echo formatValue($account['debit'] ?? 0); ?></td>
                                                        <td><?php echo formatValue($account['credit'] ?? 0); ?></td>
                                                        <td><?php echo formatValue($account['solde'] ?? 0); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                <tr class="total-row">
                                                    <td><strong>Total Trésorerie Passif</strong></td>
                                                    <td colspan="3"><strong><?php echo formatValue($structure['passif']['tresorerie']['total']); ?></strong></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <?php endif; ?>

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

    <!-- Modal for saving periodic copy -->
    <div class="modal fade" id="savePeriodicModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Sauvegarder Copie Périodique</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="?page=bilan&action=save_periodic">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="copy_title" class="form-label">Titre de la copie</label>
                            <input type="text" class="form-control" id="copy_title" name="copy_title"
                                   value="Copie Périodique - <?php echo date('d/m/Y H:i'); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="copy_date" class="form-label">Date</label>
                            <input type="date" class="form-control" id="copy_date" name="copy_date"
                                   value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-success">Sauvegarder</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../_layout_footer.php'; ?>
</body>
</html>