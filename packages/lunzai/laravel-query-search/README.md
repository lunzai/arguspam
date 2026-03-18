# lunzai/laravel-query-search

A Yii2-inspired, declarative query filtering, sorting, and eager-loading abstraction for Laravel 12 index endpoints.

Instead of scattering `if ($request->has(...))` blocks across your controllers, you declare a **Search class** per resource. The package handles validation, operator dispatch, relation wrapping, and pagination — your controller stays a one-liner.

---

## Requirements

| Dependency | Version |
|---|---|
| PHP | 8.3+ |
| Laravel | 12.x |

---

## Installation

```bash
composer require lunzai/laravel-query-search
```

The service provider is auto-discovered via Laravel's package discovery. No manual registration is needed.

### Publish the config

```bash
php artisan vendor:publish --tag=query-search-config
```

This creates `config/query-search.php`.

---

## Configuration

```php
// config/query-search.php

return [

    // Query-string parameter names (customize if your API uses different names)
    'params' => [
        'filter'   => 'filter',    // filter[status]=active
        'sort'     => 'sort',      // sort=-created_at,name
        'include'  => 'include',   // include=roles,permissions
        'count'    => 'count',     // count=sessions
        'page'     => 'page',      // page=2
        'per_page' => 'per_page',  // per_page=50
    ],

    'pagination' => [
        'default_per_page' => 20,   // items per page when per_page is omitted
        'max_per_page'     => 100,  // hard ceiling — larger values are capped
    ],

    // false (default) — silently drop invalid filters
    // true            — throw HTTP 422 ValidationException on any invalid input
    'strict' => false,

];
```

---

## Quick Start

### 1. Create a Search class

```php
// app/Search/UserSearch.php

namespace App\Search;

use Lunzai\QuerySearch\Search;

class UserSearch extends Search
{
    // Columns the client is allowed to sort by
    protected array $sortable = ['name', 'email', 'status', 'created_at'];

    // Relations the client may eager-load via ?include=
    protected array $includeable = ['roles', 'permissions'];

    // Relations the client may count via ?count=
    protected array $countable = ['sessions', 'loginAttempts'];

    protected function filters(): array
    {
        return [
            'status'     => ['operator' => 'in',      'column' => 'status'],
            'email'      => ['operator' => 'like',     'column' => 'email'],
            'created_at' => ['operator' => 'range',    'column' => 'created_at'],
            'verified'   => ['operator' => 'boolean',  'column' => 'email_verified_at'],
            'name'       => ['operator' => 'exact',    'column' => 'name'],
            'role_id'    => ['operator' => 'in',       'column' => 'id', 'relation' => 'roles'],
        ];
    }
}
```

### 2. Use it in a controller

```php
// app/Http/Controllers/UserController.php

use App\Models\User;
use App\Search\UserSearch;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request, UserSearch $search)
    {
        return $search
            ->for(User::query())
            ->fromRequest($request)
            ->paginate();
    }
}
```

### 3. Make a request

```
GET /users
  ?filter[status]=active,pending
  &filter[email]=john
  &filter[created_at]=2024-01-01,2024-12-31
  &filter[role_id]=1,3
  &sort=-created_at,name
  &include=roles
  &count=sessions
  &page=2
  &per_page=25
```

---

## Creating a Search Class

Every Search class extends `Lunzai\QuerySearch\Search` and must implement `filters()`.

```php
use Lunzai\QuerySearch\Search;

class PostSearch extends Search
{
    // ...
}
```

### `$sortable`

Whitelist of columns the client may sort by. Values not in this list are silently ignored.

```php
protected array $sortable = ['title', 'published_at', 'views'];
```

Sort direction is controlled by a leading `-` in the query string: `sort=-published_at` → `ORDER BY published_at DESC`.

### `$includeable`

Whitelist of relation names the client may eager-load with `?include=`.

```php
protected array $includeable = ['author', 'tags', 'comments'];
```

### `$countable`

Whitelist of relation names the client may count with `?count=`. Adds `{relation}_count` to each model.

```php
protected array $countable = ['comments', 'likes'];
```

