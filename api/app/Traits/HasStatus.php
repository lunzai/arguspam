<?php

namespace App\Traits;

use App\Enums\Status;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

trait HasStatus
{
    #[Scope]
    public function active(Builder $query): void
    {
        $query->where('status', Status::ACTIVE->value);
    }

    public function isActive(): bool
    {
        $status = $this->status;
        if ($status instanceof Status) {
            return $status === Status::ACTIVE;
        }
        return $status === Status::ACTIVE->value;
    }

    public function isInactive(): bool
    {
        $status = $this->{$this->statusColumn};
        if ($status instanceof Status) {
            return $status === Status::INACTIVE;
        }
        return $status === Status::INACTIVE->value;
    }
}
