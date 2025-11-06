<?php
$title = "Panel";
ob_start();
?>
<h2>Bienvenido, <?=htmlspecialchars($user['name'])?></h2>
<p>Últimas acciones: panel resumido. Usa el menú para navegar.</p>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