### `filters()`

Returns an associative array mapping filter keys (as they appear in `filter[key]`) to filter definitions.

```php
protected function filters(): array
{
    return [
        'status' => ['operator' => 'in', 'column' => 'status'],
        // ...
    ];
}
```

**Filter definition keys:**

| Key | Required | Description |
|---|---|---|
| `operator` | yes | One of `exact`, `like`, `in`, `range`, `boolean`, `custom` |
| `column` | for built-in operators | The database column to filter on |
| `relation` | no | Wraps the filter in `whereHas($relation, ...)` |
| `handler` | for `custom` only | FQCN of a class implementing `FilterHandler` |

### `rules()`

Override or extend the auto-generated validation rules for any filter key or top-level parameter. Rules returned here are **merged on top** of the auto-generated `['sometimes', 'string']` rules.

```php
protected function rules(): array
{
    return [
        'filter.status'  => ['sometimes', 'string', 'in:active,inactive,pending'],
        'filter.email'   => ['sometimes', 'string', 'max:255'],
        'filter.mfa'     => ['sometimes', 'string', 'regex:/^(active|pending|off)(,(active|pending|off))*$/'],
    ];
}
```

---

## Filter Operators

### `exact`

Adds `WHERE column = value`. Empty / whitespace-only values are skipped.

```php
'name' => ['operator' => 'exact', 'column' => 'name'],
```

```
GET /users?filter[name]=Alice
-- WHERE name = 'Alice'
```

---

### `like`

