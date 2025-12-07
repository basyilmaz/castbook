<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AuthToken extends Model
{
    protected $fillable = [
        'user_id',
        'token',
        'expires_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * İlişkili kullanıcı
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Token'ın geçerli olup olmadığını kontrol et
     */
    public function isValid(): bool
    {
        return $this->expires_at->isFuture();
    }

    /**
     * Yeni bir auth token oluştur
     */
    public static function createForUser(User $user, ?string $ipAddress = null, ?string $userAgent = null): self
    {
        // Eski token'ları temizle (aynı kullanıcı için)
        static::where('user_id', $user->id)
            ->where('expires_at', '<', now())
            ->delete();

        return static::create([
            'user_id' => $user->id,
            'token' => Str::random(64),
            'expires_at' => now()->addDays(7), // 7 gün geçerli
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }

    /**
     * Token ile kullanıcıyı bul
     */
    public static function findValidToken(string $token): ?self
    {
        return static::where('token', $token)
            ->where('expires_at', '>', now())
            ->first();
    }

    /**
     * Kullanıcının tüm token'larını sil (logout)
     */
    public static function revokeAllForUser(int $userId): void
    {
        static::where('user_id', $userId)->delete();
    }
}
