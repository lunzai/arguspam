<?php

namespace Lunzai\QuerySearch;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Lunzai\QuerySearch\Support\FilterResolver;

abstract class Search
{
    /** Columns allowed for sorting (base table only). */
    protected array $sortable = [];

    /** Relation names allowed in ?include=. */
    protected array $includeable = [];

    /** Relation names allowed in ?count=. */
    protected array $countable = [];

    // -------------------------------------------------------------------------
    // Internal state
    // -------------------------------------------------------------------------

    private Builder $query;

    /** @var array<string, string> Validated filter key → raw string value */
    private array $validatedFilters = [];

    /** @var array<int, array{field: string, direction: string}> */
    private array $validatedSort = [];

    /** @var array<int, string> */
    private array $validatedInclude = [];

    /** @var array<int, string> */
    private array $validatedCount = [];

    private int $page = 1;
    private ?int $perPage = null;
    private bool $applied = false;

    // -------------------------------------------------------------------------
    // Abstract API — concrete Search classes must implement these
    // -------------------------------------------------------------------------

    /**
     * Declare filterable attributes.
     *
     * Each key is the filter name (as it appears in filter[key]=…).
     * Each value is an associative array with keys:
     *   - 'operator'  string  'exact'|'in'|'like'|'range'|'boolean'|'custom'
     *   - 'column'    string  The DB column to filter on (not needed for 'custom')
     *   - 'relation'  string  (optional) Wrap filter in whereHas($relation, …)
     *   - 'handler'   string  Class name (only for 'custom' operator)
     *
     * @return array<string, array<string, mixed>>
     */
    abstract protected function filters(): array;

    // -------------------------------------------------------------------------
    // Overridable API
    // -------------------------------------------------------------------------

    /**
     * Extra / override Laravel validation rules for filter.*, sort, include,
     * count, page, and per_page.
     *
     * The base class automatically generates 'sometimes'+'string' rules for
     * every key declared in filters(). Rules returned here are merged on top,
     * so you can tighten specific keys (e.g. add 'in:active,inactive').
     *
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [];
    }

    // -------------------------------------------------------------------------
    // Fluent builder API
    // -------------------------------------------------------------------------

    /**
     * Set the base Eloquent query the Search will build on.
     */
    public function for(Builder $query): static
    {
        $this->query = $query;

        return $this;
    }

    /**
     * Extract, validate, and store filter/sort/include/count/pagination from
     * the incoming request using the configured parameter names.
     */
    public function fromRequest(Request $request): static
    {
        $params = config('query-search.params');

        $raw = [
            'filter' => $request->input($params['filter'], []),
            'sort' => $request->input($params['sort'], ''),
            'include' => $request->input($params['include'], ''),
            'count' => $request->input($params['count'], ''),
            'page' => $request->input($params['page'], 1),
            'per_page' => $request->input($params['per_page']),
        ];

        // Ensure filter is always an array.
        if (!is_array($raw['filter'])) {
            $raw['filter'] = [];
        }

        $rules = $this->buildRules();

        $validator = Validator::make($raw, $rules);

        if ($validator->fails()) {
            if (config('query-search.strict', false)) {
                throw new ValidationException($validator);
            }

            $this->storeValidated($this->collectValidFields($raw, $rules));
        } else {
            $this->storeValidated($validator->validated());
        }

        return $this;
    }

    /**
     * Apply all validated filters, includes, counts, and sorts to the query.
     */
    public function apply(): Builder
    {
        if (!isset($this->query)) {
            throw new \LogicException('Call for() before apply() or paginate().');
        }

        if ($this->applied) {
            return $this->query;
        }

        $this->applied = true;

        $resolver = app(FilterResolver::class);
        $definitions = $this->filters();

        // Filters
        foreach ($this->validatedFilters as $key => $value) {
            if (!isset($definitions[$key])) {
                continue;
            }

            $this->query = $resolver->apply($this->query, $value, $definitions[$key]);
        }

        // Includes
        if (!empty($this->validatedInclude)) {
            $this->query->with($this->validatedInclude);
        }

        // Counts
        if (!empty($this->validatedCount)) {
            $this->query->withCount($this->validatedCount);
        }

        // Sorts
        foreach ($this->validatedSort as ['field' => $field, 'direction' => $direction]) {
            $this->query->orderBy($field, $direction);
        }

        return $this->query;
    }