Adds `WHERE column LIKE '%value%'`. LIKE wildcards (`%`, `_`, `\`) in the value are escaped automatically. Empty values are skipped.

```php
'email' => ['operator' => 'like', 'column' => 'email'],
```

```
GET /users?filter[email]=gmail
-- WHERE email LIKE '%gmail%'
```

---

### `in`

Splits the value on commas and adds `WHERE column IN (...)`. A single value uses `=` instead of `IN`. Empty or comma-only values are skipped.

```php
'status' => ['operator' => 'in', 'column' => 'status'],
```

```
GET /users?filter[status]=active,pending
-- WHERE status IN ('active', 'pending')

GET /users?filter[status]=active
-- WHERE status = 'active'
```

---

### `range`

Parses a `min,max` pair.

| Value | SQL |
|---|---|
| `2024-01-01,2024-12-31` | `BETWEEN '2024-01-01' AND '2024-12-31'` |
| `2024-01-01,` | `>= '2024-01-01'` |
| `,2024-12-31` | `<= '2024-12-31'` |
| `,` or no comma | skipped |

```php
'created_at' => ['operator' => 'range', 'column' => 'created_at'],
```

```
GET /users?filter[created_at]=2024-01-01,2024-06-30
-- WHERE created_at BETWEEN '2024-01-01' AND '2024-06-30'

GET /users?filter[created_at]=2024-01-01,
-- WHERE created_at >= '2024-01-01'
```

---

### `boolean`

Maps human-friendly strings to `true`/`false`. Unrecognized values are skipped.

| Truthy | Falsy |
|---|---|
| `true`, `1`, `on`, `yes` | `false`, `0`, `off`, `no` |

Comparison is case-insensitive.

```php
'verified' => ['operator' => 'boolean', 'column' => 'email_verified_at'],
```

```
GET /users?filter[verified]=true
-- WHERE email_verified_at = 1

GET /users?filter[verified]=no
-- WHERE email_verified_at = 0
```

---

### `custom`

Delegates to a class implementing `Lunzai\QuerySearch\Contracts\FilterHandler`. Useful for complex multi-column or multi-condition logic.

```php
'mfa' => ['operator' => 'custom', 'handler' => MfaFilter::class],
```

See [Custom Filter Handlers](#custom-filter-handlers) below.

---

## Relation Filters

Adding a `relation` key to any built-in operator wraps it in `whereHas`:

```php
'role_id' => ['operator' => 'in', 'column' => 'id', 'relation' => 'roles'],
```

```
GET /users?filter[role_id]=1,3
-- WHERE EXISTS (SELECT 1 FROM roles INNER JOIN role_user ... WHERE roles.id IN (1, 3))
```

**No-op guard:** If the inner filter produces no SQL constraints (e.g. `filter[role_id]=,,,` is all commas), the `whereHas` is skipped entirely — the filter is treated as absent rather than restricting to records that have any related row.

---

## Query String Reference

| Parameter | Example | Description |
|---|---|---|
| `filter[key]` | `filter[status]=active` | Apply a declared filter |
| `sort` | `sort=-created_at,name` | Comma-separated fields; prefix `-` for descending |
| `include` | `include=roles,permissions` | Eager-load relations (whitelisted by `$includeable`) |
| `count` | `count=sessions` | Add `{relation}_count` attribute (whitelisted by `$countable`) |
| `page` | `page=3` | Page number (default: 1) |
| `per_page` | `per_page=50` | Items per page (capped at `max_per_page`) |

Parameter names are configurable in `config/query-search.php` under the `params` key.

---

## Custom Filter Handlers

For filters that don't fit a single operator, implement the `FilterHandler` contract:

```php
use Illuminate\Database\Eloquent\Builder;
use Lunzai\QuerySearch\Contracts\FilterHandler;

class MfaFilter implements FilterHandler
{
    public function handle(Builder $query, string $value, array $definition): Builder
    {
        // $value is the raw validated string from filter[mfa]
        // $definition is the full filter definition array from filters()

        $statuses = array_filter(array_map('trim', explode(',', $value)));

        return $query->where(function (Builder $q) use ($statuses) {
            foreach ($statuses as $i => $status) {
                $method = $i === 0 ? 'where' : 'orWhere';
                $q->{$method}('mfa_status', $status);
            }
        });
    }
}
```

Register it in your Search class:

```php
protected function filters(): array
{
    return [
        'mfa' => ['operator' => 'custom', 'handler' => MfaFilter::class],
    ];
}
```

---

## Validation Modes

### Lenient (default)

Invalid filter values are silently dropped; valid ones still apply.

```
GET /users?filter[status]=not_a_valid_status&filter[email]=john
-- filter[status] dropped, filter[email]=john still applied
```

### Strict

Set `strict => true` in `config/query-search.php` to throw an HTTP 422 `ValidationException` on any invalid input.

```php
// config/query-search.php
'strict' => true,
```

You can also tighten individual filter rules via `rules()` without enabling strict mode globally.

---

## Fluent API

The Search class exposes a fluent builder interface:

```php
// Inject via the container or instantiate directly
$search = new UserSearch;

// or: resolve from the container to get auto-wired dependencies
$search = app(UserSearch::class);

// Chain:
$paginator = $search
    ->for(User::query())       // set the base Eloquent Builder
    ->fromRequest($request)    // parse & validate the HTTP request
    ->paginate();              // apply + paginate (returns LengthAwarePaginator)

// Or get the raw Builder if you need to add clauses afterwards:
$builder = $search
    ->for(User::query())
    ->fromRequest($request)
    ->apply();                 // returns Builder

$users = $builder->where('tenant_id', $tenantId)->get();
```

### `paginate(?int $perPage = null)`

- Applies all filters, includes, counts, and sorts.
- Uses `per_page` from the request if present (capped at `max_per_page`).
- Falls back to `default_per_page` from config.
- The optional `$perPage` argument overrides everything.

---

## Pagination

The package returns a standard Laravel `LengthAwarePaginator`. You can append query parameters for correct pagination links:

```php
return $search
    ->for(User::query())
    ->fromRequest($request)
    ->paginate()
    ->appends($request->query());
```

---

## Testing

Install dev dependencies:

```bash
cd packages/lunzai/laravel-query-search
composer install
```

Run the test suite:

```bash
vendor/bin/phpunit
```

The suite uses **Orchestra Testbench** with an in-memory SQLite database. No external services required.

Tests are organized into two suites:

- **Unit** — individual filter classes, operator behavior, validation logic
- **Feature** — full round-trips through a real SQLite DB (filter, sort, include, count, pagination)

---

## License

MIT
