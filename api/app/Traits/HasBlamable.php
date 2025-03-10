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
            $createdByColumn = $model->getCreatedByColumn();
            $updatedByColumn = $model->getUpdatedByColumn();
            if ($createdByColumn !== false) {
                $model->{$createdByColumn} = Auth::id();
            }
            if ($updatedByColumn !== false) {
                $model->{$updatedByColumn} = Auth::id();
            }
        });

        static::updating(function ($model) {
            $updatedByColumn = $model->getUpdatedByColumn();
            if ($updatedByColumn !== false) {
                $model->{$updatedByColumn} = Auth::id();
            }
        });
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
