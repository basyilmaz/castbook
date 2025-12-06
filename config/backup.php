<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Restore Toggle
    |--------------------------------------------------------------------------
    |
    | Geri yükleme işlemlerinin çalışabilmesi için bu değerin true olması gerekir.
    | Varsayılan olarak kapalı (false) gelir, PROD ortamında bilinçli olarak
    | açılması tavsiye edilir.
    |
    */
    'restore_enabled' => env('BACKUP_RESTORE_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Maksimum Yedek Boyutu (MB)
    |--------------------------------------------------------------------------
    |
    | JSON yedek dosyaları bu sınırı aşarsa kabul edilmez. Büyük dosyalar
    | belleği zorlayabileceğinden güvenlik amacıyla sınırlandırılmıştır.
    |
    */
    'max_upload_mb' => env('BACKUP_MAX_UPLOAD_MB', 20),
];
