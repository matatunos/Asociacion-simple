<?php
$title = "Pagos y Cuotas";
ob_start();
?>
<h2>Pagos</h2>

<h3>Registrar pago</h3>
<form method="post" action="index.php?page=payments">
  <input type="hidden" name="action" value="record_payment">
  <label>ID Usuario: <input type="number" name="user_id" required></label><br/>
  <label>Importe: <input type="number" step="0.01" name="amount" required></label><br/>
  <label>Método: <input type="text" name="method" value="efectivo"></label><br/>
  <label>Notas: <textarea name="notes"></textarea></label><br/>
  <button type="submit">Registrar pago</button>
</form>

<h3>Pagos recientes</h3>
<table>
<tr><th>ID</th><th>Usuario</th><th>Importe</th><th>Método</th><th>Fecha</th></tr>
<?php foreach($payments as $p): ?>
  <tr>
    <td><?= $p['id'] ?></td>
    <td><?= htmlspecialchars($p['name'] . ' <' . $p['email'] . '>') ?></td>
    <td><?= $p['amount'] ?></td>
    <td><?= htmlspecialchars($p['method']) ?></td>
    <td><?= $p['created_at'] ?></td>
  </tr>
<?php endforeach; ?>
</table>

<h3>Cuotas del año <?=date('Y')?></h3>
<table>
<tr><th>Usuario</th><th>Año</th><th>Pagado</th><th>Importe</th></tr>
<?php foreach($memberships as $m): ?>
  <tr>
    <td><?=htmlspecialchars($m['name'].' <'.$m['email'].'>')?></td>
    <td><?= $m['year'] ?></td>
    <td><?= $m['paid'] ? 'Sí' : 'No' ?></td>
    <td><?= $m['amount'] ?></td>
  </tr>
<?php endforeach; ?>
</table>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
