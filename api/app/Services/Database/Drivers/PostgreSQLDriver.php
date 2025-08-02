<?php

namespace App\Services\Database\Drivers;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use PDO;
use PDOException;

class PostgreSQLDriver extends AbstractDatabaseDriver
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
                ]
            );
        } catch (PDOException $e) {
            $this->handleError('connect', $e);
        }
    }

    protected function getDsn(array $credentials): string
    {
        return sprintf(
            'pgsql:host=%s;port=%s;dbname=%s;user=%s;password=%s',
            $credentials['host'],
            $credentials['port'] ?? 5432,
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

            // Create user with expiration
            $sql = 'CREATE USER :username WITH PASSWORD :password VALID UNTIL :expires';
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([
                ':username' => $username,
                ':password' => $password,
                ':expires' => $expiresAt->toDateTimeString(),
            ]);

            // Grant permissions based on scope
            $this->grantPermissions($username, $database, $scope);

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
        // Grant connection to database
        $this->connection->exec("GRANT CONNECT ON DATABASE {$database} TO {$username}");

        switch ($scope) {
            case 'read':
                $this->connection->exec("GRANT USAGE ON SCHEMA public TO {$username}");
                $this->connection->exec("GRANT SELECT ON ALL TABLES IN SCHEMA public TO {$username}");
                $this->connection->exec("ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT SELECT ON TABLES TO {$username}");
                break;

            case 'write':
                $this->connection->exec("GRANT USAGE ON SCHEMA public TO {$username}");
                $this->connection->exec("GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO {$username}");
                $this->connection->exec("GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO {$username}");
                $this->connection->exec("ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT SELECT, INSERT, UPDATE, DELETE ON TABLES TO {$username}");
                $this->connection->exec("ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT USAGE, SELECT ON SEQUENCES TO {$username}");
                break;

            case 'admin':
                $this->connection->exec("GRANT ALL PRIVILEGES ON DATABASE {$database} TO {$username}");
                $this->connection->exec("GRANT ALL PRIVILEGES ON SCHEMA public TO {$username}");
                $this->connection->exec("GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO {$username}");
                $this->connection->exec("GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO {$username}");
                break;
        }
    }

    public function terminateUser(string $username, string $database): bool
    {
        $this->logOperation('terminateUser', ['username' => $username, 'database' => $database]);

        try {
            $this->connection->beginTransaction();

            // Terminate active connections
            $sql = 'SELECT pg_terminate_backend(pid) FROM pg_stat_activity 
                    WHERE usename = :username AND pid <> pg_backend_pid()';
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([':username' => $username]);

            // Revoke all privileges
            $this->connection->exec("REVOKE ALL PRIVILEGES ON DATABASE {$database} FROM {$username}");
            $this->connection->exec("REVOKE ALL PRIVILEGES ON SCHEMA public FROM {$username}");
            $this->connection->exec("REVOKE ALL PRIVILEGES ON ALL TABLES IN SCHEMA public FROM {$username}");
            $this->connection->exec("REVOKE ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public FROM {$username}");

            // Drop user
            $this->connection->exec("DROP USER IF EXISTS {$username}");

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
            $sql = "SELECT 
                        query_start as timestamp,
                        query as query_text,
                        application_name,
                        client_addr
                    FROM pg_stat_activity 
                    WHERE usename = :username 
                    AND query_start BETWEEN :from_time AND :to_time
                    AND query NOT LIKE 'BEGIN%'
                    AND query NOT LIKE 'COMMIT%'
                    AND query NOT LIKE 'ROLLBACK%'
                    ORDER BY query_start DESC";

            $stmt = $this->connection->prepare($sql);
            $stmt->execute([
                ':username' => $username,
                ':from_time' => $fromTime->toDateTimeString(),
                ':to_time' => $toTime->toDateTimeString(),
            ]);

            $logs = $stmt->fetchAll();

            // Also check pg_stat_statements if available
            try {
                $extSql = "SELECT 1 FROM pg_extension WHERE extname = 'pg_stat_statements'";
                $extStmt = $this->connection->query($extSql);

                if ($extStmt->fetch()) {
                    $statSql = "SELECT 
                                    calls,
                                    total_exec_time,
                                    mean_exec_time,
                                    query
                                FROM pg_stat_statements 
                                WHERE userid = (SELECT oid FROM pg_user WHERE usename = :username)
                                AND query NOT LIKE 'BEGIN%'
                                AND query NOT LIKE 'COMMIT%'";

                    $statStmt = $this->connection->prepare($statSql);
                    $statStmt->execute([':username' => $username]);

                    $statements = $statStmt->fetchAll();
                    foreach ($statements as $statement) {
                        $logs[] = [
                            'timestamp' => $fromTime->toDateTimeString(),
                            'query_text' => $statement['query'],
                            'execution_count' => $statement['calls'],
                            'avg_execution_time' => $statement['mean_exec_time'],
                        ];
                    }
                }
            } catch (Exception $e) {
                // pg_stat_statements might not be available
                Log::debug('pg_stat_statements not available', ['error' => $e->getMessage()]);
            }

            $this->logOperation('retrieveUserQueryLogs.success', ['count' => count($logs)]);
            return $logs;

        } catch (Exception $e) {
            $this->handleError('retrieveUserQueryLogs', $e, ['username' => $username]);
            return [];
        }
    }
}
