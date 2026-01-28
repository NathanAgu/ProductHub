<?php
namespace Models;

use \Models\DataStoreInterface;

class ProductPdoDataStore implements DataStoreInterface
{
    private $pdo;
    private $table;

    public function __construct($table = 'products')
    {
        $this->table = $table;

        // Charger la configuration
        $config = require __DIR__ . '/../config/db.php';
        $dbConfig = $config['database'];

        $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false
        ];

        $this->pdo = new \PDO($dsn, $dbConfig['username'], $dbConfig['password'], $options);
        $this->initTable();
    }

private function initTable()
{
    $sql = "
        CREATE TABLE IF NOT EXISTS `{$this->table}` (
            id VARCHAR(255)
                CHARACTER SET utf8mb4
                COLLATE utf8mb4_unicode_ci
                PRIMARY KEY,
            name VARCHAR(255)
                CHARACTER SET utf8mb4
                COLLATE utf8mb4_unicode_ci
                NOT NULL,
            description TEXT
                CHARACTER SET utf8mb4
                COLLATE utf8mb4_unicode_ci,
            price DECIMAL(10, 2) DEFAULT 0,
            stock INT DEFAULT 0,
            created_at DATETIME,
            updated_at DATETIME,
            INDEX idx_name (name),
            INDEX idx_created_at (created_at),
            INDEX idx_updated_at (updated_at)
        )
        ENGINE=InnoDB
        DEFAULT CHARSET=utf8mb4
        COLLATE=utf8mb4_unicode_ci
    ";

    $this->pdo->exec($sql);
}


    public function getAll()
    {
        $stmt = $this->pdo->query("SELECT * FROM `{$this->table}` ORDER BY created_at DESC");
        $rows = $stmt->fetchAll();

        $items = [];
        foreach ($rows as $row) {
            $items[$row['id']] = $row;
        }
        return $items;
    }

    public function getById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM `{$this->table}` WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function getByName($name)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM `{$this->table}` WHERE LOWER(name) LIKE LOWER(?)");
        $stmt->execute(['%' . $name . '%']);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $id = uniqid();
        $createdAt = date('Y-m-d H:i:s');

        $name = $data['name'] ?? '';
        $description = $data['description'] ?? '';
        $price = $data['price'] ?? 0;
        $stock = $data['stock'] ?? 0;

        $stmt = $this->pdo->prepare(
            "INSERT INTO `{$this->table}` (id, name, description, price, stock, created_at) 
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$id, $name, $description, $price, $stock, $createdAt]);

        return [
            'id' => $id,
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'stock' => $stock,
            'created_at' => $createdAt,
            'updated_at' => null
        ];
    }

    public function update($id, $data):array
    {
        // Vérifier si l'élément existe
        $existing = $this->getById($id);
        if (!$existing) {
            return null;
        }

        $updatedAt = date('Y-m-d H:i:s');

        $name = $data['name'] ?? $existing['name'];
        $description = $data['description'] ?? $existing['description'];
        $price = $data['price'] ?? $existing['price'];
        $stock = $data['stock'] ?? $existing['stock'];

        $stmt = $this->pdo->prepare(
            "UPDATE `{$this->table}` 
             SET name = ?, description = ?, price = ?, stock = ?, updated_at = ? 
             WHERE id = ?"
        );
        $stmt->execute([$name, $description, $price, $stock, $updatedAt, $id]);

        return [
            'id' => $id,
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'stock' => $stock,
            'created_at' => $existing['created_at'],
            'updated_at' => $updatedAt
        ];
    }

    public function delete(string $id):bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM `{$this->table}` WHERE id = ?");
        $stmt->execute([$id]);

        return $stmt->rowCount() > 0;
    }
}