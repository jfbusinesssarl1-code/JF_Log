<div class="section-header">
    <h1><i class="bi bi-chat-dots"></i> Messages reçus</h1>
    <p>Gérez les messages envoyés par les visiteurs du site</p>
</div>

<style>
.message-card {
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    margin-bottom: 16px;
    transition: all 0.3s ease;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.message-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.message-card.unread {
    background: linear-gradient(135deg, #fff9e6 0%, #ffffff 100%);
    border-left: 4px solid #ffc107;
}

.message-card.read {
    background: #ffffff;
    border-left: 4px solid #e0e0e0;
}

.message-header {
    padding: 20px;
    cursor: pointer;
    user-select: none;
}

.message-header:hover {
    background-color: rgba(0, 0, 0, 0.02);
}

.message-header-top {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 12px;
}

.message-sender {
    display: flex;
    align-items: center;
    gap: 12px;
}

.message-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(135deg, #1a5490 0%, #2a7bc0 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 18px;
    flex-shrink: 0;
}

.message-info {}

.message-name {
    font-weight: 600;
    font-size: 16px;
    color: #333;
    margin-bottom: 2px;
}

.message-email {
    font-size: 13px;
    color: #666;
}

.message-meta {
    text-align: right;
    display: flex;
    flex-direction: column;
    gap: 6px;
    align-items: flex-end;
}

.message-date {
    font-size: 13px;
    color: #666;
}

.message-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.message-badge.unread {
    background-color: #ffc107;
    color: #000;
}

.message-badge.read {
    background-color: #e0e0e0;
    color: #666;
}

.message-subject {
    font-size: 15px;
    color: #555;
    margin-bottom: 8px;
}

.message-preview {
    font-size: 14px;
    color: #777;
    line-height: 1.5;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.message-expand-icon {
    font-size: 20px;
    color: #999;
    transition: transform 0.3s ease;
}

.message-card.expanded .message-expand-icon {
    transform: rotate(180deg);
}

.message-body {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease;
    border-top: 1px solid #f0f0f0;
}

.message-card.expanded .message-body {
    max-height: 1000px;
}

.message-body-content {
    padding: 20px;
    background-color: #fafafa;
}

.message-full-text {
    font-size: 14px;
    line-height: 1.7;
    color: #333;
    white-space: pre-wrap;
    margin-bottom: 20px;
}

.message-actions {
    display: flex;
    gap: 10px;
}

.btn-icon {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.search-filters {
    background: white;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 24px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.stats-bar {
    background: white;
    padding: 16px 20px;
    border-radius: 12px;
    margin-bottom: 24px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 12px;
}

.stat-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}

.stat-icon.primary {
    background: linear-gradient(135deg, #1a5490 0%, #2a7bc0 100%);
    color: white;
}

.stat-icon.warning {
    background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%);
    color: white;
}

.stat-details {}

.stat-label {
    font-size: 12px;
    color: #666;
    margin-bottom: 2px;
}

.stat-value {
    font-size: 20px;
    font-weight: 600;
    color: #333;
}
</style>

<div class="admin-card p-0">
    <!-- Barre de statistiques -->
    <div class="stats-bar">
        <div class="stat-item">
            <div class="stat-icon primary">
                <i class="bi bi-envelope"></i>
            </div>
            <div class="stat-details">
                <div class="stat-label">Total Messages</div>
                <div class="stat-value"><?php echo $messages_total ?? 0; ?></div>
            </div>
        </div>
        <?php
        $unreadCount = 0;
        if (!empty($messages)) {
            foreach ($messages as $m) {
                if (!($m['is_read'] ?? false)) {
                    $unreadCount++;
                }
            }
        }
        ?>
        <div class="stat-item">
            <div class="stat-icon warning">
                <i class="bi bi-envelope-exclamation"></i>
            </div>
            <div class="stat-details">
                <div class="stat-label">Non lus</div>
                <div class="stat-value"><?php echo $unreadCount; ?></div>
            </div>
        </div>
    </div>

    <!-- Filtres de recherche -->
    <div class="search-filters">
        <form class="row g-3" method="get" action="?">
            <input type="hidden" name="page" value="admin">
            <input type="hidden" name="section" value="messages">
            <div class="col-md-6">
                <label class="form-label fw-semibold"><i class="bi bi-search"></i> Rechercher</label>
                <input type="text" name="q" value="<?php echo htmlspecialchars($messages_query ?? ''); ?>" 
                       class="form-control" placeholder="Nom, email, sujet ou contenu du message">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold"><i class="bi bi-list-ol"></i> Messages par page</label>
                <select name="per" class="form-select">
                    <?php foreach ([5,10,20,50,100] as $opt): ?>
                        <option value="<?php echo $opt; ?>" <?php echo (($messages_perPage ?? 10) == $opt) ? 'selected' : ''; ?>>
                            <?php echo $opt; ?> messages
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Rechercher
                    </button>
                </div>
            </div>
        </form>
        <div class="mt-3">
            <a href="?page=admin&section=messages&action=export<?php echo !empty($messages_query) ? '&q='.urlencode($messages_query) : ''; ?>" 
               class="btn btn-outline-success btn-sm">
                <i class="bi bi-file-earmark-excel"></i> Exporter en CSV
            </a>
        </div>
    </div>

    <!-- Liste des messages -->
    <div class="p-4">
        <?php if (!empty($messages)): ?>
            <?php foreach ($messages as $m): 
                $isRead = $m['is_read'] ?? false;
                $messageId = (string)($m['_id']);
                $name = htmlspecialchars($m['name'] ?? 'Anonyme');
                $email = htmlspecialchars($m['email'] ?? '');
                $subject = htmlspecialchars($m['subject'] ?? 'Sans sujet');
                $message = htmlspecialchars($m['message'] ?? '');
                $date = isset($m['created_at']) ? date('d/m/Y à H:i', (int)($m['created_at']->toDateTime()->format('U'))) : '';
                $initials = strtoupper(substr($name, 0, 2));
            ?>
                <div class="message-card <?php echo $isRead ? 'read' : 'unread'; ?>" data-message-id="<?php echo $messageId; ?>">
                    <div class="message-header" onclick="toggleMessage('<?php echo $messageId; ?>')">
                        <div class="message-header-top">
                            <div class="message-sender">
                                <div class="message-avatar"><?php echo $initials; ?></div>
                                <div class="message-info">
                                    <div class="message-name"><?php echo $name; ?></div>
                                    <div class="message-email">
                                        <i class="bi bi-envelope"></i> <?php echo $email; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="message-meta">
                                <span class="message-badge <?php echo $isRead ? 'read' : 'unread'; ?>">
                                    <?php echo $isRead ? 'Lu' : 'Nouveau'; ?>
                                </span>
                                <div class="message-date">
                                    <i class="bi bi-clock"></i> <?php echo $date; ?>
                                </div>
                            </div>
                        </div>
                        <div class="message-subject">
                            <i class="bi bi-chat-square-text"></i> <strong><?php echo $subject; ?></strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div class="message-preview"><?php echo $message; ?></div>
                            <i class="bi bi-chevron-down message-expand-icon"></i>
                        </div>
                    </div>
                    <div class="message-body">
                        <div class="message-body-content">
                            <div class="message-full-text"><?php echo nl2br($message); ?></div>
                            <div class="message-actions">
                                <?php $token = \App\Core\Csrf::getToken(); ?>
                                <a href="mailto:<?php echo $email; ?>?subject=Re: <?php echo urlencode($subject); ?>" 
                                   class="btn btn-primary btn-sm btn-icon">
                                    <i class="bi bi-reply-fill"></i> Répondre
                                </a>
                                <a href="?page=admin&section=messages&action=delete&message_id=<?php echo urlencode($messageId); ?>&token=<?php echo urlencode($token); ?>" 
                                   class="btn btn-danger btn-sm btn-icon" 
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce message ?');">
                                    <i class="bi bi-trash-fill"></i> Supprimer
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox" style="font-size: 64px; color: #ccc;"></i>
                <p class="text-muted mt-3 mb-0">Aucun message trouvé</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php
    $total = $messages_total ?? 0;
    $page = $messages_page ?? 1;
    $per = $messages_perPage ?? 10;
    $pages = (int) ceil($total / max(1, $per));
    $qparam = !empty($messages_query) ? '&q=' . urlencode($messages_query) : '';
    if ($pages > 1): ?>
        <div class="p-4 pt-0">
            <nav aria-label="Messages pagination">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=admin&section=messages&p=<?php echo ($page - 1) . $qparam; ?>">
                                <i class="bi bi-chevron-left"></i> Précédent
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $pages; $i++): 
                        if ($i == 1 || $i == $pages || ($i >= $page - 2 && $i <= $page + 2)):
                    ?>
                        <li class="page-item <?php echo ($i === $page) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=admin&section=messages&p=<?php echo $i . $qparam; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php 
                        elseif ($i == $page - 3 || $i == $page + 3):
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        endif;
                    endfor; ?>
                    
                    <?php if ($page < $pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=admin&section=messages&p=<?php echo ($page + 1) . $qparam; ?>">
                                Suivant <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>

<script>
function toggleMessage(messageId) {
    const card = document.querySelector(`.message-card[data-message-id="${messageId}"]`);
    if (!card) return;
    
    const isExpanded = card.classList.contains('expanded');
    
    if (isExpanded) {
        // Collapse
        card.classList.remove('expanded');
    } else {
        // Expand
        card.classList.add('expanded');
        
        // Mark as read if it's unread
        if (card.classList.contains('unread')) {
            fetch(`?page=admin&section=messages&action=mark_read&message_id=${messageId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update UI
                        card.classList.remove('unread');
                        card.classList.add('read');
                        
                        const badge = card.querySelector('.message-badge');
                        if (badge) {
                            badge.classList.remove('unread');
                            badge.classList.add('read');
                            badge.textContent = 'Lu';
                        }
                        
                        // Update unread count
                        const unreadStat = document.querySelector('.stat-icon.warning').parentElement.querySelector('.stat-value');
                        if (unreadStat) {
                            const currentCount = parseInt(unreadStat.textContent);
                            unreadStat.textContent = Math.max(0, currentCount - 1);
                        }
                    }
                })
                .catch(error => console.error('Error marking message as read:', error));
        }
    }
}
</script>
