<?php
require __DIR__ . '/../vendor/autoload.php';

use Models\ProductPdoDataStore;
use Models\CartPdoDataStore;
use Models\CategoryDataStore;
use Faker\Factory;

$params = require __DIR__ . '/../src/config/params.php';
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

// ===============================
// Instanciation des stores
// ===============================
$productStore = new ProductPdoDataStore();
$cartStore    = new CartPdoDataStore();
$CategoryStore = new CategoryDataStore();


// ===============================
// Génération des catégories
// ===============================

$categoryNames = ['Hauts', 'Bas', 'Chaussures', 'Accessoires'];
$categories = [];

foreach ($categoryNames as $name) {
    $category = [
        'id' => uniqid(),
        'name' => $name,
    ];
    $CategoryStore->create($category);
    $categories[] = $category;
}


// ===============================
// Génération des produits
// ===============================
$products = [];

for ($i = 0; $i < 20; $i++) {

    $product = $productStore->create([
        'name'        => $faker->randomElement($productTypes),
        'color'       => $faker->randomElement($params["colors"]),
        'price'       => $faker->randomFloat(2, 1, 500),
        'stock'       => $faker->numberBetween(0, 100),
        'brand'       => $faker->randomElement($params["brands"]),
        'category_id' => $faker->randomElement($categories)['id'],

        'created_at' => date('Y-m-d H'),
        'updated_at' => date('Y-m-d H')
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
            $faker->numberBetween(1, 4),
            $faker->randomElement($params["sizes"])
        );
    }
}

echo "✔ Produits et paniers générés avec succès !\n";
