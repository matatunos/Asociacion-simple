<?php
// Variables esperadas: $title, $content, $user, $config
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?=htmlspecialchars($title ?? 'Asociación')?></title>
  <link rel="stylesheet" href="assets/css/pihole.css">
</head>
<body>
  <div class="app">
    <header class="app-header">
      <h1>Gestor de Asociación</h1>
    </header>
    <nav class="app-nav">
      <a href="index.php?page=dashboard">Inicio</a>
      <a href="index.php?page=payments">Pagos</a>
      <a href="index.php?page=vouchers">Vales</a>
      <?php if($user && $user['role']==='admin'): ?>
        <a href="index.php?page=users">Usuarios</a>
        <a href="index.php?page=agenda">Agenda</a>
      <?php endif; ?>
      <a href="index.php?page=logout">Cerrar sesión</a>
    </nav>
      <nav class="app-nav">
        <a href="index.php?page=dashboard">Inicio</a>
        <a href="index.php?page=payments">Pagos</a>
        <a href="index.php?page=vouchers">Vales</a>
        <?php if($user && $user['role']==='admin'): ?>
          <a href="index.php?page=users">Usuarios</a>
          <a href="index.php?page=agenda">Agenda</a>
        <?php endif; ?>
        <a href="index.php?page=logout">Cerrar sesión</a>
      </nav>
    </header>
    <main class="app-main">
      <?= $content ?>
    </main>
    <footer>Gestor básico · <?=date('Y')?></footer>
  </div>
</body>
</html>
