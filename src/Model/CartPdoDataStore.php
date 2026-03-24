<?php
namespace Models;

use Models\DataStoreInterface;
use \Util\LoggingService;

class CartPdoDataStore implements DataStoreInterface
{
    private $pdo;
    private $table = 'carts';
    private $logger;

    public function __construct()
    {
        $this->logger = LoggingService::getCartLogger();

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
            size VARCHAR(4)
                CHARACTER SET utf8mb4
                COLLATE utf8mb4_unicode_ci,
            UNIQUE KEY unique_cart_product_size (cart_id, product_id, size),
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
        // Récupérer le panier
        $stmt = $this->pdo->prepare("SELECT * FROM carts WHERE id = ?");
        $stmt->execute([$id]);
        $cart = $stmt->fetch();

        if (!$cart) {
            return null;
        }

        // Récupérer les items avec les infos produits
        $stmt = $this->pdo->prepare("
        SELECT 
            ci.product_id,
            p.name,
            p.price,
            ci.quantity,
            ci.size,
            (p.price * ci.quantity) AS line_total
        FROM cart_items ci
        JOIN products p ON p.id = ci.product_id
        WHERE ci.cart_id = ?
    ");
        $stmt->execute([$id]);

        $items = $stmt->fetchAll();

        // Calcul du total du panier
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

        //Log création panier
        $this->logger->info('Panier crée', [
            'id' => $id,
            'items_count' => 0,
            'created_at' => $createdAt
        ]);

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
                $this->addOrUpdateItem($id, $item['product_id'], $item['quantity'], $item['size']);
            }
        }

        $updatedAt = date('Y-m-d H:i:s');
        $stmt = $this->pdo->prepare(
            "UPDATE carts SET updated_at = ? WHERE id = ?"
        );
        $stmt->execute([$updatedAt, $id]);

        //Log modification panier
        $this->logger->info('panier mis à jour', [
            'id' => $id,
            'items_before' => $itemsBeforeCount,
            'items_after' => $itemsAfterCount,
            'total_before' => $totalBefore,
            'total_after' => $totalAfter,
            'updated_at' => $updatedAt
        ]);

        return $this->getById($id);
    }

    public function delete(string $id): bool
    {
        $cart = $this->getById($id);

        $stmt = $this->pdo->prepare("DELETE FROM carts WHERE id = ?");
        $stmt->execute([$id]);

        $deleted = $stmt->rowcount() > 0;

        //Log suppression panier   
        if ($deleted) {
            $this->logger->info('panir supprimé', [
                'cart_id' => $id,
                'items_count' => $itemsCount,
                'total_value' => $totalValue,
                'deleted_at' => date('Y-m-d H:i:s')
            ]);
        }

        return $deleted;
    }

    /* ================= ITEMS ================= */

    public function addOrUpdateItem(string $cartId, string $productId, int $qty, string $size): void
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO cart_items (cart_id, product_id, quantity, size)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE quantity = ?"
        );
        $stmt->execute([$cartId, $productId, $qty, $size, $qty]);
    }

    public function addItem(string $cartId, string $productId, int $quantity = 1, string $size = ''): void
    {
        // Vérifier si le produit avec la MÊME TAILLE est déjà dans le panier
        $stmt = $this->pdo->prepare(
            "SELECT quantity FROM cart_items WHERE cart_id = ? AND product_id = ? AND size = ?"
        );
        $stmt->execute([$cartId, $productId, $size]);
        $existing = $stmt->fetch();

        if ($existing) {
            // Le produit avec cette taille existe → on incrémente la quantité
            $stmt = $this->pdo->prepare(
                "UPDATE cart_items
                SET quantity = quantity + ?
                WHERE cart_id = ? AND product_id = ? AND size = ?"
            );
            $stmt->execute([$quantity, $cartId, $productId, $size]);
        } else {
            // Le produit avec cette taille n'existe pas → on l'ajoute
            $stmt = $this->pdo->prepare(
                "INSERT INTO cart_items (cart_id, product_id, quantity, size)
                VALUES (?, ?, ?, ?)"
            );
            $stmt->execute([$cartId, $productId, $quantity, $size]);
        }
    }

    /* ================= ITEMS ================= */

    public function removeItem(string $cartId, string $productId, string $size = ''): void
    {
        if (!empty($size)) {
            // Supprimer un item spécifique avec sa taille
            $stmt = $this->pdo->prepare(
                "DELETE FROM cart_items WHERE cart_id = ? AND product_id = ? AND size = ?"
            );
            $stmt->execute([$cartId, $productId, $size]);
        } else {
            // Supprimer tous les items de ce produit (toutes tailles)
            $stmt = $this->pdo->prepare(
                "DELETE FROM cart_items WHERE cart_id = ? AND product_id = ?"
            );
            $stmt->execute([$cartId, $productId]);
        }
        
        // Log de la suppression
        $this->logger->info('Produit supprimé du panier', [
            'cart_id' => $cartId,
            'product_id' => $productId,
            'size' => $size ?: 'toutes tailles',
            'deleted_at' => date('Y-m-d H:i:s')
        ]);
    }
}