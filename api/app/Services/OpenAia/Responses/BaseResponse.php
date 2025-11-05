<?php

namespace App\Services\OpenAi\Responses;

abstract class BaseResponse
{
    abstract public static function fromJson(string $json): self;

    abstract public function toArray(): array;
}
