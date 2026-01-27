<?php
ob_start();
$prefix = $baseUrl.'/product';
?>

<h1>Erreur Product</h1>
<div class="error"><?= htmlspecialchars($message) ?></div>
<a href="<?= $prefix."/products"?>" class="btn">Retour à la liste</a>

<?php $content = ob_get_clean(); include __DIR__.'/../layout.php'; ?>