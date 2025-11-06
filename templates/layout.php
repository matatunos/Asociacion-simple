<?php
// Variables esperadas: $title, $content, $user, $config
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"/>
  <title><?=htmlspecialchars($title ?? 'Asociación')?></title>
  <style>
    body { font-family: Arial, sans-serif; max-width:1000px; margin:20px auto; }
    nav { margin-bottom: 1rem; }
    nav a { margin-right: 10px; }
    table { width:100%; border-collapse:collapse; }
    th,td{ border:1px solid #ddd; padding:8px; }
    .error { color:red; }
  </style>
</head>
<body>
  <header>
    <h1>Gestor de Asociación</h1>
    <nav>
      <a href="index.php?page=dashboard">Inicio</a>
      <a href="index.php?page=payments">Pagos</a>
      <a href="index.php?page=vouchers">Vales</a>
      <?php if($user && $user['role']==='admin'): ?><a href="index.php?page=users">Usuarios</a><?php endif; ?>
      <a href="index.php?page=logout">Cerrar sesión</a>
    </nav>
    <hr/>
  </header>
  <main>
    <?= $content ?>
  </main>
  <footer><hr/>Gestor básico · <?=date('Y')?></footer>
</body>
</html>
