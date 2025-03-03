<?php

namespace App\Traits;

use App\Enums\Status;

trait HasStatus
{
    protected string $statusColumn = 'status';

    public function isActive(): bool
    {
        $status = $this->{$this->statusColumn};
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
