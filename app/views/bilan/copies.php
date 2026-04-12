<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include __DIR__ . '/../_layout_head.php'; ?>
    <title>Copies du Bilan</title>
    <style>
        .copy-card {
            margin-bottom: 1rem;
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 1rem;
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .copy-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 18px 30px rgba(0, 0, 0, 0.08);
        }
        .copy-card.archived {
            border-color: #ced4da;
            opacity: 0.95;
            filter: grayscale(0.03);
        }
        .copy-card .card-header {
            background: #2f6ce5;
            color: white;
            border-bottom: 0;
            border-top-left-radius: 1rem;
            border-top-right-radius: 1rem;
            padding: 1rem 1.25rem;
        }
        .copy-card .card-header h5 {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 700;
        }
        .copy-card .card-body {
            padding: 1.25rem;
        }
        .copy-meta {
            font-size: 0.88rem;
            color: #6c757d;
            margin-bottom: 1rem;
            line-height: 1.6;
        }
        .copy-meta span {
            display: inline-flex;
            align-items: center;
            margin-right: 1rem;
        }
        .copy-meta span i {
            margin-right: 0.4rem;
            font-size: 0.95rem;
        }
        .copy-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }
        .copy-actions .btn {
            min-width: 120px;
            flex: 1 1 calc(50% - 0.75rem);
            white-space: nowrap;
        }
        .copy-actions .btn i {
            margin-right: 0.5rem;
        }
        .status-badge {
            font-size: 0.78rem;
            padding: 0.45rem 0.8rem;
            border-radius: 999px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .page-summary .card {
            border-radius: 1rem;
        }
        .page-summary .card-body {
            padding: 1rem 1.25rem;
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
                    <?php
                        $totalCopies = count($copies);
                        $activeCopies = 0;
                        $archivedCopies = 0;
                        foreach ($copies as $copy) {
                            if ((($copy['status'] ?? null) === 'archived')) {
                                $archivedCopies++;
                            } else {
                                $activeCopies++;
                            }
                        }
                    ?>
                    <div class="row page-summary g-3 mb-4">
                        <div class="col-md-4">
                            <div class="card border-primary">
                                <div class="card-body text-primary">
                                    <h6 class="text-uppercase text-muted mb-2">Total des copies</h6>
                                    <div class="h3 mb-0"><?php echo $totalCopies; ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-success">
                                <div class="card-body text-success">
                                    <h6 class="text-uppercase text-muted mb-2">Actives</h6>
                                    <div class="h3 mb-0"><?php echo $activeCopies; ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-secondary">
                                <div class="card-body text-secondary">
                                    <h6 class="text-uppercase text-muted mb-2">Archivées</h6>
                                    <div class="h3 mb-0"><?php echo $archivedCopies; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row g-3">
                        <?php foreach ($copies as $copy): ?>
                            <div class="col-md-6 col-xl-4">
                                <div class="card copy-card <?php echo (($copy['status'] ?? null) === 'archived') ? 'archived' : ''; ?>">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5><?php echo htmlspecialchars($copy['title']); ?></h5>
                                            <div class="copy-meta">
                                                <span><i class="fas fa-calendar-day"></i> <?php echo htmlspecialchars($copy['date']); ?></span>
                                                <span><i class="fas fa-clock"></i> <?php echo isset($copy['created_at']) ? $copy['created_at']->toDateTime()->format('d/m/Y H:i') : 'N/A'; ?></span>
                                            </div>
                                        </div>
                                        <span class="badge <?php echo (($copy['status'] ?? null) === 'archived') ? 'bg-secondary' : 'bg-success'; ?> status-badge">
                                            <?php echo (($copy['status'] ?? null) === 'archived') ? 'Archivée' : 'Active'; ?>
                                        </span>
                                    </div>
                                    <div class="card-body">
                                        <p class="copy-meta mb-3">
                                            <strong>Comptes :</strong> <?php echo count($copy['accounts'] ?? []); ?>
                                        </p>

                                        <div class="copy-actions">
                                            <a href="?page=bilan&amp;action=view_copy&amp;id=<?php echo $copy['_id']; ?>"
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> Voir
                                            </a>
                                            <a href="?page=bilan&amp;action=export&amp;format=pdf&amp;type=copy&amp;copy_id=<?php echo $copy['_id']; ?>"
                                               class="btn btn-sm btn-outline-info"
                                               title="Exporter PDF">
                                                <i class="fas fa-file-pdf"></i> PDF
                                            </a>
                                            <?php if (($copy['status'] ?? null) !== 'archived'): ?>
                                                <a href="?page=bilan&amp;action=archive_copy&amp;id=<?php echo $copy['_id']; ?>"
                                                   class="btn btn-sm btn-outline-warning"
                                                   onclick="return confirm('Êtes-vous sûr de vouloir archiver cette copie ? Elle ne pourra plus être supprimée.')">
                                                    <i class="fas fa-archive"></i> Archiver
                                                </a>
                                                <a href="?page=bilan&amp;action=delete_copy&amp;id=<?php echo $copy['_id']; ?>"
                                                   class="btn btn-sm btn-outline-danger"
                                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette copie ?')">
                                                    <i class="fas fa-trash"></i> Supprimer
                                                </a>
                                            <?php else: ?>
                                                <a href="?page=bilan&amp;action=restore_copy&amp;id=<?php echo $copy['_id']; ?>"
                                                   class="btn btn-sm btn-outline-success"
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