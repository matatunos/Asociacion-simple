<?php
$title = "Agenda de Socios";
ob_start();
$year = date('Y');
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h2>Agenda de Socios</h2>
  <a href="#newMember" class="btn btn-success" data-bs-toggle="collapse">Nuevo socio</a>
</div>

<div id="newMember" class="collapse mb-4">
  <div class="card card-body">
    <form method="post" action="index.php?page=agenda">
      <input type="hidden" name="action" value="create">
      <div class="row g-2">
        <div class="col-md-6">
          <input name="name" class="form-control" placeholder="Nombre completo" required>
        </div>
        <div class="col-md-6">
          <input name="email" class="form-control" placeholder="Email (opcional)">
        </div>
      </div>
      <div class="row g-2 mt-2">
        <div class="col-md-4"><input name="phone" class="form-control" placeholder="Teléfono"></div>
        <div class="col-md-8"><input name="address" class="form-control" placeholder="Dirección"></div>
      </div>
      <div class="mt-3">
        <button class="btn btn-primary">Crear socio</button>
      </div>
    </form>
  </div>
</div>

<?php if (!empty($members)): ?>
  <ul class="list-group">
    <?php foreach($members as $m): ?>
      <li class="list-group-item d-flex justify-content-between align-items-center">
        <div>
          <strong><?=htmlspecialchars($m['name'])?></strong>
          <div class="small text-muted"><?=htmlspecialchars($m['email'] ?? '')?></div>
        </div>

        <div class="d-flex align-items-center gap-2">
          <form method="post" action="index.php?page=agenda" class="d-inline">
            <input type="hidden" name="action" value="toggle_payment">
            <input type="hidden" name="id" value="<?= $m['id'] ?>">
            <input type="hidden" name="year" value="<?= $year ?>">
            <div class="form-check form-switch me-2">
              <input class="form-check-input" type="checkbox" role="switch" disabled <?= !empty($m['paid']) ? 'checked' : '' ?>>
              <label class="form-check-label small"><?= !empty($m['paid']) ? 'Al día' : 'Pendiente' ?></label>
            </div>
            <button class="btn btn-outline-primary btn-sm" title="Actualizar estado de pago">Actualizar pago</button>
          </form>

          <a class="btn btn-outline-secondary btn-sm" href="index.php?page=agenda&edit=<?= $m['id'] ?>">Editar</a>

          <form method="post" action="index.php?page=agenda" class="d-inline" onsubmit="return confirm('¿Eliminar socio?')">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= $m['id'] ?>">
            <button class="btn btn-outline-danger btn-sm">Eliminar</button>
          </form>
        </div>
      </li>
    <?php endforeach; ?>
  </ul>
<?php else: ?>
  <div class="alert alert-secondary">No hay socios registrados.</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
