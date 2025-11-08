<?php
// Variables esperadas: $title, $content, $user, $config
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title><?=htmlspecialchars($title ?? 'Asociación')?></title>
  <!-- Bootstrap 5 CSS (CDN) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container my-4">
    <header class="d-flex flex-column flex-md-row align-items-center pb-3 mb-4 border-bottom">
      <a href="index.php" class="d-flex align-items-center text-dark text-decoration-none">
        <h1 class="h4 mb-0">Gestor de Asociación</h1>
      </a>
      <nav class="ms-md-4 mt-3 mt-md-0">
        <a class="btn btn-sm btn-outline-primary me-2" href="index.php?page=dashboard">Inicio</a>
        <a class="btn btn-sm btn-outline-primary me-2" href="index.php?page=payments">Pagos</a>
        <a class="btn btn-sm btn-outline-primary me-2" href="index.php?page=vouchers">Vales</a>
        <?php if(!empty($user) && $user['role']==='admin'): ?>
          <a class="btn btn-sm btn-outline-primary me-2" href="index.php?page=users">Usuarios</a>
          <a class="btn btn-sm btn-outline-primary me-2" href="index.php?page=agenda">Agenda</a>
        <?php endif; ?>
        <a class="btn btn-sm btn-outline-secondary" href="index.php?page=logout">Cerrar sesión</a>
      </nav>
    </header>

    <main>
      <?= $content ?>
    </main>

    <footer class="pt-4 mt-4 text-muted border-top">Gestor básico · <?=date('Y')?></footer>
  </div>

  <!-- Bootstrap 5 JS (opcional) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
