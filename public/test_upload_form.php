<!DOCTYPE html>
<html>
<head>
    <title>Test Upload Activité</title>
    <style>
        body { font-family: Arial; padding: 20px; max-width: 600px; margin: 0 auto; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input, textarea, select { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        button { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0056b3; }
        .alert { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
    </style>
</head>
<body>
    <h1>🧪 Test Upload Activité</h1>
    
    <?php
    $message = '';
    $messageType = '';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $message = "✅ Formulaire reçu! FILES[activityImage]: ";
        $messageType = 'info';
        
        if (!empty($_FILES['activityImage']['name'])) {
            $message = "✅ Image reçue: " . $_FILES['activityImage']['name'];
            $message .= " (Size: " . $_FILES['activityImage']['size'] . " bytes)";
            $message .= " (Error code: " . $_FILES['activityImage']['error'] . ")";
            $messageType = 'success';
        } else {
            $message = "❌ AUCUNE image dans le formulaire!";
            $messageType = 'danger';
        }
    }
    ?>
    
    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?>">
            <?= $message ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="title">Titre:</label>
            <input type="text" id="title" name="title" required value="Test Activité <?= date('Y-m-d H:i:s') ?>">
        </div>
        
        <div class="form-group">
            <label for="image">Image (requis):</label>
            <input type="file" id="image" name="activityImage" accept="image/*" required>
        </div>
        
        <div class="form-group">
            <label for="description">Description:</label>
            <textarea id="description" name="description" rows="4">Test d'upload image</textarea>
        </div>
        
        <div class="form-group">
            <label for="status">Statut:</label>
            <select id="status" name="status">
                <option>En cours</option>
                <option>Planifié</option>
                <option>En attente</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="date">Date:</label>
            <input type="date" id="date" name="date" value="<?= date('Y-m-d') ?>">
        </div>
        
        <button type="submit">📤 Envoyer</button>
    </form>
    
    <hr style="margin-top: 30px;">
    <h3>📊 Diagnostic</h3>
    <pre><?php
        echo "PHP Version: " . phpversion() . "\n";
        echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
        echo "post_max_size: " . ini_get('post_max_size') . "\n";
        echo "file_uploads: " . (ini_get('file_uploads') ? 'enabled' : 'disabled') . "\n";
        echo "\n_FILES: " . json_encode($_FILES, JSON_PRETTY_PRINT) . "\n";
    ?></pre>
</body>
</html>
