<?php
$title = "Agenda de Socios";
ob_start();
?>
<h2>Agenda de Socios</h2>

<?php if(!empty($error)): ?>
  <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<?php if(isset($editMember) && $editMember): ?>
<h3>Editar Socio</h3>
<form method="post" action="index.php?page=agenda">
  <input type="hidden" name="action" value="update">
  <input type="hidden" name="id" value="<?= $editMember['id'] ?>">
  
  <label>Nombre: <input type="text" name="name" value="<?= htmlspecialchars($editMember['name']) ?>" required></label>
  
  <label>Email: <input type="email" name="email" value="<?= htmlspecialchars($editMember['email'] ?? '') ?>"></label>
  
  <label>Teléfono: <input type="text" name="phone" value="<?= htmlspecialchars($editMember['phone'] ?? '') ?>"></label>
  
  <label>Dirección: <input type="text" name="address" value="<?= htmlspecialchars($editMember['address'] ?? '') ?>"></label>
  
  <label>Ciudad: <input type="text" name="city" value="<?= htmlspecialchars($editMember['city'] ?? '') ?>"></label>
  
  <label>Código Postal: <input type="text" name="postal_code" value="<?= htmlspecialchars($editMember['postal_code'] ?? '') ?>"></label>
  
  <label>Notas: <textarea name="notes" rows="3"><?= htmlspecialchars($editMember['notes'] ?? '') ?></textarea></label>
  
  <button type="submit">Actualizar</button>
  <a href="index.php?page=agenda" class="btn">Cancelar</a>
</form>
<?php else: ?>
<h3>Crear Socio</h3>
<form method="post" action="index.php?page=agenda">
  <input type="hidden" name="action" value="create">
  
  <label>Nombre: <input type="text" name="name" required></label>
  
  <label>Email: <input type="email" name="email"></label>
  
  <label>Teléfono: <input type="text" name="phone"></label>
  
  <label>Dirección: <input type="text" name="address"></label>
  
  <label>Ciudad: <input type="text" name="city"></label>
  
  <label>Código Postal: <input type="text" name="postal_code"></label>
  
  <label>Notas: <textarea name="notes" rows="3"></textarea></label>
  
  <button type="submit">Crear Socio</button>
</form>
<?php endif; ?>

<h3>Lista de Socios</h3>
<?php if(empty($members)): ?>
  <p>No hay socios registrados.</p>
<?php else: ?>
<table>
  <tr>
    <th>ID</th>
    <th>Nombre</th>
    <th>Email</th>
    <th>Teléfono</th>
    <th>Ciudad</th>
    <th>Código Postal</th>
    <th>Notas</th>
    <th>Acciones</th>
  </tr>
  <?php foreach($members as $m): ?>
    <tr>
      <td><?= $m['id'] ?></td>
      <td><?= htmlspecialchars($m['name']) ?></td>
      <td><?= htmlspecialchars($m['email'] ?? '') ?></td>
      <td><?= htmlspecialchars($m['phone'] ?? '') ?></td>
      <td><?= htmlspecialchars($m['city'] ?? '') ?></td>
      <td><?= htmlspecialchars($m['postal_code'] ?? '') ?></td>
      <td><?= htmlspecialchars(mb_substr($m['notes'] ?? '', 0, 50)) ?><?= strlen($m['notes'] ?? '') > 50 ? '...' : '' ?></td>
      <td>
        <a href="index.php?page=agenda&edit=<?= $m['id'] ?>" class="btn btn-small">Editar</a>
        <form method="post" style="display:inline">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= $m['id'] ?>">
          <button type="submit" class="btn-danger btn-small" onclick="return confirm('¿Borrar socio?')">Borrar</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
</table>
<?php endif; ?>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
