<?php

namespace App\Traits;

use App\Models\Activity;

trait LogsActivity
{
    protected static function bootLogsActivity()
    {
        // Only log activities if we have an authenticated user or a specific user context
        static::created(function ($model) {
            if (auth()->check() || isset($model->created_by_user_id)) {
                static::logActivity('create', 'Tenant Created', "New tenants '{$model->name}' was created");
            }
        });

        static::updated(function ($model) {
            if (auth()->check() || isset($model->updated_by_user_id)) {
                $changes = $model->getChanges();
                unset($changes['updated_at']); // Exclude updated_at from changes
                if (!empty($changes)) {
                    static::logActivity('update', 'Tenant Updated', "Tenant '{$model->name}' was updated");
                }
            }
        });

        static::deleted(function ($model) {
            if (auth()->check() || isset($model->deleted_by_user_id)) {
                static::logActivity('delete', 'Tenant Deleted', "Tenant '{$model->name}' was deleted");
            }
        });
    }

    public static function logActivity($type, $action, $description)
    {
        $userId = auth()->id() ?? 
                 request()->input('created_by_user_id') ?? 
                 static::getCurrentUserId();

        if (!$userId) {
            return; // Skip logging if no user ID is available
        }

        Activity::create([
            'user_id' => $userId,
            'tenant_id' => auth()->user()?->tenant_id ?? request()->tenant?->id,
            'type' => $type,
            'action' => $action,
            'description' => $description,
            'subject_type' => static::class,
            'subject_id' => static::getCurrentModelId(),
            'metadata' => [
                'model_name' => static::getCurrentModelName(),
                'changes' => [
                    $type => static::getCurrentModelData()
                ]
            ]
        ]);
    }

    protected static function getCurrentUserId()
    {
        if (isset(static::$currentModel)) {
            return static::$currentModel->created_by_user_id ?? 
                   static::$currentModel->updated_by_user_id ?? 
                   static::$currentModel->user_id ?? 
                   null;
        }
        return null;
    }

    protected static function getCurrentModelId()
    {
        return isset(static::$currentModel) ? static::$currentModel->id : null;
    }

    protected static function getCurrentModelName()
    {
        return isset(static::$currentModel) ? static::$currentModel->name : null;
    }

    protected static function getCurrentModelData()
    {
        return isset(static::$currentModel) ? static::$currentModel->toArray() : [];
    }
}