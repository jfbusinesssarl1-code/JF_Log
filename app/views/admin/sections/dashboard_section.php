<style>
.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 24px;
    margin-bottom: 30px;
}

.dashboard-card {
    background: white;
    border-radius: 16px;
    padding: 30px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    border-left: 5px solid;
}

.dashboard-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

.dashboard-card.primary {
    border-color: #1a5490;
}

.dashboard-card.success {
    border-color: #28a745;
}

.dashboard-card.warning {
    border-color: #ffc107;
}

.dashboard-card.info {
    border-color: #17a2b8;
}

.dashboard-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.dashboard-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    color: white;
}

.dashboard-icon.primary {
    background: linear-gradient(135deg, #1a5490, #2a7bc0);
}

.dashboard-icon.success {
    background: linear-gradient(135deg, #28a745, #27ae60);
}

.dashboard-icon.warning {
    background: linear-gradient(135deg, #ffc107, #f39c12);
}

.dashboard-icon.info {
    background: linear-gradient(135deg, #17a2b8, #3fc4d8);
}

.dashboard-title {
    font-size: 16px;
    color: #666;
    margin-bottom: 12px;
}

.dashboard-value {
    font-size: 36px;
    font-weight: 700;
    color: #333;
    line-height: 1;
}

.dashboard-subtitle {
    font-size: 14px;
    color: #999;
    margin-top: 8px;
}

.welcome-banner {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 16px;
    padding: 40px;
    color: white;
    margin-bottom: 30px;
    position: relative;
    overflow: hidden;
}

.welcome-banner::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 300px;
    height: 300px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
}

.welcome-title {
    font-size: 32px;
    font-weight: 700;
    margin-bottom: 12px;
    position: relative;
    z-index: 1;
}

.welcome-text {
    font-size: 16px;
    opacity: 0.9;
    position: relative;
    z-index: 1;
}

.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-top: 30px;
}

.quick-action-btn {
    background: white;
    color: #333;
    padding: 16px 24px;
    border-radius: 12px;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.quick-action-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
    color: #1a5490;
}

.quick-action-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    background: linear-gradient(135deg, #1a5490, #2a7bc0);
    color: white;    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}
</style>

<div class="section-header">
  <h1><i class="bi bi-house-door-fill"></i> Tableau de bord</h1>
  <p>Vue d'ensemble de l'administration CB.JF</p>
</div>

<div class="welcome-banner">
    <div class="welcome-title">
        👋 Bienvenue, <?= htmlspecialchars($_SESSION['user']['username'] ?? 'Admin') ?> !
    </div>
    <div class="welcome-text">
        Vous êtes connecté(e) en tant que <strong><?= htmlspecialchars($_SESSION['user']['role'] ?? 'admin') ?></strong>
    </div>
</div>

<div class="dashboard-grid">
    <div class="dashboard-card primary">
        <div class="dashboard-card-header">
            <div>
                <div class="dashboard-title">Gestion du site</div>
                <div class="dashboard-value">8</div>
            </div>
            <div class="dashboard-icon primary">
                <i class="bi bi-globe"></i>
            </div>
        </div>
        <div class="dashboard-subtitle">Sections configurables</div>
    </div>
    
    <div class="dashboard-card success">
        <div class="dashboard-card-header">
            <div>
                <div class="dashboard-title">Activités</div>
                <div class="dashboard-value">
                    <?php
                    try {
                        $activityModel = new \App\Models\ActivityModel();
                        echo count($activityModel->getAll());
                    } catch (Exception $e) {
                        echo '0';
                    }
                    ?>
                </div>
            </div>
            <div class="dashboard-icon success">
                <i class="bi bi-clock-history"></i>
            </div>
        </div>
        <div class="dashboard-subtitle">Projets actifs</div>
    </div>
    
    <div class="dashboard-card warning">
        <div class="dashboard-card-header">
            <div>
                <div class="dashboard-title">Services</div>
                <div class="dashboard-value">
                    <?php
                    try {
                        $serviceModel = new \App\Models\ServiceModel();
                        echo count($serviceModel->getAll());
                    } catch (Exception $e) {
                        echo '0';
                    }
                    ?>
                </div>
            </div>
            <div class="dashboard-icon warning">
                <i class="bi bi-briefcase-fill"></i>
            </div>
        </div>
        <div class="dashboard-subtitle">Services disponibles</div>
    </div>
    
    <div class="dashboard-card info">
        <div class="dashboard-card-header">
            <div>
                <div class="dashboard-title">Partenaires</div>
                <div class="dashboard-value">
                    <?php
                    try {
                        $partnerModel = new \App\Models\PartnerModel();
                        echo count($partnerModel->getAll());
                    } catch (Exception $e) {
                        echo '0';
                    }
                    ?>
                </div>
            </div>
            <div class="dashboard-icon info">
                <i class="bi bi-handshake-fill"></i>
            </div>
        </div>
        <div class="dashboard-subtitle">Partenaires actifs</div>
    </div>
</div>

<div class="modern-card" style="background: white; border-radius: 16px; padding: 30px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);">
    <h5 class="mb-4"><i class="bi bi-lightning-charge-fill"></i> Actions rapides</h5>
    <div class="quick-actions">
        <a href="?page=admin&section=activities" class="quick-action-btn">
            <div class="quick-action-icon"><i class="bi bi-clock-history"></i></div>
            <div>
                <div style="font-weight: 600;">Activités</div>
                <small class="text-muted">Gérer les projets</small>
            </div>
        </a>
        
        <a href="?page=admin&section=services" class="quick-action-btn">
            <div class="quick-action-icon"><i class="bi bi-briefcase"></i></div>
            <div>
                <div style="font-weight: 600;">Services</div>
                <small class="text-muted">Ajouter des services</small>
            </div>
        </a>
        
        <a href="?page=admin&section=messages" class="quick-action-btn">
            <div class="quick-action-icon"><i class="bi bi-envelope"></i></div>
            <div>
                <div style="font-weight: 600;">Messages</div>
                <small class="text-muted">Consulter les messages</small>
            </div>
        </a>
        
        <a href="?page=admin&section=users" class="quick-action-btn">
            <div class="quick-action-icon"><i class="bi bi-people"></i></div>
            <div>
                <div style="font-weight: 600;">Utilisateurs</div>
                <small class="text-muted">Gérer les accès</small>
            </div>
        </a>
        
        <a href="?page=admin&section=partners" class="quick-action-btn">
            <div class="quick-action-icon"><i class="bi bi-handshake"></i></div>
            <div>
                <div style="font-weight: 600;">Partenaires</div>
                <small class="text-muted">Ajouter partenaires</small>
            </div>
        </a>
        
        <a href="?page=admin&section=home" class="quick-action-btn">
            <div class="quick-action-icon"><i class="bi bi-images"></i></div>
            <div>
                <div style="font-weight: 600;">Carrousel</div>
                <small class="text-muted">Page d'accueil</small>
            </div>
        </a>
    </div>
</div>