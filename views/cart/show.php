<?php
ob_start();
$prefix = $baseUrl . '/cart';
?>

    <h1>Panier #<?= htmlspecialchars($cart['id']) ?></h1>

    <div class="actions" style="margin-bottom: 1em;">
        <a href="<?= $prefix ?>/<?= $cart['id'] ?>/edit" class="btn btn-turquoise">Éditer</a>
        <a href="<?= $prefix ?>/<?= $cart['id'] ?>/add" class="btn btn-success">Ajouter un produit</a>
        <a href="<?= $prefix ?>" class="btn btn-warning">Retour à la liste des paniers</a>
    </div>

<?php if (empty($cart['items'])): ?>
    <p>Ce panier est vide.</p>
<?php else: ?>
    <table>
        <thead>
        <tr>
            <th>Produit</th>
            <th>Prix unitaire</th>
            <th>Quantité</th>
            <th>Total</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $grandTotal = 0;

        foreach ($cart['items'] as $item): ?>
        <tr>
            <td><?= htmlspecialchars($item['name']) ?></td>
            <td><?= number_format($item['price'], 2) ?> €</td>
            <td><?= $item['quantity'] ?></td>
            <td><?= number_format($item['line_total'], 2) ?> €</td>
        </tr>
        <?php endforeach; ?>

        </tbody>
        <tfoot>
        <tr>
            <th colspan="3">Total Panier</th>
            <th><?= number_format($cart['total'], 2) ?> €</th>
        </tr>
        </tfoot>
    </table>
<?php endif; ?>

<?php $content = ob_get_clean(); include __DIR__.'/../layout.php'; ?>
