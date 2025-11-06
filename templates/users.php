<?php
$title = "Usuarios";
ob_start();
?>
<h2>Usuarios</h2>
<h3>Crear usuario</h3>
<form method="post" action="index.php?page=users">
  <input type="hidden" name="action" value="create">
  <label>Email: <input type="email" name="email" required></label><br/>
  <label>Nombre: <input type="text" name="name" required></label><br/>
  <label>Contraseña (opcional): <input type="text" name="password"></label><br/>
  <label>Rol:
    <select name="role">
      <option value="member">Socio</option>
      <option value="admin">Admin</option>
    </select>
  </label><br/><br/>
  <button type="submit">Crear</button>
</form>

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
