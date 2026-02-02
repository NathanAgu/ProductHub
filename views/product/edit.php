<?php
ob_start();
$prefix = $baseUrl.'/product';
$params = require __DIR__ . '/../../src/config/params.php';
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
            <label for="brand">Marque</label>
            <select name="brand" id="brand">
                <?php
                $brands = $params['brands'];
                foreach ($brands as $brand): 
                    $selected = (isset($product['brand']) && $product['brand'] === $brand) ? 'selected' : '';
                ?>
                    <option value="<?= htmlspecialchars($brand) ?>" <?= $selected ?>><?= htmlspecialchars($brand) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="color">Couleur</label>
            <select name="color" id="color">
                <?php
                $colors = $params['colors'];
                foreach ($colors as $color): 
                    $selected = (isset($product['color']) && $product['color'] === $color) ? 'selected' : '';
                ?>
                    <option value="<?= htmlspecialchars($color) ?>" <?= $selected ?>><?= htmlspecialchars($color) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="price">Prix (€)</label>
            <input type="number" id="price" name="price" step="0.01" min="0" value="<?= htmlspecialchars($product['price'] ?? 0) ?>">
        </div>

        <div class="form-group">
            <label for="stock">Stock</label>
            <input type="number" id="stock" name="stock" min="0" value="<?= htmlspecialchars($product['stock'] ?? 0) ?>">
        </div>

        <div class="actions">
            <button type="submit" class="btn btn-success">Mettre à jour</button>
            <a href="<?= $prefix ?>" class="btn">Annuler</a>
        </div>
    </form>

<?php $content = ob_get_clean(); include __DIR__.'/../layout.php'; ?>