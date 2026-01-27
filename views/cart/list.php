<?php
ob_start();
$prefix = $baseUrl . '/cart';
?>

    <h1>Liste des Paniers</h1>

    <div class="actions">
        <form method="POST" action="<?= $prefix ?>/create" style="display:inline;">
            <button type="submit" class="btn btn-primary">Nouveau Panier</button>
        </form>
    </div>

<?php if (empty($carts)): ?>
    <p>Aucun panier trouvé.</p>
<?php else: ?>
    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Produits</th>
            <th>Date de création</th>
            <th>Date de mise à jour</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($carts as $cart): ?>
            <tr>
                <td><?= htmlspecialchars($cart['id']) ?></td>
                <td><?= count($cart['items'] ?? []) ?></td>
                <td><?= htmlspecialchars($cart['created_at'] ?? '') ?></td>
                <td><?= htmlspecialchars($cart['updated_at'] ?? '-') ?></td>
                <td>
                    <a href="<?= $prefix ?>/<?= $cart['id'] ?>" class="btn btn-sm">Voir</a>
                    <a href="<?= $prefix ?>/<?= $cart['id'] ?>/edit" class="btn btn-warning">Modifier</a>

                    <form method="POST"
                          action="<?= $prefix ?>/<?= $cart['id'] ?>/delete"
                          style="display:inline;">
                        <button type="submit"
                                class="btn btn-sm btn-danger"
                                onclick="return confirm('Supprimer ce panier ?')">
                            Supprimer
                        </button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php $content = ob_get_clean(); include __DIR__.'/../layout.php'; ?>
