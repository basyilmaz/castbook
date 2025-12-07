<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AuthToken extends Model
{
    // Token ömrü (dakika cinsinden)
    public const TOKEN_LIFETIME_MINUTES = 1440; // 24 saat
    
    // Aktivite sonrası uzatma süresi (dakika)
    public const TOKEN_EXTEND_MINUTES = 120; // 2 saat

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
     * IP adresinin eşleşip eşleşmediğini kontrol et
     */
    public function matchesIp(?string $currentIp): bool
    {
        // IP kaydedilmemişse geç
        if (empty($this->ip_address)) {
            return true;
        }
        
        return $this->ip_address === $currentIp;
    }

    /**
     * Token'ın son kullanma süresini uzat (aktivite varsa)
     */
    public function extendExpiration(): void
    {
        // Sadece son 30 dakikada kaldıysa uzat
        if ($this->expires_at->diffInMinutes(now()) < self::TOKEN_EXTEND_MINUTES) {
            $this->expires_at = now()->addMinutes(self::TOKEN_EXTEND_MINUTES);
            $this->save();
        }
    }

    /**
     * Yeni bir auth token oluştur
     */
    public static function createForUser(User $user, ?string $ipAddress = null, ?string $userAgent = null): self
    {
        // Aynı kullanıcının eski token'larını temizle
        static::where('user_id', $user->id)->delete();

        return static::create([
            'user_id' => $user->id,
            'token' => self::generateSecureToken(),
            'expires_at' => now()->addMinutes(self::TOKEN_LIFETIME_MINUTES),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent ? substr($userAgent, 0, 500) : null, // User agent max 500 karakter
        ]);
    }

    /**
     * Güvenli token oluştur (kısa ama güvenli)
     */
    protected static function generateSecureToken(): string
    {
        // 16 karakter - kısa URL için ama yeterince güvenli
        return bin2hex(random_bytes(8)); // 16 karakter hex
    }

    /**
     * Token ile kullanıcıyı bul
     * IP kontrolü devre dışı - Railway proxy arkasında IP değişken
     */
    public static function findValidToken(string $token, ?string $currentIp = null): ?self
    {
        $authToken = static::where('token', $token)
            ->where('expires_at', '>', now())
            ->first();

        if (!$authToken) {
            return null;
        }

        // IP kontrolü devre dışı - Railway'de proxy arkasında IP değişebilir
        // Token'ı kullanıldığında süresini uzat
        $authToken->extendExpiration();

        return $authToken;
    }

    /**
     * Kullanıcının tüm token'larını sil (logout)
     */
    public static function revokeAllForUser(int $userId): void
    {
        static::where('user_id', $userId)->delete();
    }

    /**
     * Süresi dolmuş token'ları temizle (cron job için)
     */
    public static function cleanupExpired(): int
    {
        return static::where('expires_at', '<', now())->delete();
    }
}
