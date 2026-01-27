<?php
ob_start();
$prefix = $baseUrl . '/cart';
?>

<h1>Ajouter des produits au panier #<?= htmlspecialchars($cart['id']) ?></h1>

<form method="POST" action="<?= $prefix ?>/<?= $cart['id'] ?>/add">

    <?php if (empty($products)): ?>
        <p>Aucun produit disponible.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Ajouter</th>
                    <th>Produit</th>
                    <th>Prix</th>
                    <th>Quantité</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td>
                            <input type="checkbox" name="products[<?= $product['id'] ?>][checked]" value="1">
                        </td>
                        <td><?= htmlspecialchars($product['name']) ?></td>
                        <td><?= number_format($product['price'] ?? 0, 2) ?> €</td>
                        <td>
                            <input type="number" name="products[<?= $product['id'] ?>][quantity]" value="1" min="1">
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div class="actions">
        <button type="submit" class="btn btn-success">Ajouter au panier</button>
        <a href="<?= $prefix ?>/<?= $cart['id'] ?>" class="btn">Annuler</a>
    </div>

</form>

<?php $content = ob_get_clean();
include __DIR__ . '/../layout.php'; ?>