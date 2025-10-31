<?php

namespace App\Services\Jit\Databases\Drivers;

use App\Enums\DatabaseScope;
use DateTime;
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

    public function getAllDatabases(): array
    {
        return $this->connection
            ->query('SELECT datname FROM pg_database')
            ->fetchAll(PDO::FETCH_COLUMN) ?? [];
    }

    public function testConnection(array $credentials): bool
    {
        try {
            $dsn = $this->getDsn($credentials);
            $testConnection = new PDO(
                $dsn,
                $credentials['username'],
                $credentials['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );

            // Test the connection by executing a simple query
            $testConnection->query('SELECT 1');
            $testConnection = null; // Close the test connection

            return true;
        } catch (Exception $e) {
            Log::debug('Connection test failed', [
                'host' => $credentials['host'] ?? 'unknown',
                'port' => $credentials['port'] ?? 'unknown',
                'database' => $credentials['database'] ?? 'unknown',
                'username' => $credentials['username'] ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function createUser(string $username, string $password, string|array $databases, DatabaseScope $scope, DateTime $expiresAt): bool
    {
        $normalizedDatabases = $this->normalizeDatabases($databases);
        try {
            // Ensure we have a fresh connection
            if (!isset($this->connection) || $this->connection === null) {
                $this->connect($this->config['db']);
            }
            $this->connection->beginTransaction();

            // Create user with expiration
            $sql = 'CREATE USER :username WITH PASSWORD :password VALID UNTIL :expires';
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([
                ':username' => $username,
                ':password' => $password,
                ':expires' => $expiresAt->format('Y-m-d H:i:s'),
            ]);

            // Grant permissions based on scope and databases
            $this->grantPermissions($username, $databases, $scope);

            $this->connection->commit();
            return true;

        } catch (Exception $e) {
            $this->connection->rollBack();
            $this->handleError('createUser', $e, ['username' => $username]);
            return false;
        }
    }

    private function grantPermissions(string $username, string|array $databases, DatabaseScope $scope): void
    {
        $normalizedDatabases = $this->normalizeDatabases($databases);
        $hasAllAccess = $this->hasAllDatabaseAccess($databases);

        if ($hasAllAccess) {
            // Grant access to all databases
            $this->grantAllDatabaseAccess($username, $scope);
        } else {
            // Grant access to specific databases
            foreach ($normalizedDatabases as $database) {
                $this->grantDatabaseAccess($username, $database, $scope);
            }
        }
    }

    /**
     * Grant access to all databases
     */
    private function grantAllDatabaseAccess(string $username, DatabaseScope $scope): void
    {
        switch ($scope) {
            case DatabaseScope::READ_ONLY:
                $this->connection->exec("GRANT CONNECT ON ALL DATABASES TO {$username}");
                $this->connection->exec("GRANT USAGE ON ALL SCHEMAS TO {$username}");
                $this->connection->exec("GRANT SELECT ON ALL TABLES IN ALL SCHEMAS TO {$username}");
                break;

            case DatabaseScope::READ_WRITE:
            case DatabaseScope::DML:
                $this->connection->exec("GRANT CONNECT ON ALL DATABASES TO {$username}");
                $this->connection->exec("GRANT USAGE ON ALL SCHEMAS TO {$username}");
                $this->connection->exec("GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN ALL SCHEMAS TO {$username}");
                $this->connection->exec("GRANT USAGE, SELECT ON ALL SEQUENCES IN ALL SCHEMAS TO {$username}");
                break;

            case DatabaseScope::DDL:
                $this->connection->exec("GRANT CONNECT ON ALL DATABASES TO {$username}");
                $this->connection->exec("GRANT USAGE ON ALL SCHEMAS TO {$username}");
                $this->connection->exec("GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, ALTER ON ALL TABLES IN ALL SCHEMAS TO {$username}");
                $this->connection->exec("GRANT USAGE, SELECT ON ALL SEQUENCES IN ALL SCHEMAS TO {$username}");
                break;

            case DatabaseScope::ALL:
                $this->connection->exec("GRANT ALL PRIVILEGES ON ALL DATABASES TO {$username}");
                $this->connection->exec("GRANT ALL PRIVILEGES ON ALL SCHEMAS TO {$username}");
                $this->connection->exec("GRANT ALL PRIVILEGES ON ALL TABLES IN ALL SCHEMAS TO {$username}");
                $this->connection->exec("GRANT ALL PRIVILEGES ON ALL SEQUENCES IN ALL SCHEMAS TO {$username}");
                break;
        }
    }

    /**
     * Grant access to a specific database
     */
    private function grantDatabaseAccess(string $username, string $database, DatabaseScope $scope): void
    {
        // Grant connection to database
        $this->connection->exec("GRANT CONNECT ON DATABASE {$database} TO {$username}");

        switch ($scope) {
            case DatabaseScope::READ_ONLY:
                $this->connection->exec("GRANT USAGE ON SCHEMA public TO {$username}");
                $this->connection->exec("GRANT SELECT ON ALL TABLES IN SCHEMA public TO {$username}");
                $this->connection->exec("ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT SELECT ON TABLES TO {$username}");
                break;

            case DatabaseScope::READ_WRITE:
            case DatabaseScope::DML:
                $this->connection->exec("GRANT USAGE ON SCHEMA public TO {$username}");
                $this->connection->exec("GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO {$username}");
                $this->connection->exec("GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO {$username}");
                $this->connection->exec("ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT SELECT, INSERT, UPDATE, DELETE ON TABLES TO {$username}");
                $this->connection->exec("ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT USAGE, SELECT ON SEQUENCES TO {$username}");
                break;

            case DatabaseScope::DDL:
                $this->connection->exec("GRANT USAGE ON SCHEMA public TO {$username}");
                $this->connection->exec("GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, ALTER ON ALL TABLES IN SCHEMA public TO {$username}");
                $this->connection->exec("GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO {$username}");
                $this->connection->exec("ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, ALTER ON TABLES TO {$username}");
                $this->connection->exec("ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT USAGE, SELECT ON SEQUENCES TO {$username}");
                break;

            case DatabaseScope::ALL:
                $this->connection->exec("GRANT ALL PRIVILEGES ON DATABASE {$database} TO {$username}");
                $this->connection->exec("GRANT ALL PRIVILEGES ON SCHEMA public TO {$username}");
                $this->connection->exec("GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO {$username}");
                $this->connection->exec("GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO {$username}");
                break;
        }
    }

    public function terminateUser(string $username, string|array $databases): bool
    {
        try {
            // Ensure we have a fresh connection
            if (!isset($this->connection) || $this->connection === null) {
                $this->connect($this->config['db']);
            }
            $this->connection->beginTransaction();

            // Terminate active connections
            $sql = 'SELECT pg_terminate_backend(pid) FROM pg_stat_activity 
                    WHERE usename = :username AND pid <> pg_backend_pid()';
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([':username' => $username]);

            // Revoke privileges for specified databases or all if empty
            $dbList = is_array($databases) ? $databases : (empty($databases) ? [] : [$databases]);
            if (empty($dbList)) {
                // Best-effort revoke on public schema when databases are not specified
                $this->connection->exec("REVOKE ALL PRIVILEGES ON SCHEMA public FROM {$username}");
                $this->connection->exec("REVOKE ALL PRIVILEGES ON ALL TABLES IN SCHEMA public FROM {$username}");
                $this->connection->exec("REVOKE ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public FROM {$username}");
            } else {
                foreach ($dbList as $dbName) {
                    $this->connection->exec("REVOKE ALL PRIVILEGES ON DATABASE {$dbName} FROM {$username}");
                }
                $this->connection->exec("REVOKE ALL PRIVILEGES ON SCHEMA public FROM {$username}");
                $this->connection->exec("REVOKE ALL PRIVILEGES ON ALL TABLES IN SCHEMA public FROM {$username}");
                $this->connection->exec("REVOKE ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public FROM {$username}");
            }

            // Drop user
            $this->connection->exec("DROP USER IF EXISTS {$username}");

            $this->connection->commit();
            return true;

        } catch (Exception $e) {
            $this->connection->rollBack();
            $this->handleError('terminateUser', $e, ['username' => $username]);
            return false;
        }
    }

    public function retrieveUserQueryLogs(string $username): array
    {
        try {
            $sql = "SELECT 
                        query_start as timestamp,
                        query as query_text,
                        application_name,
                        client_addr
                    FROM pg_stat_activity 
                    WHERE usename = :username 
                    AND query NOT LIKE 'BEGIN%'
                    AND query NOT LIKE 'COMMIT%'
                    AND query NOT LIKE 'ROLLBACK%'
                    ORDER BY query_start DESC";

            $stmt = $this->connection->prepare($sql);
            $stmt->execute([
                ':username' => $username,
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
                            'timestamp' => date('Y-m-d H:i:s'),
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
            return $logs;
        } catch (Exception $e) {
            $this->handleError('retrieveUserQueryLogs', $e, ['username' => $username]);
            return [];
        }
    }

    public function isQueryLoggingEnabled(): bool
    {
        // PostgreSQL does not have a general log table like MySQL; return false by default
        return false;
    }

    public function enableQueryLogging(): void
    {
        // No-op: enabling detailed query logging would require server config changes (superuser)
    }

    public function disableQueryLogging(): void
    {
        // No-op: see above
    }
}
