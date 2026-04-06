<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include __DIR__ . '/../_layout_head.php'; ?>
    <title>Copies du Bilan</title>
    <style>
        .copy-card {
            margin-bottom: 1rem;
            border-left: 4px solid #007bff;
        }
        .copy-card.archived {
            border-left-color: #6c757d;
            opacity: 0.7;
        }
        .status-badge {
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../navbar.php'; ?>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3">
                        <i class="fas fa-history"></i> Copies du Bilan
                    </h1>
                    <div>
                        <a href="?page=bilan" class="btn btn-primary">
                            <i class="fas fa-eye"></i> Voir Bilan en Cours
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

                <?php if (empty($copies)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Aucune copie périodique n'a encore été sauvegardée.
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($copies as $copy): ?>
                            <div class="col-md-6">
                                <div class="card copy-card <?php echo (($copy['status'] ?? null) === 'archived') ? 'archived' : ''; ?>">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0"><?php echo htmlspecialchars($copy['title']); ?></h5>
                                        <span class="badge <?php echo (($copy['status'] ?? null) === 'archived') ? 'bg-secondary' : 'bg-success'; ?> status-badge">
                                            <?php echo (($copy['status'] ?? null) === 'archived') ? 'Archivée' : 'Active'; ?>
                                        </span>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-2">
                                            <strong>Date:</strong> <?php echo htmlspecialchars($copy['date']); ?>
                                        </p>
                                        <p class="mb-2">
                                            <strong>Créée le:</strong> <?php echo isset($copy['created_at']) ? $copy['created_at']->toDateTime()->format('d/m/Y H:i') : 'N/A'; ?>
                                        </p>
                                        <p class="mb-3">
                                            <strong>Nombre de comptes:</strong> <?php echo count($copy['accounts'] ?? []); ?>
                                        </p>

                                        <div class="btn-group" role="group">
                                            <a href="?page=bilan&action=view_copy&id=<?php echo $copy['_id']; ?>"
                                               class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i> Voir
                                            </a>

                                            <?php if (($copy['status'] ?? null) !== 'archived'): ?>
                                                <a href="?page=bilan&action=archive_copy&id=<?php echo $copy['_id']; ?>"
                                                   class="btn btn-sm btn-warning"
                                                   onclick="return confirm('Êtes-vous sûr de vouloir archiver cette copie ? Elle ne pourra plus être supprimée.')">
                                                    <i class="fas fa-archive"></i> Archiver
                                                </a>

                                                <a href="?page=bilan&action=delete_copy&id=<?php echo $copy['_id']; ?>"
                                                   class="btn btn-sm btn-danger"
                                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette copie ?')">
                                                    <i class="fas fa-trash"></i> Supprimer
                                                </a>
                                            <?php else: ?>
                                                <a href="?page=bilan&action=restore_copy&id=<?php echo $copy['_id']; ?>"
                                                   class="btn btn-sm btn-success"
                                                   onclick="return confirm('Êtes-vous sûr de vouloir restaurer cette copie archivée ?')">
                                                    <i class="fas fa-undo"></i> Restaurer
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../_layout_footer.php'; ?>
</body>
</html>