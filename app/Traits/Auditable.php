<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

trait Auditable
{
    /**
     * Model boot edildiğinde audit eventlerini kaydet
     */
    public static function bootAuditable(): void
    {
        // Oluşturma
        static::created(function (Model $model) {
            AuditLog::log(
                action: 'create',
                description: static::getAuditCreateDescription($model),
                model: $model,
                newValues: $model->getAttributes()
            );
        });

        // Güncelleme
        static::updated(function (Model $model) {
            $dirty = $model->getDirty();
            
            // Sadece audit'e değecek değişiklikler varsa logla
            if (empty($dirty)) {
                return;
            }

            $oldValues = array_intersect_key($model->getOriginal(), $dirty);

            AuditLog::log(
                action: 'update',
                description: static::getAuditUpdateDescription($model, array_keys($dirty)),
                model: $model,
                oldValues: $oldValues,
                newValues: $dirty
            );
        });

        // Silme
        static::deleted(function (Model $model) {
            AuditLog::log(
                action: 'delete',
                description: static::getAuditDeleteDescription($model),
                model: $model,
                oldValues: $model->getAttributes()
            );
        });
    }

    /**
     * Oluşturma açıklaması
     */
    protected static function getAuditCreateDescription(Model $model): string
    {
        $modelName = class_basename($model);
        $identifier = $model->getAuditIdentifier();
        
        return "{$modelName} #{$model->id} oluşturuldu" . ($identifier ? " ({$identifier})" : '');
    }

    /**
     * Güncelleme açıklaması
     */
    protected static function getAuditUpdateDescription(Model $model, array $changedFields): string
    {
        $modelName = class_basename($model);
        $identifier = $model->getAuditIdentifier();
        $fieldsStr = implode(', ', array_slice($changedFields, 0, 3));
        
        if (count($changedFields) > 3) {
            $fieldsStr .= ' ve diğerleri';
        }
        
        return "{$modelName} #{$model->id} güncellendi ({$fieldsStr})" . ($identifier ? " - {$identifier}" : '');
    }

    /**
     * Silme açıklaması
     */
    protected static function getAuditDeleteDescription(Model $model): string
    {
        $modelName = class_basename($model);
        $identifier = $model->getAuditIdentifier();
        
        return "{$modelName} #{$model->id} silindi" . ($identifier ? " ({$identifier})" : '');
    }

    /**
     * Model için tanımlayıcı (override edilebilir)
     */
    public function getAuditIdentifier(): ?string
    {
        // Alt sınıflar override edebilir
        return $this->name ?? $this->title ?? $this->official_number ?? null;
    }
}
