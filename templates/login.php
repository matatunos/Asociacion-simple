<?php
$title = "Acceso";
ob_start();
?>
<h2>Acceder</h2>
<?php if(!empty($error)): ?><p class="error"><?=htmlspecialchars($error)?></p><?php endif; ?>
<form method="post" action="index.php?page=login">
  <label>Email:<br/><input type="email" name="email" required></label><br/><br/>
  <label>Contraseña:<br/><input type="password" name="password" required></label><br/><br/>
  <button type="submit">Entrar</button>
</form>
<?php
$content = ob_get_clean();
$user = null;
$config = $config ?? [];
require __DIR__ . '/layout.php';
