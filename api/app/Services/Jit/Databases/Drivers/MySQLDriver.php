<?php

namespace App\Services\Jit\Databases\Drivers;

use App\Enums\DatabaseScope;
use App\Services\Jit\Databases\Models\Query;
use DateTime;
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
                ]
            );
        } catch (Exception $e) {
            $this->handleError('connect', $e);
        }
    }

    protected function getDsn(array $credentials): string
    {
        return sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $credentials['host'],
            $credentials['port'] ?? config('pam.database.connection.mysql_port', 3306),
            $credentials['database'] ?? ''
        );
    }

    public function getAllDatabases(): array
    {
        return $this->connection
            ->query('SHOW DATABASES')
            ->fetchAll(PDO::FETCH_COLUMN) ?? [];
    }

    public function createUser(string $username, string $password, string|array $databases, DatabaseScope $scope, DateTime $expiresAt): bool
    {
        try {
            // Ensure we have a fresh connection
            if (!isset($this->connection) || $this->connection === null) {
                $this->connect($this->config['db']);
            }
            // Begin transaction
            $this->connection->beginTransaction();
            try {
                // Create user (without password expiration for now)
                $sql = "CREATE USER :username@'%' IDENTIFIED BY :password";
                $stmt = $this->connection->prepare($sql);
                $stmt->execute([
                    ':username' => $username,
                    ':password' => $password,
                ]);
                // Grant permissions based on scope and databases
                $this->grantPermissions($username, $databases, $scope);
                // Flush privileges
                $this->connection->exec('FLUSH PRIVILEGES');
                // Commit transaction
                $this->connection->commit();
                return true;
            } catch (Exception $e) {
                // Rollback transaction on any error
                $this->connection->rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            $this->handleError('createUser', $e, ['username' => $username]);
            return false;
        }
    }

    private function grantPermissions(string $username, string|array $databases, DatabaseScope $scope): void
    {
        $userHost = "'{$username}'@'%'";
        $normalizedDatabases = $this->normalizeDatabases($databases);
        $hasAllAccess = $this->hasAllDatabaseAccess($databases);

        // Get the appropriate privileges for the scope
        $privileges = $this->getPrivilegesForScope($scope);

        if ($hasAllAccess) {
            // Grant access to all databases
            $this->connection->exec("GRANT {$privileges} ON *.* TO {$userHost}");
        } else {
            // Grant access to specific databases
            foreach ($normalizedDatabases as $database) {
                $this->connection->exec("GRANT {$privileges} ON `{$database}`.* TO {$userHost}");
            }
        }
    }

    /**
     * Get the appropriate privileges string for the given scope
     */
    private function getPrivilegesForScope(DatabaseScope $scope): string
    {
        return match ($scope) {
            DatabaseScope::READ_ONLY => 'SELECT',
            DatabaseScope::READ_WRITE, DatabaseScope::DML => 'SELECT, INSERT, UPDATE, DELETE',
            DatabaseScope::DDL => 'SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, ALTER, INDEX',
            DatabaseScope::ALL => 'ALL PRIVILEGES',
        };
    }

    public function terminateUser(string $username, string|array $databases): bool
    {
        try {
            if (!isset($this->connection) || $this->connection === null) {
                $this->connect($this->config['db']);
            }
            $this->connection->beginTransaction();
            try {
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
                $this->connection->exec("REVOKE ALL PRIVILEGES, GRANT OPTION FROM '{$username}'@'%' ");
                $this->connection->exec("DROP USER IF EXISTS '{$username}'@'%' ");
                $this->connection->exec('FLUSH PRIVILEGES');
                $this->connection->commit();
                return true;
            } catch (Exception $e) {
                $this->connection->rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            $this->handleError('terminateUser', $e, ['username' => $username]);
            return false;
        }
    }

    public function isQueryLoggingEnabled(): bool
    {
        $logCheck = $this->connection->query("SHOW VARIABLES LIKE 'general_log'");
        $logOutput = $this->connection->query("SHOW VARIABLES LIKE 'log_output'");
        return $logCheck->fetch()['Value'] === 'ON' && $logOutput->fetch()['Value'] === 'TABLE';
    }

    public function enableQueryLogging(): void
    {
        $this->connection->exec('SET GLOBAL general_log = ON');
        $this->connection->exec("SET GLOBAL log_output = 'TABLE'");
    }

    public function disableQueryLogging(): void
    {
        $this->connection->exec('SET GLOBAL general_log = OFF');
        $this->connection->exec("SET GLOBAL log_output = 'FILE'");
    }

    public function retrieveUserQueryLogs(string $username): array
    {
        try {
            // Check if general log is enabled and is a table
            if (!$this->isQueryLoggingEnabled()) {
                return [];
            }
            $sql = "SELECT
                    MIN(user_host) AS user_host,
                    MIN(event_time) AS first_timestamp,
                    MAX(event_time) AS last_timestamp,
                    command_type,
                    COUNT(*) AS count,
                    CAST(argument AS CHAR) AS argument
                FROM mysql.general_log 
                WHERE command_type NOT IN ('Quit', 'Close stmt', 'Init DB', 'Connect')
                AND user_host LIKE '%test%'
                AND CAST(argument AS CHAR) NOT REGEXP '(SET NAMES|SET sql_quote_show_create|SET CHARACTER SET|SET character_set|SET collation_connection|SET lc_messages|SELECT USER()|CONNECTION_ID|current_user|SELECT @@|SHOW |information_schema|performance_schema|SELECT DATABASE|SET autocommit)'
                GROUP BY CAST(argument AS CHAR), command_type
                ORDER BY MAX(event_time) DESC";

            $stmt = $this->connection->prepare($sql);
            $stmt->execute([
                ':username_pattern' => $username.'%',
            ]);
            return array_map(Query::fromArray(...), $stmt->fetchAll());
        } catch (Exception $e) {
            $this->handleError('retrieveUserQueryLogs', $e, ['username' => $username]);
            return [];
        }
    }
}
