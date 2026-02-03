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
            brand VARCHAR(100)
                CHARACTER SET utf8mb4
                COLLATE utf8mb4_unicode_ci,
            color VARCHAR(50)
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

    public function getAllBrands()
    {
        $stmt = $this->pdo->query("SELECT DISTINCT brand FROM `{$this->table}` WHERE brand IS NOT NULL AND brand != '' ORDER BY brand");
        $brands = $stmt->fetchAll(\PDO::FETCH_COLUMN, 0);
        
        return $brands ?: [];
    }

    public function getAllColors()
    {
        $stmt = $this->pdo->query("SELECT DISTINCT color FROM `{$this->table}` WHERE color IS NOT NULL AND color != '' ORDER BY color");
        $colors = $stmt->fetchAll(\PDO::FETCH_COLUMN, 0);

        return $colors ?: [];
    }

    public function searchFilters(string $name = '', array $priceRange = [], array $brands = [], array $colors = [])
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE 1=1";
        $params = [];

        //Recherche par nom
        if (!empty($name)) {
            $sql .= " AND LOWER(name) LIKE LOWER(?)";
            $params[] = '%' . trim($name) . '%';
        }
        
        //Recherche par prix
        if (!empty($priceRange)) {
            $priceConditions = [];
            
            foreach ($priceRange as $range) {
                switch ($range) {
                    case '0-25':
                        $priceConditions[] = "(price >= 0 AND price <= 25)";
                        break;
                    case '25-50':
                        $priceConditions[] = "(price > 25 AND price <= 50)";
                        break;
                    case '50-100':
                        $priceConditions[] = "(price > 50 AND price <= 100)";
                        break;
                    case '100+':
                        $priceConditions[] = "(price > 100)";
                        break;
                }
            }
            
            if (!empty($priceConditions)) {
                $sql .= " AND (" . implode(' OR ', $priceConditions) . ")";
            }
        }
        
        if (!empty($brands)) {
            $placeholders = implode(',', array_fill(0, count($brands), '?'));
            $sql .= " AND brand IN ($placeholders)";
            $params = array_merge($params, $brands);
        }

        if (!empty($colors)) {
            $placeholders = implode(',', array_fill(0, count($colors), '?'));
            $sql .= " AND color IN ($placeholders)";
            $params = array_merge($params, $colors);
        }

        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $id = uniqid();
        $createdAt = date('Y-m-d H:i:s');

        $name = $data['name'] ?? '';
        $brand = $data['brand'] ?? null;
        $color = $data['color'] ?? null;
        $price = $data['price'] ?? 0;
        $stock = $data['stock'] ?? 0;

        $stmt = $this->pdo->prepare(
            "INSERT INTO `{$this->table}` (id, name, brand, color, price, stock, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$id, $name, $brand, $color, $price, $stock, $createdAt]);

        return [
            'id' => $id,
            'name' => $name,
            'brand' => $brand,
            'color' => $color,
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
            return [];
        }

        $updatedAt = date('Y-m-d H:i:s');

        $name = $data['name'] ?? $existing['name'];
        $brand = $data['brand'] ?? $existing['brand'];
        $color = $data['color'] ?? $existing['color'];
        $price = $data['price'] ?? $existing['price'];
        $stock = $data['stock'] ?? $existing['stock'];

        $stmt = $this->pdo->prepare(
            "UPDATE `{$this->table}` 
             SET name = ?, brand = ?, color = ?, price = ?, stock = ?, updated_at = ? 
             WHERE id = ?"
        );
        $stmt->execute([$name, $brand, $color, $price, $stock, $updatedAt, $id]);

        return [
            'id' => $id,
            'name' => $name,
            'brand' => $brand,
            'color' => $color,
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