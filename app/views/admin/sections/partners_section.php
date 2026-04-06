<style>
.partners-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 24px;
    margin-top: 30px;
}

.partner-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    text-align: center;
}

.partner-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
}

.partner-logo-container {
    height: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 16px;
}

.partner-logo-container img {
    max-width: 100%;
    max-height: 100px;
    object-fit: contain;
}

.partner-name {
    font-size: 18px;
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
}

.partner-link {
    font-size: 13px;
    color: #666;
    margin-bottom: 16px;
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.partner-actions {
    display: flex;
    gap: 8px;
}

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
    color: #1a5490;
}

.modern-card-header h5 {
    margin: 0;
    font-weight: 600;
}
</style>

<div class="section-header">
    <h1><i class="bi bi-handshake-fill"></i> Gestion des Partenaires</h1>
    <p>Gérez les logos et informations de vos partenaires</p>
</div>

<?php if (!empty($success)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="modern-card">
    <div class="modern-card-header">
        <i class="bi bi-<?php echo !empty($editPartner) ? 'pencil-square' : 'plus-circle-fill'; ?>"></i>
        <h5><?php echo !empty($editPartner) ? 'Modifier le partenaire' : 'Ajouter un nouveau partenaire'; ?></h5>
    </div>
    
    <form method="post" enctype="multipart/form-data" action="?page=admin&section=partners">
        <input type="hidden" name="csrf_token" value="<?= \App\Core\Csrf::generateToken() ?>">
        <?php if (!empty($editPartnerId)): ?>
            <input type="hidden" name="partner_id" value="<?= htmlspecialchars($editPartnerId) ?>">
        <?php endif; ?>
        
        <div class="row g-3 mb-3">
            <div class="col-md-5">
                <label for="partnerName" class="form-label fw-semibold">
                    <i class="bi bi-building"></i> Nom du partenaire <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" id="partnerName" name="partnerName" 
                       placeholder="Ex: Entreprise ABC" 
                       value="<?= htmlspecialchars($editPartner['name'] ?? '') ?>" 
                       required>
            </div>
            <div class="col-md-7">
                <label for="partnerLink" class="form-label fw-semibold">
                    <i class="bi bi-link-45deg"></i> Site web (optionnel)
                </label>
                <input type="url" class="form-control" id="partnerLink" name="partnerLink" 
                       placeholder="https://exemple.com" 
                       value="<?= htmlspecialchars($editPartner['link'] ?? '') ?>">
            </div>
        </div>

        <div class="mb-4">
            <label for="partnerLogo" class="form-label fw-semibold">
                <i class="bi bi-image"></i> Logo
                <?php if (!empty($editPartner)): ?>
                    <small class="text-muted">(laisser vide pour conserver l'actuel)</small>
                <?php endif; ?>
            </label>
            <input type="file" class="form-control" id="partnerLogo" name="partnerLogo" accept="image/*">
            <small class="text-muted">Format recommandé: PNG avec fond transparent - Max 2 Mo</small>
            
            <?php if (!empty($editPartner['logo'])): ?>
                <div class="mt-3 p-3 bg-light rounded">
                    <small class="text-muted d-block mb-2">Logo actuel:</small>
                    <img src="<?= htmlspecialchars($editPartner['logo']) ?>" 
                         alt="Logo actuel" 
                         style="max-width: 150px; max-height: 80px; object-fit: contain;">
                </div>
            <?php endif; ?>
        </div>
        
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-lg px-4">
                <i class="bi bi-<?php echo !empty($editPartner) ? 'check-circle' : 'plus-circle'; ?>"></i>
                <?php echo !empty($editPartner) ? 'Mettre à jour' : 'Ajouter'; ?>
            </button>
            <?php if (!empty($editPartner)): ?>
                <a href="?page=admin&section=partners" class="btn btn-secondary btn-lg px-4">
                    <i class="bi bi-x-circle"></i> Annuler
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="modern-card">
    <div class="modern-card-header">
        <i class="bi bi-grid-3x3-gap-fill"></i>
        <h5>Partenaires existants (<?php echo count($partners ?? []); ?>)</h5>
    </div>
    
    <?php if (!empty($partners) && is_array($partners)): ?>
        <div class="partners-grid">
            <?php foreach ($partners as $partner): ?>
                <div class="partner-card">
                    <div class="partner-logo-container">
                        <?php if (!empty($partner['logo'])): ?>
                            <img src="<?= htmlspecialchars($partner['logo']) ?>" 
                                 alt="<?= htmlspecialchars($partner['name'] ?? 'Partner') ?>">
                        <?php else: ?>
                            <i class="bi bi-building" style="font-size: 48px; color: #ccc;"></i>
                        <?php endif; ?>
                    </div>
                    
                    <div class="partner-name"><?= htmlspecialchars($partner['name'] ?? 'N/A') ?></div>
                    
                    <?php if (!empty($partner['link'])): ?>
                        <a href="<?= htmlspecialchars($partner['link']) ?>" 
                           target="_blank" 
                           rel="noopener" 
                           class="partner-link">
                            <i class="bi bi-link-45deg"></i> <?= htmlspecialchars($partner['link']) ?>
                        </a>
                    <?php else: ?>
                        <div class="partner-link text-muted">-</div>
                    <?php endif; ?>
                    
                    <div class="partner-actions">
                        <a href="?page=admin&section=partners&action=edit&partner_id=<?= $partner['_id'] ?>" 
                           class="btn btn-warning flex-fill">
                            <i class="bi bi-pencil-fill"></i> Modifier
                        </a>
                        <a href="?page=admin&section=partners&action=delete&partner_id=<?= $partner['_id'] ?>&token=<?= \App\Core\Csrf::getToken() ?>" 
                           class="btn btn-danger flex-fill" 
                           onclick="return confirm('⚠️ Supprimer ce partenaire ?')">
                            <i class="bi bi-trash-fill"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="bi bi-handshake" style="font-size: 80px; color: #ddd;"></i>
            <p class="text-muted mt-3">Aucun partenaire pour le moment</p>
            <p class="text-muted" style="font-size: 14px;">Ajoutez  vos premiers partenaires institutionnels</p>
        </div>
    <?php endif; ?>
</div>

<?php if (!empty($editPartner)): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.modern-card form');
    if (form) {
        form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
});
</script>
<?php endif; ?>
