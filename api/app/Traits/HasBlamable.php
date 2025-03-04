<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait HasBlamable
{
    /**
     * Get the name of the "created by" column.
     */
    public function getCreatedByColumn(): string
    {
        return $this->createdByAttribute ?? 'created_by';
    }

    /**
     * Get the name of the "updated by" column.
     */
    public function getUpdatedByColumn(): string
    {
        return $this->updatedByAttribute ?? 'updated_by';
    }

    /**
     * Boot the trait.
     */
    protected static function bootHasBlamable(): void
    {
        static::creating(function ($model) {
            if ($model->hasColumn($model->getCreatedByColumn())) {
                $model->{$model->getCreatedByColumn()} = Auth::id();
            }
        });

        static::updating(function ($model) {
            if ($model->hasColumn($model->getUpdatedByColumn())) {
                $model->{$model->getUpdatedByColumn()} = Auth::id();
            }
        });
    }

    /**
     * Check if the model has a given column.
     */
    protected function hasColumn(string $column): bool
    {
        return in_array($column, $this->getFillable())
            || in_array($column, array_keys($this->getCasts()));
    }

    /**
     * Get the user that created the model.
     */
    public function createdBy()
    {
        return $this->belongsTo(config('auth.providers.users.model'), $this->getCreatedByColumn());
    }

    /**
     * Get the user that last updated the model.
     */
    public function updatedBy()
    {
        return $this->belongsTo(config('auth.providers.users.model'), $this->getUpdatedByColumn());
    }
}