    /**
     * Apply the search and paginate.
     */
    public function paginate(?int $perPage = null): LengthAwarePaginator
    {
        $perPage ??= $this->perPage
            ?? config('query-search.pagination.default_per_page', 20);

        $max = config('query-search.pagination.max_per_page', 100);
        $perPage = min((int) $perPage, $max);

        return $this->apply()->paginate($perPage, page: $this->page);
    }

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------

    /**
     * Build the full validation rules array by merging auto-generated rules
     * from filters() with any rules() override from the subclass.
     *
     * @return array<string, mixed>
     */
    private function buildRules(): array
    {
        $auto = [];

        foreach (array_keys($this->filters()) as $key) {
            $auto['filter.'.$key] = ['sometimes', 'string'];
        }

        $auto['sort'] = ['sometimes', 'string'];
        $auto['include'] = ['sometimes', 'string'];
        $auto['count'] = ['sometimes', 'string'];
        $auto['page'] = ['sometimes', 'integer', 'min:1'];
        $auto['per_page'] = ['sometimes', 'integer', 'min:1', 'max:'.config('query-search.pagination.max_per_page', 100)];

        // Subclass rules() win when keys overlap.
        return array_merge($auto, $this->rules());
    }

    /**
     * Lenient validation: validate each top-level field individually and
     * collect only those that pass.
     *
     * @param  array<string, mixed>  $raw
     * @param  array<string, mixed>  $rules
     * @return array<string, mixed>
     */
    private function collectValidFields(array $raw, array $rules): array
    {
        $valid = [];

        // Validate top-level scalar fields independently.
        foreach (['sort', 'include', 'count', 'page', 'per_page'] as $field) {
            if (!isset($raw[$field]) || $raw[$field] === '' || $raw[$field] === null) {
                continue;
            }

            $fieldRules = array_filter(
                $rules,
                fn ($k) => $k === $field,
                ARRAY_FILTER_USE_KEY
            );

            $v = Validator::make([$field => $raw[$field]], $fieldRules);

            if (!$v->fails()) {
                $valid[$field] = $raw[$field];
            }
        }

        // Validate each filter key independently.
        if (is_array($raw['filter'])) {
            $valid['filter'] = [];

            foreach ($raw['filter'] as $key => $value) {
                $ruleKey = 'filter.'.$key;

                if (!isset($rules[$ruleKey])) {
                    // Not declared in filters() — always drop.
                    continue;
                }

                $v = Validator::make(
                    ['filter' => [$key => $value]],
                    [$ruleKey => $rules[$ruleKey]]
                );

                if (!$v->fails()) {
                    $valid['filter'][$key] = $value;
                }
            }
        }

        return $valid;
    }

    /**
     * Store a validated data array into the internal state properties.
     *
     * @param  array<string, mixed>  $validated
     */
    private function storeValidated(array $validated): void
    {
        // Filters
        $this->validatedFilters = is_array($validated['filter'] ?? null)
            ? $validated['filter']
            : [];

        // Sort — parse "field1,-field2"
        $this->validatedSort = [];

        if (!empty($validated['sort'])) {
            foreach (explode(',', (string) $validated['sort']) as $segment) {
                $segment = trim($segment);

                if ($segment === '') {
                    continue;
                }

                $desc = str_starts_with($segment, '-');
                $field = $desc ? substr($segment, 1) : $segment;

                if (in_array($field, $this->sortable, true)) {
                    $this->validatedSort[] = [
                        'field' => $field,
                        'direction' => $desc ? 'desc' : 'asc',
                    ];
                }
            }
        }

        // Include — whitelist against $includeable
        $this->validatedInclude = [];

        if (!empty($validated['include'])) {
            foreach (explode(',', (string) $validated['include']) as $relation) {
                $relation = trim($relation);

                if ($relation !== '' && in_array($relation, $this->includeable, true)) {
                    $this->validatedInclude[] = $relation;
                }
            }
        }

        // Count — whitelist against $countable
        $this->validatedCount = [];

        if (!empty($validated['count'])) {
            foreach (explode(',', (string) $validated['count']) as $relation) {
                $relation = trim($relation);

                if ($relation !== '' && in_array($relation, $this->countable, true)) {
                    $this->validatedCount[] = $relation;
                }
            }
        }

        // Pagination
        $this->page = isset($validated['page']) ? max(1, (int) $validated['page']) : 1;
        $this->perPage = isset($validated['per_page']) ? (int) $validated['per_page'] : null;
    }
}
