<?php

namespace App\Models;

use App\Enums\SettingDataType;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'group',
        'label',
        'description',
        'data_type',
    ];

    protected $casts = [
        'data_type' => SettingDataType::class,
    ];

    /**
     * Get the typed value attribute.
     */
    public function getTypedValueAttribute(): mixed
    {
        return $this->data_type->cast($this->value);
    }

    /**
     * Set the value with proper type casting.
     */
    public function setTypedValueAttribute(mixed $value): void
    {
        if (!$this->data_type->validate($value)) {
            throw new \InvalidArgumentException("Invalid value for type {$this->data_type->value}");
        }

        $this->attributes['value'] = $this->data_type->prepare($value);
    }
}
