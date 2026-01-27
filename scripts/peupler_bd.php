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
    'Jus de fruits',
    'Ordinateur portable',
    'Téléphone',
    'Fromage',
    'Café moulu',
    'Biscottes',
    'Tablette tactile',
    'Clavier sans fil',
    'Casque audio',
    'Livre'
];

$countries = [
    'France',
    'Espagne',
    'Maroc',
    'Italie',
    'Allemagne',
    'Belgique'
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

    $type = $faker->randomElement($productTypes);
    $name = $type . ' ' . ucfirst($faker->word());

    $product = $productStore->create([
        'name'        => $name,
        'description' => $faker->sentence(12),
        'price'       => $faker->randomFloat(2, 1, 500),
        'stock'       => $faker->numberBetween(0, 100),

        // champs enrichis (si présents)
        'country'          => $faker->randomElement($countries),
        'expiration_date' => $faker->dateTimeBetween('now', '+2 years')->format('Y-m-d'),

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
