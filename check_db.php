<?php
try {
    $db = new PDO('sqlite:database/database.sqlite');
    $tables = $db->query('SELECT name FROM sqlite_master WHERE type="table"')->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables in database:\n";
    foreach ($tables as $table) {
        echo "- $table\n";
    }
    
    // Check for cache and sessions tables
    if (in_array('cache', $tables)) {
        echo "\n✓ cache table exists\n";
    } else {
        echo "\n✗ cache table MISSING\n";
    }
    
    if (in_array('sessions', $tables)) {
        echo "✓ sessions table exists\n";
    } else {
        echo "✗ sessions table MISSING\n";
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
