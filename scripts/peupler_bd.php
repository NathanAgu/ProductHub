<?php
require __DIR__ . '/../vendor/autoload.php';

use Models\ProductPdoDataStore;
use Models\CartPdoDataStore;
use Faker\Factory;

$faker = Factory::create('fr_FR');

// ===============================
// Données réalistes (contrôlées)
// ===============================
$productTypes = [
    'T-shirt',
    'Jeans',
    'Sneakers',
    'Casquette',
    'Veste',
    'Short',
    'Chemise',
    'Pull',
    'Robe',
    'Jupe',
];

$colors = [
    'Rouge',
    'Bleu',
    'Vert',
    'Noir',
    'Blanc',
    'Jaune',
    'Gris',
    'Rose',
    'Violet',
    'Orange',
];

$brands = [
    'Nike',
    'Adidas',
    'Stüssy',
    'Supreme',
];

// ===============================
// Instanciation des stores
// ===============================
$productStore = new ProductPdoDataStore();
$cartStore    = new CartPdoDataStore();

// ===============================
// Génération des produits
// ===============================
$products = [];

for ($i = 0; $i < 20; $i++) {

    $product = $productStore->create([
        'name'        => $faker->randomElement($productTypes),
        'color'       => $faker->randomElement($colors),
        'price'       => $faker->randomFloat(2, 1, 500),
        'stock'       => $faker->numberBetween(0, 100),
        'brand'       => $faker->randomElement($brands),

        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);

    $products[] = $product;
}

// ===============================
// Génération des paniers
// ===============================
$productIds = array_column($products, 'id');

for ($i = 0; $i < 5; $i++) {

    $cart = $cartStore->create();

    // 2 à 5 produits par panier
    $selectedProducts = $faker->randomElements($productIds, rand(2, 5));

    foreach ($selectedProducts as $productId) {
        $cartStore->addItem(
            $cart['id'],
            $productId,
            $faker->numberBetween(1, 4)
        );
    }
}

echo "✔ Produits et paniers générés avec succès !\n";
