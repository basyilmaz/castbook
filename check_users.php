<?php
try {
    $db = new PDO('sqlite:database/database.sqlite');
    $users = $db->query('SELECT id, name, email, role, is_active FROM users')->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($users)) {
        echo "❌ Veritabanında kullanıcı yok!\n\n";
        echo "Seeder çalıştırmanız gerekiyor:\n";
        echo "  php artisan db:seed\n";
    } else {
        echo "✓ Mevcut kullanıcılar:\n\n";
        foreach ($users as $user) {
            echo "ID: {$user['id']}\n";
            echo "İsim: {$user['name']}\n";
            echo "Email: {$user['email']}\n";
            echo "Rol: {$user['role']}\n";
            echo "Aktif: " . ($user['is_active'] ? 'Evet' : 'Hayır') . "\n";
            echo "---\n";
        }
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
