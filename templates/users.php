<?php
$title = "Usuarios";
ob_start();
?>
<h2>Usuarios</h2>

<?php if(isset($editUser) && $editUser): ?>
<h3>Editar usuario</h3>
<form method="post" action="index.php?page=users">
  <input type="hidden" name="action" value="update">
  <input type="hidden" name="id" value="<?= $editUser['id'] ?>">
  <label>Email: <input type="email" name="email" value="<?= htmlspecialchars($editUser['email']) ?>" required></label><br/>
  <label>Nombre: <input type="text" name="name" value="<?= htmlspecialchars($editUser['name']) ?>" required></label><br/>
  <label>Contraseña (dejar vacío para no cambiar): <input type="password" name="password"></label><br/>
  <label>Rol:
    <select name="role">
      <option value="member" <?= $editUser['role'] === 'member' ? 'selected' : '' ?>>Socio</option>
      <option value="admin" <?= $editUser['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
    </select>
  </label><br/><br/>
  <button type="submit">Actualizar</button>
  <a href="index.php?page=users">Cancelar</a>
</form>
<?php else: ?>
<h3>Crear usuario</h3>
<form method="post" action="index.php?page=users">
  <input type="hidden" name="action" value="create">
  <label>Email: <input type="email" name="email" required></label><br/>
  <label>Nombre: <input type="text" name="name" required></label><br/>
  <label>Contraseña (opcional): <input type="password" name="password"></label><br/>
  <label>Rol:
    <select name="role">
      <option value="member">Socio</option>
      <option value="admin">Admin</option>
    </select>
  </label><br/><br/>
  <button type="submit">Crear</button>
</form>
<?php endif; ?>

<h3>Lista</h3>
<table>
  <tr><th>ID</th><th>Email</th><th>Nombre</th><th>Rol</th><th>Acciones</th></tr>
  <?php foreach($users as $u): ?>
    <tr>
      <td><?= $u['id'] ?></td>
      <td><?= htmlspecialchars($u['email']) ?></td>
      <td><?= htmlspecialchars($u['name']) ?></td>
      <td><?= $u['role'] ?></td>
      <td>
        <a href="index.php?page=users&edit=<?= $u['id'] ?>">Editar</a>
        <form method="post" style="display:inline">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= $u['id'] ?>">
          <button type="submit" onclick="return confirm('Borrar usuario?')">Borrar</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
</table>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
