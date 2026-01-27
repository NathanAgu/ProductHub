<?php
ob_start();
$prefix = $baseUrl.'/cart';
?>

<h1>Erreur Cart</h1>
<div class="error"><?= htmlspecialchars($message) ?></div>
<a href="<?= $prefix."/carts"?>" class="btn">Retour à la liste</a>

<?php $content = ob_get_clean(); include __DIR__.'/../layout.php'; ?>
