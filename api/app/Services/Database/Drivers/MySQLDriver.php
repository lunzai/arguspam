<?php

namespace App\Services\Database\Drivers;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use PDO;

class MySQLDriver extends AbstractDatabaseDriver
{
    protected function connect(array $credentials): void
    {
        try {
            $dsn = $this->getDsn($credentials);
            $this->connection = new PDO(
                $dsn,
                $credentials['username'],
                $credentials['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4',
                ]
            );
        } catch (Exception $e) {
            $this->handleError('connect', $e);
        }
    }

    protected function getDsn(array $credentials): string
    {
        return sprintf(
            'mysql:host=%s;port=%s;dbname=%s;user=%s;password=%s;charset=utf8mb4',
            $credentials['host'],
            $credentials['port'] ?? 3306,
            $credentials['database'] ?? '',
            $credentials['username'] ?? '',
            $credentials['password'] ?? ''
        );
    }

    public function createUser(string $username, string $password, string $database, string $scope, Carbon $expiresAt): bool
    {
        $this->logOperation('createUser', ['username' => $username, 'database' => $database, 'scope' => $scope]);

        try {
            $this->connection->beginTransaction();

            // Create user with password expiration
            $sql = "CREATE USER :username@'%' IDENTIFIED BY :password 
                    PASSWORD EXPIRE INTERVAL :days DAY";

            $daysUntilExpiry = now()->diffInDays($expiresAt);

            $stmt = $this->connection->prepare($sql);
            $stmt->execute([
                ':username' => $username,
                ':password' => $password,
                ':days' => $daysUntilExpiry,
            ]);

            // Grant permissions based on scope
            $this->grantPermissions($username, $database, $scope);

            // Flush privileges
            $this->connection->exec('FLUSH PRIVILEGES');

            $this->connection->commit();
            $this->logOperation('createUser.success', ['username' => $username]);
            return true;

        } catch (Exception $e) {
            $this->connection->rollBack();
            $this->handleError('createUser', $e, ['username' => $username]);
            return false;
        }
    }

    private function grantPermissions(string $username, string $database, string $scope): void
    {
        $userHost = "'{$username}'@'%'";

        switch ($scope) {
            case 'read_only':
                $this->connection->exec("GRANT SELECT ON `{$database}`.* TO {$userHost}");
                break;

            case 'read_write':
                $this->connection->exec("GRANT SELECT, INSERT, UPDATE, DELETE ON `{$database}`.* TO {$userHost}");
                break;

            case 'admin':
                $this->connection->exec("GRANT ALL PRIVILEGES ON `{$database}`.* TO {$userHost} WITH GRANT OPTION");
                break;
        }
    }

    public function terminateUser(string $username, string $database): bool
    {
        $this->logOperation('terminateUser', ['username' => $username, 'database' => $database]);

        try {
            $this->connection->beginTransaction();

            // Kill active connections
            $sql = "SELECT CONCAT('KILL ', id, ';') AS kill_command 
                    FROM information_schema.processlist 
                    WHERE user = :username";

            $stmt = $this->connection->prepare($sql);
            $stmt->execute([':username' => $username]);

            $killCommands = $stmt->fetchAll(PDO::FETCH_COLUMN);
            foreach ($killCommands as $command) {
                try {
                    $this->connection->exec($command);
                } catch (Exception $e) {
                    // Connection might already be terminated
                    Log::debug('Failed to kill connection', ['command' => $command]);
                }
            }

            // Revoke all privileges
            $this->connection->exec("REVOKE ALL PRIVILEGES, GRANT OPTION FROM '{$username}'@'%'");

            // Drop user
            $this->connection->exec("DROP USER IF EXISTS '{$username}'@'%'");

            // Flush privileges
            $this->connection->exec('FLUSH PRIVILEGES');

            $this->connection->commit();
            $this->logOperation('terminateUser.success', ['username' => $username]);
            return true;

        } catch (Exception $e) {
            $this->connection->rollBack();
            $this->handleError('terminateUser', $e, ['username' => $username]);
            return false;
        }
    }

    public function retrieveUserQueryLogs(string $username, Carbon $fromTime, Carbon $toTime): array
    {
        $this->logOperation('retrieveUserQueryLogs', ['username' => $username]);

        try {
            $logs = [];

            // Check if general log is enabled and is a table
            $logCheck = $this->connection->query("SHOW VARIABLES LIKE 'general_log'");
            $logOutput = $this->connection->query("SHOW VARIABLES LIKE 'log_output'");

            $isLogEnabled = $logCheck->fetch()['Value'] ?? 'OFF';
            $logOutputType = $logOutput->fetch()['Value'] ?? '';

            if ($isLogEnabled === 'ON' && str_contains($logOutputType, 'TABLE')) {
                $sql = "SELECT 
                            event_time as timestamp,
                            argument as query_text
                        FROM mysql.general_log 
                        WHERE user_host LIKE :username_pattern
                        AND event_time BETWEEN :from_time AND :to_time
                        AND command_type = 'Query'
                        AND argument NOT LIKE 'BEGIN%'
                        AND argument NOT LIKE 'COMMIT%'
                        AND argument NOT LIKE 'ROLLBACK%'
                        AND argument NOT LIKE 'USE %'
                        ORDER BY event_time DESC";

                $stmt = $this->connection->prepare($sql);
                $stmt->execute([
                    ':username_pattern' => $username.'%',
                    ':from_time' => $fromTime->toDateTimeString(),
                    ':to_time' => $toTime->toDateTimeString(),
                ]);

                $logs = $stmt->fetchAll();
            }

            // Try performance_schema if available
            try {
                $sql = "SELECT 
                            TIMER_START,
                            SQL_TEXT as query_text
                        FROM performance_schema.events_statements_history_long
                        WHERE USER = :username
                        AND SQL_TEXT IS NOT NULL
                        AND SQL_TEXT NOT LIKE 'BEGIN%'
                        AND SQL_TEXT NOT LIKE 'COMMIT%'
                        ORDER BY TIMER_START DESC
                        LIMIT 1000";

                $stmt = $this->connection->prepare($sql);
                $stmt->execute([':username' => $username]);

                $perfLogs = $stmt->fetchAll();
                foreach ($perfLogs as $log) {
                    $logs[] = [
                        'timestamp' => now()->toDateTimeString(), // Approximate
                        'query_text' => $log['query_text'],
                    ];
                }
            } catch (Exception $e) {
                Log::debug('Performance schema not available', ['error' => $e->getMessage()]);
            }

            $this->logOperation('retrieveUserQueryLogs.success', ['count' => count($logs)]);
            return $logs;

        } catch (Exception $e) {
            $this->handleError('retrieveUserQueryLogs', $e, ['username' => $username]);
            return [];
        }
    }
}

// SELECT
//     t.processlist_user as user,
//     t.processlist_host as host,
//     esh.sql_text,
//     esh.current_schema as db,
//     ROUND(esh.timer_wait/1000000000, 2) as duration_ms,
//     esh.rows_examined,
//     esh.rows_sent
// FROM performance_schema.events_statements_history esh
// JOIN performance_schema.threads t ON esh.thread_id = t.thread_id
// WHERE esh.sql_text IS NOT NULL
// AND t.processlist_user = 'root'
// ORDER BY esh.event_id DESC
// LIMIT 30;
