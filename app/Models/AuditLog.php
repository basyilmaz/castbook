<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'user_name',
        'action',
        'model_type',
        'model_id',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    /**
     * Log oluşturan kullanıcı
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * İlişkili model
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo('model');
    }

    /**
     * Yeni audit log oluştur
     */
    public static function log(
        string $action,
        string $description,
        ?Model $model = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): self {
        $user = Auth::user();

        return self::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? 'Sistem',
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model?->id,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Action türüne göre renk
     */
    public function getActionColorAttribute(): string
    {
        return match ($this->action) {
            'create' => 'success',
            'update' => 'warning',
            'delete' => 'danger',
            'login' => 'primary',
            'logout' => 'secondary',
            'export' => 'info',
            default => 'secondary',
        };
    }

    /**
     * Action türüne göre ikon
     */
    public function getActionIconAttribute(): string
    {
        return match ($this->action) {
            'create' => 'bi-plus-circle',
            'update' => 'bi-pencil',
            'delete' => 'bi-trash',
            'login' => 'bi-box-arrow-in-right',
            'logout' => 'bi-box-arrow-right',
            'export' => 'bi-download',
            'import' => 'bi-upload',
            default => 'bi-activity',
        };
    }

    /**
     * Action türüne göre Türkçe label
     */
    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'create' => 'Oluşturma',
            'update' => 'Güncelleme',
            'delete' => 'Silme',
            'login' => 'Giriş',
            'logout' => 'Çıkış',
            'export' => 'Dışa Aktarma',
            'import' => 'İçe Aktarma',
            default => ucfirst($this->action),
        };
    }
}
