<?php

namespace App\Traits;

trait HasExpandable
{
    /**
     * Get the expandable fields for the model.
     * Override this in your model to define allowed relationships.
     *
     * @return array
     */
    public function getExpandable(): array
    {
        return $this->expandable ?? [];
    }
}
