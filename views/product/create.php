<?php
ob_start();
$prefix = $_GET["baseUrl"] . '/product';
?>

<h1>Créer un Produit</h1>

<?php if (isset($error)): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST" action="<?= $prefix . "/store" ?>">
    <div class="form-group">
        <label for="name">Nom du produit *</label>
        <input type="text" id="name" name="name" required>
    </div>

    <div class="form-group">
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="4"></textarea>
    </div>

    <div class="form-group">
        <label for="price">Prix (€)</label>
        <input type="number" id="price" name="price" step="0.01" min="0" value="0">
    </div>

    <div class="form-group">
        <label for="stock">Stock</label>
        <input type="number" id="stock" name="stock" min="0" value="0">
    </div>

        <div class="actions">
            <button type="submit" class="btn btn-success">Créer</button>
            <a href="<?= $prefix ?>" class="btn">Annuler</a>
        </div>
    </form>

<?php $content = ob_get_clean();
include __DIR__ . '/../layout.php'; ?>