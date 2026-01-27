<?php
ob_start();
$prefix = $baseUrl . '/cart';
?>

<h1>Modifier le Panier</h1>

<?php if (empty($cart['items'])): ?>
    <p>Le panier est vide.</p>
<?php else: ?>

    <form method="POST" action="<?= $prefix ?>/<?= $cart['id'] ?>/update">

        <table class="table table-striped">
            <thead>
            <tr>
                <th>Produit</th>
                <th>Prix (€)</th>
                <th>Quantité</th>
                <th>Sous-total (€)</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>

            <?php $total = 0; ?>

            <?php foreach ($cart['items'] as $item): ?>
                <?php
                $subtotal = $item['price'] * $item['quantity'];
                $total += $subtotal;
                ?>
                <tr>
                    <td><?= htmlspecialchars($item['name']) ?></td>
                    <td><?= number_format($item['price'], 2) ?></td>

                    <td style="width:120px">
                        <input
                                type="number"
                                name="items[<?= $item['product_id'] ?>]"
                                value="<?= $item['quantity'] ?>"
                                min="0"
                                class="form-control"
                        >
                    </td>

                    <td><?= number_format($subtotal, 2) ?></td>

                    <td>
                        <a href="<?= $prefix ?>/<?= $cart['id'] ?>/remove/<?= $item['product_id'] ?>"
                           class="btn btn-sm btn-danger"
                           onclick="return confirm('Supprimer ce produit du panier ?')">
                            Supprimer
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>

            </tbody>

            <tfoot>
            <tr>
                <th colspan="3" class="text-end">Total</th>
                <th><?= number_format($total, 2) ?> €</th>
                <th></th>
            </tr>
            </tfoot>
        </table>

        <div class="actions">
            <button type="submit" class="btn btn-success">
                Mettre à jour le panier
            </button>

            <a href="<?= $prefix ?>/<?= $cart['id'] ?>/add" class="btn btn-primary">
                Ajouter des produits
            </a>

            <a href="<?= $prefix ?>" class="btn btn-secondary">
                Retour
            </a>
        </div>

    </form>

<?php endif; ?>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>
