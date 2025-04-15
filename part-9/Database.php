<?php

namespace App;

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use PDO;
use PDOException;

class Database
{
    private $connection;
    private Logger $logger;

    public function __construct(string $host, string $user, string $password, string $databaseName)
    {
        $this->logger = new Logger('Database');
        $this->logger->pushHandler(new StreamHandler(__DIR__ . '/logs/database.log', Level::Debug));

        try {
            $dsn = "mysql:host=$host;dbname=$databaseName;charset=utf8mb4";
            $this->connection = new PDO($dsn, $user, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            $this->logger->info("Connected to database", ['dsn' => $dsn]);
        } catch (PDOException $e) {
            $this->logger->error("Database connection failed", ['message' => $e->getMessage()]);
            throw new \RuntimeException('Failed to connect to the database: ' . $e->getMessage());
        }
    }

    public function ensureTasksTableExists(): void
    {
        try {
            $query = "
                CREATE TABLE IF NOT EXISTS tasks (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    description VARCHAR(255) NOT NULL,
                    state TINYINT(1) NOT NULL DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )
            ";
            $this->connection->exec($query);
        } catch (PDOException $e) {
            throw new \RuntimeException('Failed to create or verify tasks table: ' . $e->getMessage());
        }
    }

    public function addTask(string $description): void
    {
        try {
            $query = "INSERT INTO tasks (description, state) VALUES (:description, :state)";
            $statement = $this->connection->prepare($query);
            $statement->execute([
                'description' => $description,
                'state' => 0,
            ]);
        } catch (PDOException $e) {
            throw new \RuntimeException('Failed to add task: ' . $e->getMessage());
        }
    }

    public function changeTaskState(int $taskId, int $state): void
    {
        try {
            $query = "UPDATE tasks SET state = :state, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
            $statement = $this->connection->prepare($query);
            $statement->execute([
                'id' => $taskId,
                'state' => $state
            ]);

            if ($statement->rowCount() === 0) {
                throw new \RuntimeException('No rows were updated. Task ID may not exist.');
            }
        } catch (PDOException $e) {
            throw new \RuntimeException('Failed to update task state: ' . $e->getMessage());
        }
    }

    public function deleteTask(int $taskId): void
    {
        try {
            $query = "DELETE FROM tasks WHERE id = :id";
            $statement = $this->connection->prepare($query);
            $statement->execute(['id' => $taskId]);

            if ($statement->rowCount() === 0) {
                throw new \RuntimeException('No rows were deleted. Task ID may not exist.');
            }
        } catch (PDOException $e) {
            throw new \RuntimeException('Failed to delete task: ' . $e->getMessage());
        }
    }

    public function getConnection()
    {
        if (isset($this->connection)) {
            return $this->connection;
        } else {
            $this->logger->error("Database connection not initialized.");
            throw new \RuntimeException('Database connection is not initialized.');
        }
    }

}