<?php

namespace App\Services\Jit\Databases\Models;

use DateTime;

class Query
{
    public string $userHost;
    public DateTime $firstTimestamp;
    public DateTime $lastTimestamp;
    public string $commandType;
    public int $count;
    public string $queryText;

    public function __construct(
        string $userHost,
        string $firstTimestamp,
        string $lastTimestamp,
        string $commandType,
        int $count,
        string $queryText
    ) {
        $this->userHost = $userHost;
        $this->firstTimestamp = new DateTime($firstTimestamp);
        $this->lastTimestamp = new DateTime($lastTimestamp);
        $this->commandType = $commandType;
        $this->count = $count;
        $this->queryText = $queryText;
    }

    public static function fromArray(array $data): Query
    {
        return new Query(
            $data['user_host'],
            $data['first_timestamp'],
            $data['last_timestamp'],
            $data['command_type'],
            (int) $data['count'],
            $data['query_text']
        );
    }

    public function toArray(): array
    {
        return [
            'user_host' => $this->userHost,
            'first_timestamp' => $this->firstTimestamp->format('Y-m-d H:i:s'),
            'last_timestamp' => $this->lastTimestamp->format('Y-m-d H:i:s'),
            'command_type' => $this->commandType,
            'count' => $this->count,
            'query_text' => $this->queryText,
        ];
    }

    public function __toString(): string
    {
        return json_encode($this->toArray());
    }
}
