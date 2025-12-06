<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Check database encoding
$db = new PDO('sqlite:database/database.sqlite');
$result = $db->query("PRAGMA encoding")->fetch();
echo "Database encoding: " . ($result[0] ?? 'Unknown') . "\n\n";

// Check a sample firm name
$firms = $db->query("SELECT id, name FROM firms LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
echo "Sample firm names:\n";
foreach ($firms as $firm) {
    echo "ID: {$firm['id']}, Name: {$firm['name']}\n";
    echo "  Hex: " . bin2hex($firm['name']) . "\n";
}
