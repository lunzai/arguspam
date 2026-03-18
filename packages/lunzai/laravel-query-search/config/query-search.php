<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Request Parameter Names
    |--------------------------------------------------------------------------
    |
    | The query-string keys used to extract filter, sort, include, count, and
    | pagination data from the incoming request.
    |
    */

    'params' => [
        'filter'   => 'filter',
        'sort'     => 'sort',
        'include'  => 'include',
        'count'    => 'count',
        'page'     => 'page',
        'per_page' => 'per_page',
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination Defaults
    |--------------------------------------------------------------------------
    */

    'pagination' => [
        'default_per_page' => 20,
        'max_per_page'     => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Strict Validation Mode
    |--------------------------------------------------------------------------
    |
    | When false (default), invalid filter/sort/include values are silently
    | ignored. Set to true to throw a ValidationException (HTTP 422) instead.
    |
    */

    'strict' => false,

];
