<?php
// Affiche le détail d'un service
use App\Helpers\AssetHelper;
?>
<section class="section">
  <div class="container">
    <div class="section-title">
      <h2><?php echo htmlspecialchars($service['name'] ?? 'Service'); ?></h2>
      <p>Détails du service</p>
    </div>

    <?php if (empty($service)): ?>
      <p class="text-muted">Service introuvable.</p>
      <p><a href="?page=home#services" class="btn btn-secondary">Retour aux services</a></p>
    <?php else: ?>
      <div class="card p-4">
        <div class="row">
          <div class="col-md-2">
            <?php if (!empty($service['icon'])): ?>
              <img src="<?php echo AssetHelper::url($service['icon']); ?>"
                alt="<?php echo htmlspecialchars($service['name']); ?>" style="max-width:100%;">
            <?php else: ?>
              <i class="bi bi-briefcase" style="font-size:48px;"></i>
            <?php endif; ?>
          </div>
          <div class="col-md-10">
            <h4><?php echo htmlspecialchars($service['name']); ?></h4>
            <div><?php echo nl2br(htmlspecialchars($service['description'] ?? '')); ?></div>
            <p class="mt-3"><a href="?page=home#services" class="btn btn-secondary">Retour</a></p>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>