<?php
ob_start();
$prefix = $baseUrl . '/cart';
$params = require __DIR__ . '/../../src/config/params.php';
?>

<script>
    form.addEventListener('submit', function (e) {
        if (quantité > stock) {
            e.preventDefault();
            alert('Quantité dépasse le stock!');
        }
    });
</script>

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
                    <th>Stock dispo</th>
                    <th>taille</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td>
                            <input type="checkbox" name="products[<?= $product['id'] ?>][checked]" value="1"
                                <?= $product['stock'] <= 0 ? 'disabled' : '' ?>>
                        </td>
                        <td><?= htmlspecialchars($product['name']) ?></td>
                        <td><?= number_format($product['price'] ?? 0, 2) ?> €</td>
                        <td>
                            <input type="number" name="products[<?= $product['id'] ?>][quantity]" value="1" min="1">
                        </td>
                        <td>
                            <?php
                            $stock = $product['stock'];
                            if ($stock > 20) {
                                $color = '#28a745';
                                $bgColor = '#d4edda';
                                $text = 'Bon stock';
                            } elseif ($stock > 0 && $stock <= 20) {
                                $color = '#ff9800';
                                $bgColor = '#fff3cd';
                                $text = 'Stock faible';
                            } else {
                                $color = '#dc3545';
                                $bgColor = '#f8d7da';
                                $text = 'Rupture';
                            }
                            ?>
                            <span
                                style="background-color: <?= $bgColor ?>; color: <?= $color ?>; padding: 5px 10px; border-radius: 3px; font-weight: bold;">
                                <?= $stock ?> - <?= $text ?>
                            </span>
                        </td>
                        <td>
                            <select name="size" id="size">
                                <option value="">-- Sélectionner une taille --</option>
                                <?php
                                $sizes = $params['sizes'];
                                foreach ($sizes as $size):
                                    $selected = (isset($product['size']) && $product['size'] === $size) ? 'selected' : '';
                                    ?>
                                    <option value="<?= htmlspecialchars($size) ?>" <?= $selected ?>><?= htmlspecialchars($size) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
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