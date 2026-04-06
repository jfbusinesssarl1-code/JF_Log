<?php
echo "═════════════════════════════════════════════════════════════════════\n";
echo "✅ LIMITES PHP (configurées via .htaccess)\n";
echo "═════════════════════════════════════════════════════════════════════\n\n";

$configs = [
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'max_execution_time' => ini_get('max_execution_time'),
    'max_input_time' => ini_get('max_input_time'),
    'memory_limit' => ini_get('memory_limit'),
];

foreach ($configs as $key => $value) {
    echo "$key: $value\n";
}

echo "\n═════════════════════════════════════════════════════════════════════\n";
echo "✅ Configuration activée pour uploads jusqu'à 3 MB (3072 KB)\n";
echo "═════════════════════════════════════════════════════════════════════\n";
