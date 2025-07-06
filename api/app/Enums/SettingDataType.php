<?php

namespace App\Enums;

enum SettingDataType: string
{
    case STRING = 'string';
    case INTEGER = 'integer';
    case FLOAT = 'float';
    case BOOLEAN = 'boolean';
    case JSON = 'json';
    case ARRAY = 'array';

    public function cast(string $value): mixed
    {
        return match ($this) {
            self::STRING => (string) $value,
            self::INTEGER => (int) $value,
            self::FLOAT => (float) $value,
            self::BOOLEAN => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            self::JSON => json_decode($value, true),
            self::ARRAY => is_array($value) ? $value : explode(',', $value),
        };
    }

    public function validate(mixed $value): bool
    {
        return match ($this) {
            self::STRING => is_string($value) || is_numeric($value),
            self::INTEGER => is_int($value) || (is_string($value) && ctype_digit($value)),
            self::FLOAT => is_float($value) ||
                is_int($value) ||
                (is_string($value) && is_numeric($value)),
            self::BOOLEAN => is_bool($value) ||
                in_array($value, [0, 1], true) ||
                (!is_array($value) && in_array(strtolower((string) $value), ['true', 'false', '0', '1'])),
            self::JSON => is_array($value) ||
                (is_string($value) && json_decode($value) !== null && json_last_error() === JSON_ERROR_NONE),
            self::ARRAY => is_array($value) || is_string($value),
        };
    }

    public function prepare(mixed $value): string
    {
        return match ($this) {
            self::STRING => (string) $value,
            self::INTEGER => (string) (int) $value,
            self::FLOAT => (string) (float) $value,
            self::BOOLEAN => $value ? 'true' : 'false',
            self::JSON, self::ARRAY => is_string($value) ? $value : json_encode($value),
        };
    }
}
