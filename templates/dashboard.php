<?php
$title = "Panel";
ob_start();
?>
<h2>Bienvenido, <?=htmlspecialchars($user['name'])?></h2>
<p>Panel de control de la asociación</p>

<h3>Estadísticas</h3>
<table>
  <tr>
    <th>Métrica</th>
    <th>Valor</th>
  </tr>
  <tr>
    <td>Usuarios activos</td>
    <td><?= $stats['total_users'] ?></td>
  </tr>
  <tr>
    <td>Pagos registrados</td>
    <td><?= $stats['total_payments'] ?></td>
  </tr>
  <tr>
    <td>Vales emitidos</td>
    <td><?= $stats['total_vouchers'] ?></td>
  </tr>
  <tr>
    <td>Cuotas pagadas (<?= $stats['current_year'] ?>)</td>
    <td><?= $stats['paid_memberships'] ?></td>
  </tr>
</table>

<h3>Acciones rápidas</h3>
<ul>
  <li><a href="index.php?page=payments">Gestionar pagos y cuotas</a></li>
  <li><a href="index.php?page=vouchers">Crear vales para eventos</a></li>
  <?php if($user['role'] === 'admin'): ?>
    <li><a href="index.php?page=users">Administrar usuarios</a></li>
  <?php endif; ?>
</ul>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
