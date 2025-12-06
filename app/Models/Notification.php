<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'icon',
        'link',
        'link_text',
        'data',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    /**
     * Bildirim sahibi kullanıcı
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Okunmamış bildirimleri getir
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Son N bildirimi getir
     */
    public function scopeRecent($query, int $limit = 10)
    {
        return $query->orderByDesc('created_at')->limit($limit);
    }

    /**
     * Bildirimi okundu olarak işaretle
     */
    public function markAsRead(): bool
    {
        if ($this->read_at) {
            return false;
        }

        return $this->update(['read_at' => now()]);
    }

    /**
     * İkon class'ı
     */
    public function getIconClassAttribute(): string
    {
        return $this->icon ?? match ($this->type) {
            'payment_reminder' => 'bi-cash-coin text-warning',
            'declaration_reminder' => 'bi-file-earmark-text text-info',
            'overdue' => 'bi-exclamation-triangle text-danger',
            'success' => 'bi-check-circle text-success',
            default => 'bi-bell text-primary',
        };
    }

    /**
     * Yeni bildirim oluştur (helper)
     */
    public static function notify(
        string $type,
        string $title,
        string $message,
        ?int $userId = null,
        ?string $link = null,
        ?string $icon = null,
        ?array $data = null
    ): self {
        return static::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'icon' => $icon,
            'link' => $link,
            'data' => $data,
        ]);
    }

    /**
     * Tüm kullanıcılara bildirim gönder
     */
    public static function notifyAll(
        string $type,
        string $title,
        string $message,
        ?string $link = null,
        ?string $icon = null,
        ?array $data = null
    ): void {
        $users = User::all();
        
        foreach ($users as $user) {
            static::notify($type, $title, $message, $user->id, $link, $icon, $data);
        }
    }
}
