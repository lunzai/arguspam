<?php

namespace Lunzai\QuerySearch\Tests\Fixtures\Search\Filters;

use Illuminate\Database\Eloquent\Builder;
use Lunzai\QuerySearch\Contracts\FilterHandler;

/**
 * Handles multi-value MFA status filtering.
 *
 * Values:
 *   active  — two_factor_enabled = 1 AND two_factor_confirmed_at IS NOT NULL
 *   pending — two_factor_enabled = 1 AND two_factor_confirmed_at IS NULL
 *   off     — two_factor_enabled = 0
 */
class MfaFilter implements FilterHandler
{
    public function handle(Builder $query, string $value, array $definition): Builder
    {
        $states = array_values(array_filter(
            array_map('trim', explode(',', $value)),
            fn (string $s) => $s !== ''
        ));

        if (empty($states)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($states) {
            foreach ($states as $i => $state) {
                $method = $i === 0 ? 'where' : 'orWhere';

                match ($state) {
                    'active' => $q->{$method}(function (Builder $sub) {
                        $sub->where('two_factor_enabled', true)
                            ->whereNotNull('two_factor_confirmed_at');
                    }),
                    'pending' => $q->{$method}(function (Builder $sub) {
                        $sub->where('two_factor_enabled', true)
                            ->whereNull('two_factor_confirmed_at');
                    }),
                    'off' => $q->{$method}('two_factor_enabled', false),
                    default => null,
                };
            }
        });
    }
}
