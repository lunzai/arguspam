<?php

namespace Database\Factories;

use App\Enums\DatabaseScope;
use App\Enums\RequestStatus;
use App\Enums\RiskRating;
use App\Models\Asset;
use App\Models\Org;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Request>
 */
class RequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDatetime = now();
        $startDatetime->setSeconds(0);
        $duration = $this->faker->randomElement([30, 60, 90, 120]);
        $endDatetime = Carbon::parse($startDatetime)->addMinutes($duration);

        $requests = [
            [
                'reason' => 'Update promotional pricing for bundled products in the staging database before syncing to production for the upcoming holiday campaign. Need to verify price propagation logic and discount thresholds.',
                'intended_query' => "UPDATE product_catalog pc
                    JOIN promotion_rules pr ON pc.category = pr.category
                    SET pc.discount_price = pc.base_price * (1 - pr.discount_percent / 100)
                    WHERE pr.active = 1
                    AND pr.start_date <= NOW()
                    AND pr.end_date >= NOW()
                    AND pc.status = 'active';
                ",
                'scope' => DatabaseScope::READ_WRITE,
                'is_access_sensitive_data' => false,
                'sensitive_data_note' => null,
            ],
            [
                'reason' => 'Investigate a suspicious spike in login failures related to administrative accounts reported by the SIEM. Need to cross-check user activity with authentication logs.',
                'intended_query' => "SELECT u.username,
                        COUNT(l.id) AS failed_attempts,
                        MAX(l.timestamp) AS last_attempt
                    FROM users u
                    JOIN login_attempts l ON u.id = l.user_id
                    WHERE l.status = 'FAILED'
                    AND u.role IN ('admin', 'superuser')
                    AND l.timestamp > NOW() - INTERVAL 1 DAY
                    GROUP BY u.username
                    ORDER BY failed_attempts DESC;
                ",
                'scope' => DatabaseScope::READ_ONLY,
                'is_access_sensitive_data' => true,
                'sensitive_data_note' => 'Includes usernames and timestamps linked to privileged accounts; potential exposure of admin account activity patterns.',
            ],
            [
                'reason' => 'Apply schema migration for the finance database to support multi-currency transactions and ensure referential integrity for new payment methods.',
                'intended_query' => "ALTER TABLE payments
                    ADD COLUMN currency_code CHAR(3) NOT NULL DEFAULT 'USD',
                    ADD COLUMN exchange_rate DECIMAL(10,5) NULL,
                    ADD CONSTRAINT fk_payment_customer FOREIGN KEY (customer_id)
                    REFERENCES customers(id) ON DELETE CASCADE,
                    ADD CONSTRAINT chk_currency CHECK (currency_code IN ('USD', 'EUR', 'MYR', 'SGD'));
                ",
                'scope' => DatabaseScope::DDL,
                'is_access_sensitive_data' => true,
                'sensitive_data_note' => 'Structural modification involves financial transaction tables containing customer-linked identifiers and monetary values.',
            ],
            [
                'reason' => 'Run customer churn analysis model validation by sampling transactional behavior data for customers inactive for more than 90 days. The output will feed into the data science pipeline.',
                'intended_query' => "SELECT c.customer_id,
                        c.signup_date,
                        SUM(o.total_amount) AS total_spent,
                        COUNT(o.id) AS orders_count,
                        MAX(o.order_date) AS last_order_date
                    FROM customers c
                    LEFT JOIN orders o ON c.customer_id = o.customer_id
                    WHERE c.status = 'inactive'
                    AND c.last_login < NOW() - INTERVAL 90 DAY
                    GROUP BY c.customer_id, c.signup_date
                    HAVING total_spent > 0
                    LIMIT 5000;
                ",
                'scope' => DatabaseScope::READ_ONLY,
                'is_access_sensitive_data' => true,
                'sensitive_data_note' => 'Contains customer IDs, transaction summaries, and engagement timestamps; may reveal financial behavior and personal identifiers.',
            ],
            [
                'reason' => 'Fix mismatched account balances discovered during financial reconciliation between the ledger and payments tables after a system upgrade.',
                'intended_query' => "UPDATE ledger l
                    JOIN payments p ON l.transaction_id = p.transaction_id
                    SET l.balance = p.amount
                    WHERE ABS(l.balance - p.amount) > 0.01
                    AND l.reconciled = 0
                    AND l.transaction_date BETWEEN '2025-09-01' AND '2025-09-30';
                ",
                'scope' => DatabaseScope::READ_WRITE,
                'is_access_sensitive_data' => true,
                'sensitive_data_note' => 'Involves financial account balances, transaction IDs, and amounts; potential exposure of sensitive monetary records.',
            ],
            [
                'reason' => 'Diagnose replication lag issue between the analytics replica and production master database. Need to inspect binary log positions and table row counts to identify desynchronization cause.',
                'intended_query' => "SHOW MASTER STATUS;
                    SHOW SLAVE STATUS\G;
                    SELECT table_name, table_rows
                    FROM information_schema.tables
                    WHERE table_schema = 'analytics'
                    ORDER BY table_rows DESC
                    LIMIT 10;
                ",
                'scope' => DatabaseScope::ALL,
                'is_access_sensitive_data' => false,
                'sensitive_data_note' => null,
            ],
        ];

        $request = $this->faker->randomElement($requests);

        return [
            'org_id' => Org::factory(),
            'asset_id' => Asset::factory(),
            'requester_id' => User::factory(),
            'start_datetime' => $startDatetime,
            'end_datetime' => $endDatetime,
            'duration' => $duration,
            'reason' => $request['reason'],
            'intended_query' => $request['intended_query'],
            'scope' => $request['scope'],
            'is_access_sensitive_data' => $request['is_access_sensitive_data'],
            'sensitive_data_note' => $request['sensitive_data_note'],
            'status' => RequestStatus::PENDING,
        ];
    }

    public function sensitiveData(): static
    {
        $isSensitiveData = $this->faker->boolean();

        return $this->state(fn (array $attributes) => [
            'is_access_sensitive_data' => $isSensitiveData,
            'sensitive_data_note' => $isSensitiveData ? fake()->paragraph() : null,
        ]);
    }

    public function approved(): static
    {
        $this->approverNodeAndRiskRating();

        return $this->state(fn (array $attributes) => [
            'status' => RequestStatus::APPROVED->value,
            'approved_by' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'approved_at' => fake()->dateTimeBetween($attributes['start_datetime'], $attributes['end_datetime']),
        ]);
    }

    public function rejected(): static
    {
        $this->approverNodeAndRiskRating();

        return $this->state(fn (array $attributes) => [
            'status' => RequestStatus::REJECTED->value,
            'rejected_by' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'rejected_at' => fake()->dateTimeBetween($attributes['start_datetime'], $attributes['end_datetime']),
        ]);
    }

    private function approverNodeAndRiskRating(): static
    {
        return $this->state(fn (array $attributes) => [
            'approver_note' => fake()->paragraph(),
            'approver_risk_rating' => fake()->randomElement(RiskRating::cases()),
        ]);
    }
}
