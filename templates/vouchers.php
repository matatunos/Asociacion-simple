<?php
$title = "Vales y Documentos";
ob_start();
?>
<h2>Vales</h2>

<h3>Crear vale</h3>
<form method="post" action="index.php?page=vouchers">
  <input type="hidden" name="action" value="create_voucher">
  <label>Código: <input type="text" name="code" required></label><br/>
  <label>Nombre evento: <input type="text" name="event_name" required></label><br/>
  <label>Usuario asociado (id) opcional: <input type="number" name="user_id"></label><br/>
  <label>Válido desde: <input type="date" name="valid_from"></label><br/>
  <label>Válido hasta: <input type="date" name="valid_to"></label><br/>
  <label>Plantilla (usa {{name}}, {{email}}, {{event_name}}, {{code}}):<br/>
    <textarea name="template" rows="6">Vale para {{name}} ({{email}}) — Evento: {{event_name}} — Código: {{code}}</textarea>
  </label><br/>
  <button type="submit">Crear vale</button>
</form>

<h3>Lista de vales</h3>
<table>
<tr><th>ID</th><th>Código</th><th>Usuario</th><th>Evento</th><th>Usado</th><th>Acción</th></tr>
<?php foreach($vouchers as $v): ?>
  <tr>
    <td><?= $v['id'] ?></td>
    <td><?= htmlspecialchars($v['code']) ?></td>
    <td><?= htmlspecialchars($v['name'] . ' <' . $v['email'] . '>') ?></td>
    <td><?= htmlspecialchars($v['event_name']) ?></td>
    <td><?= $v['used'] ? 'Sí' : 'No' ?></td>
    <td>
      <?php
        $tpl = $v['template'];
        $rendered = \Voucher::renderTemplate($tpl, [
            'name'=>$v['name'] ?? '',
            'email'=>$v['email'] ?? '',
            'event_name'=>$v['event_name'],
            'code'=>$v['code']
        ]);
      ?>
      <a href="#" onclick="alert(<?=json_encode($rendered)?>); return false;">Vista</a>
    </td>
  </tr>
<?php endforeach; ?>
</table>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
