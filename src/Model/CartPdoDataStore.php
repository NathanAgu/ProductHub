<?php
namespace Models;

use Models\DataStoreInterface;

class CartPdoDataStore implements DataStoreInterface
{
    private $pdo;
    private $table = 'carts';

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
        CREATE TABLE IF NOT EXISTS carts (
            id VARCHAR(255)
                CHARACTER SET utf8mb4
                COLLATE utf8mb4_unicode_ci
                PRIMARY KEY,
            created_at DATETIME,
            updated_at DATETIME
        )
        ENGINE=InnoDB
        DEFAULT CHARSET=utf8mb4
        COLLATE=utf8mb4_unicode_ci
    ");

        $this->pdo->exec("
        CREATE TABLE IF NOT EXISTS cart_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            cart_id VARCHAR(255)
                CHARACTER SET utf8mb4
                COLLATE utf8mb4_unicode_ci,
            product_id VARCHAR(255)
                CHARACTER SET utf8mb4
                COLLATE utf8mb4_unicode_ci,
            quantity INT DEFAULT 1,
            UNIQUE(cart_id, product_id),
            INDEX idx_cart (cart_id),
            INDEX idx_product (product_id)
        )
        ENGINE=InnoDB
        DEFAULT CHARSET=utf8mb4
        COLLATE=utf8mb4_unicode_ci
    ");
    }

    /* ================= CART ================= */

    public function getAll()
    {
        $stmt = $this->pdo->query("SELECT * FROM carts ORDER BY created_at DESC");
        $carts = $stmt->fetchAll();

        $items = [];
        foreach ($carts as $cart) {
            $items[$cart['id']] = $this->getById($cart['id']);
        }
        return $items;
    }

    public function getById($id)
    {
        // 1️⃣ Récupérer le panier
        $stmt = $this->pdo->prepare("SELECT * FROM carts WHERE id = ?");
        $stmt->execute([$id]);
        $cart = $stmt->fetch();

        if (!$cart) {
            return null;
        }

        // 2️⃣ Récupérer les items avec les infos produits
        $stmt = $this->pdo->prepare("
        SELECT 
            ci.product_id,
            p.name,
            p.price,
            ci.quantity,
            (p.price * ci.quantity) AS line_total
        FROM cart_items ci
        JOIN products p ON p.id = ci.product_id
        WHERE ci.cart_id = ?
    ");
        $stmt->execute([$id]);

        $items = $stmt->fetchAll();

        // 3️⃣ Calcul du total du panier
        $total = 0;
        foreach ($items as $item) {
            $total += $item['line_total'];
        }

        $cart['items'] = $items;
        $cart['total'] = $total;

        return $cart;
    }

    public function create($data = [])
    {
        $id = uniqid();
        $createdAt = date('Y-m-d H:i:s');

        $stmt = $this->pdo->prepare(
            "INSERT INTO carts (id, created_at) VALUES (?, ?)"
        );
        $stmt->execute([$id, $createdAt]);

        return [
            'id' => $id,
            'items' => [],
            'created_at' => $createdAt,
            'updated_at' => null
        ];
    }

    public function update($id, $data): array|null
    {
        $cart = $this->getById($id);
        if (!$cart) {
            return null;
        }

        // gestion des items
        if (isset($data['items'])) {
            foreach ($data['items'] as $item) {
                $this->addOrUpdateItem($id, $item['product_id'], $item['quantity']);
            }
        }

        $updatedAt = date('Y-m-d H:i:s');
        $stmt = $this->pdo->prepare(
            "UPDATE carts SET updated_at = ? WHERE id = ?"
        );
        $stmt->execute([$updatedAt, $id]);

        return $this->getById($id);
    }

    public function delete(string $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM carts WHERE id = ?");
        $stmt->execute([$id]);

        return $stmt->rowCount() > 0;
    }

    /* ================= ITEMS ================= */

    public function addOrUpdateItem(string $cartId, string $productId, int $qty): void
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO cart_items (cart_id, product_id, quantity)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE quantity = ?"
        );
        $stmt->execute([$cartId, $productId, $qty, $qty]);
    }

    public function addItem(string $cartId, string $productId, int $quantity = 1): void
    {
        // 1️⃣ Vérifier si le produit est déjà dans le panier
        $stmt = $this->pdo->prepare(
            "SELECT quantity FROM cart_items WHERE cart_id = ? AND product_id = ?"
        );
        $stmt->execute([$cartId, $productId]);
        $existing = $stmt->fetch();

        if ($existing) {
            // 2️⃣ Le produit existe → on incrémente la quantité
            $stmt = $this->pdo->prepare(
                "UPDATE cart_items
             SET quantity = quantity + ?
             WHERE cart_id = ? AND product_id = ?"
            );
            $stmt->execute([$quantity, $cartId, $productId]);
        } else {
            // 3️⃣ Le produit n'existe pas → on l'ajoute
            $stmt = $this->pdo->prepare(
                "INSERT INTO cart_items (cart_id, product_id, quantity)
             VALUES (?, ?, ?)"
            );
            $stmt->execute([$cartId, $productId, $quantity]);
        }
    }

    public function searchFilters(string $name = '', array $priceRange = []){
        return [];
    }
}
