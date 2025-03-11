<?php

return [
    'password' => [
        'min' => 8,
    ],
    'pagination' => [
        'per_page' => 10,
    ],
    'request' => [
        'duration' => [
            'min' => 10,
            'max' => 43200, // 30 days max
        ],
    ],
];
