<?php
ob_start();
$prefix = $baseUrl.'/product';
$searchQuery = $_GET['search'] ?? '';
$priceRanges = $_GET['price'] ?? [];
?>

    <h1>Liste des Produits</h1>

    <div class="actions">
        <a href="<?= $prefix."/create?baseUrl=$baseUrl"?>" class="btn btn-primary">Nouveau Produit</a>
        
        <form method="GET" action="<?= $prefix ?>" style="display: inline-block;">
            <input type="hidden" name="baseUrl" value="<?= htmlspecialchars($baseUrl) ?>">
            <input type="text" name="search" placeholder="Recherche de produits..." 
                   value="<?= htmlspecialchars($searchQuery) ?>">

            <div class="dropdown">
                <button type="button" class="btn">Prix</button>
                <div class="dropdown-content">
                    <label class="checkbox-option">
                        <input type="checkbox" name="price[]" value="0-25" 
                               <?= in_array('0-25', $priceRanges) ? 'checked' : '' ?>>
                        <span>0€ - 25€</span>
                    </label>
                    <label class="checkbox-option">
                        <input type="checkbox" name="price[]" value="25-50"
                               <?= in_array('25-50', $priceRanges) ? 'checked' : '' ?>>
                        <span>25€ - 50€</span>
                    </label>
                    <label class="checkbox-option">
                        <input type="checkbox" name="price[]" value="50-100"
                               <?= in_array('50-100', $priceRanges) ? 'checked' : '' ?>>
                        <span>50€ - 100€</span>
                    </label>
                    <label class="checkbox-option">
                        <input type="checkbox" name="price[]" value="100+"
                               <?= in_array('100+', $priceRanges) ? 'checked' : '' ?>>
                        <span>100€ +</span>
                    </label>
                </div>
            </div>
            <div class="dropdown">
                <button type="button" class="btn">Marque</button>
            </div>
            <div class="dropdown">
                <button type="button" class="btn">Couleur</button>
            </div>
            <div class="dropdown">
                <button type="button" class="btn">Catégorie</button>
            </div>
            <button type="submit" class="btn btn-primary">Recherche</button>
        </form>
    </div>

<?php if (empty($products)): ?>
    <p>Aucun produit trouvé.</p>
<?php else: ?>
    <table>
        <thead>
        <tr>
            <th>Nom</th>
            <th>Marque</th>
            <th>Couleur</th>
            <th>Prix</th>
            <th>Stock</th>
            <th>Date d'ajout</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($products as $product): ?>
            <tr>
                <td title="<?= htmlspecialchars($product['name']) ?>">
                    <?= htmlspecialchars(mb_strimwidth($product['name'], 0, 30, '...')) ?>
                </td>
                <td><?= htmlspecialchars($product['brand'] ?? '') ?></td>
                <td><?= htmlspecialchars($product['color'] ?? '') ?></td>
                <td><?= number_format($product['price'] ?? 0, 2) ?> €</td>
                <td><?= htmlspecialchars($product['stock'] ?? 0) ?></td>
                <td><?= htmlspecialchars($product['created_at'] ?? '') ?></td>
                <td>
                    <a href="<?= $prefix ?>/<?= $product['id'] ?>/edit" class="btn btn-sm">Modifier</a>
                    <form method="POST" action="<?= $prefix ?>/<?= $product['id'] ?>/delete" style="display: inline;">
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr ?')">Supprimer</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php $content = ob_get_clean(); include __DIR__.'/../layout.php'; ?>