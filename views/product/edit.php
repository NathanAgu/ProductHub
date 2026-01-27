<?php
ob_start();
$prefix = $baseUrl.'/product';
?>

    <h1>Modifier un Produit</h1>

<?php if (isset($error)): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

    <form method="POST" action="<?= $prefix ?>/<?= $product['id'] ?>/update">
        <div class="form-group">
            <label for="name">Nom du produit *</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($product['name'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label for="country">Pays d'origine</label>
            <input type="text" id="country" name="country" value="<?= htmlspecialchars($product['country'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="price">Prix (€)</label>
            <input type="number" id="price" name="price" step="0.01" min="0" value="<?= htmlspecialchars($product['price'] ?? 0) ?>">
        </div>

        <div class="form-group">
            <label for="stock">Stock</label>
            <input type="number" id="stock" name="stock" min="0" value="<?= htmlspecialchars($product['stock'] ?? 0) ?>">
        </div>

        <div class="form-group">
            <label for="expiration_date">Date d'expiration</label>
            <input type="date" id="expiration_date" name="expiration_date" value="<?= htmlspecialchars($product['expiration_date'] ?? '') ?>">
        </div>

        <div class="actions">
            <button type="submit" class="btn btn-success">Mettre à jour</button>
            <a href="<?= $prefix ?>" class="btn">Annuler</a>
        </div>
    </form>

<?php $content = ob_get_clean(); include __DIR__.'/../layout.php'; ?>