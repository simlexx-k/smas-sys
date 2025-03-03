<?php

namespace App\Traits;

use App\Models\Activity;

trait LogsActivity
{
    protected static function bootLogsActivity()
    {
        static::created(function ($model) {
            Activity::log(
                'create',
                class_basename($model) . ' Created',
                "New {$model->getTable()} '{$model->name}' was created",
                $model,
                [
                    'model_name' => $model->name ?? null,
                    'changes' => ['created' => $model->getAttributes()]
                ]
            );
        });

        static::updated(function ($model) {
            $changes = $model->getDirty();
            $original = array_intersect_key($model->getOriginal(), $changes);

            Activity::log(
                'update',
                class_basename($model) . ' Updated',
                "The {$model->getTable()} '{$model->name}' was updated",
                $model,
                [
                    'model_name' => $model->name ?? null,
                    'changes' => [
                        'before' => $original,
                        'after' => $changes
                    ]
                ]
            );
        });

        static::deleted(function ($model) {
            Activity::log(
                'delete',
                class_basename($model) . ' Deleted',
                "The {$model->getTable()} '{$model->name}' was deleted",
                $model,
                [
                    'model_name' => $model->name ?? null,
                    'last_state' => $model->getAttributes()
                ]
            );
        });
    }
} 