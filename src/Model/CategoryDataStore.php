<?php
namespace Models;

use Models\DataStoreInterface;

class CategoryDataStore implements DataStoreInterface
{
  private $pdo;
  private $table = 'categories';

  public function __construct()
    {
        $config = require __DIR__ . '/../config/db.php';
        $dbConfig = $config['database'];

        $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false
        ];

        $this->pdo = new \PDO($dsn, $dbConfig['username'], $dbConfig['password'], $options);
        $this->initTables();
    }

    private function initTables()
    {
        $this->pdo->exec("
        CREATE TABLE IF NOT EXISTS categories (
            id VARCHAR(255)
                CHARACTER SET utf8mb4
                COLLATE utf8mb4_unicode_ci
                PRIMARY KEY,
            name VARCHAR(255)
                CHARACTER SET utf8mb4
                COLLATE utf8mb4_unicode_ci,
            created_at DATETIME,
            updated_at DATETIME
        )
        ENGINE=InnoDB
        DEFAULT CHARSET=utf8mb4
        COLLATE=utf8mb4_unicode_ci");
    }

    public function create($data)
    {
        $stmt = $this->pdo->prepare("INSERT INTO {$this->table} (id, name, created_at, updated_at) VALUES (:id, :name, NOW(), NOW())");
        $stmt->execute([
            ':id' => $data['id'],
            ':name' => $data['name']
        ]);
    }

    public function getById(string $id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function getAll()
    {
        $stmt = $this->pdo->query("SELECT * FROM {$this->table} ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    public function update(string $id, array $data)
    {
        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET name = :name, updated_at = NOW() WHERE id = :id");
        $stmt->execute([
            ':id' => $id,
            ':name' => $data['name']
        ]);
    }

    public function delete(string $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }
}