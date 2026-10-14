<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/funciones.php';
require_once __DIR__ . '/estructuras.php';

$db = new Database();
$pdo = $db->getPDO();

$editar = null;
if (isset($_GET['editar'])) {
    $id = filter_input(INPUT_GET, 'editar', FILTER_VALIDATE_INT);
    if ($id) {
        $stmt = $pdo->prepare("SELECT * FROM clientes WHERE id_cliente = ?");
        $stmt->execute([$id]);
        $editar = $stmt->fetch() ?: null;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    try {
        if ($accion === 'guardar') {
            $id = filter_input(INPUT_POST, 'id_cliente', FILTER_VALIDATE_INT);
            $nombre = trim($_POST['nombre'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $edad = filter_input(INPUT_POST, 'edad', FILTER_VALIDATE_INT);

            if ($nombre === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $edad === false || $edad < 18) {
                throw new InvalidArgumentException('Verifica nombre, email y edad. La edad debe ser de 18 años o más.');
            }

            if ($id) {
                $stmt = $pdo->prepare("UPDATE clientes SET nombre = ?, email = ?, edad = ? WHERE id_cliente = ?");
                $stmt->execute([$nombre, $email, $edad, $id]);
                flash('success', 'Cliente modificado correctamente.');
            } else {
                $stmt = $pdo->prepare("INSERT INTO clientes (nombre, email, edad) VALUES (?, ?, ?)");
                $stmt->execute([$nombre, $email, $edad]);
                flash('success', 'Cliente registrado correctamente.');
            }

            redireccionar('clientes.php');
        }

        if ($accion === 'eliminar') {
            $id = filter_input(INPUT_POST, 'id_cliente', FILTER_VALIDATE_INT);
            if (!$id) {
                throw new InvalidArgumentException('ID inválido.');
            }

            $stmt = $pdo->prepare("DELETE FROM clientes WHERE id_cliente = ?");
            $stmt->execute([$id]);
            flash('success', 'Cliente eliminado correctamente.');
            redireccionar('clientes.php');
        }
    } catch (Throwable $e) {
        flash('error', $e instanceof InvalidArgumentException ? $e->getMessage() : 'No se pudo realizar la operación. Verifica que el email no esté repetido y que el cliente no tenga pedidos asociados.');
        redireccionar('clientes.php');
    }
}

$lista = cargarLista($pdo, 'clientes');
$lista->bubbleSort('nombre', true);
$clientesOrdenados = $lista->recorrer();
$flash = obtenerFlash();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Clientes - CRUD EDA311</title>
<link rel="stylesheet" href="css/estilos.css">
</head>
<body>
<header class="barra">
    <div class="contenedor barra-flex">
        <a href="index.php" class="logo">EDA311 · HITO 3</a>
        <nav><a href="clientes.php">Clientes</a><a href="pedidos.php">Pedidos</a><a href="productos.php">Productos</a></nav>
    </div>
</header>

<main class="contenedor">
<?php if ($flash): ?><div class="alert <?= e($flash['tipo']) ?>"><?= e($flash['mensaje']) ?></div><?php endif; ?>

<section class="panel">
<h1><?= $editar ? 'Modificar cliente' : 'Registrar cliente' ?></h1>
<form method="post" class="formulario">
    <input type="hidden" name="accion" value="guardar">
    <?php if ($editar): ?><input type="hidden" name="id_cliente" value="<?= e($editar['id_cliente']) ?>"><?php endif; ?>

    <label>Nombre
        <input type="text" name="nombre" maxlength="100" required value="<?= e($editar['nombre'] ?? '') ?>">
    </label>
    <label>Email
        <input type="email" name="email" maxlength="150" required value="<?= e($editar['email'] ?? '') ?>">
    </label>
    <label>Edad
        <input type="number" name="edad" min="18" required value="<?= e($editar['edad'] ?? '') ?>">
    </label>

    <div class="acciones">
        <button class="btn primario" type="submit"><?= $editar ? 'Guardar cambios' : 'Registrar' ?></button>
        <?php if ($editar): ?><a class="btn secundario" href="clientes.php">Cancelar</a><?php endif; ?>
    </div>
</form>
</section>

<section class="panel">
<div class="titulo-tabla">
    <div><h2>Clientes ordenados</h2><p>Bubble Sort por nombre, aplicado directamente sobre la lista doblemente enlazada.</p></div>
    <span class="badge"><?= $lista->cantidad() ?> registros</span>
</div>

<div class="tabla-scroll">
<table>
<thead><tr><th>ID</th><th>Nombre</th><th>Email</th><th>Edad</th><th>Acciones</th></tr></thead>
<tbody>
<?php foreach ($clientesOrdenados as $cliente): ?>
<tr>
<td><?= e($cliente['id_cliente']) ?></td>
<td><?= e($cliente['nombre']) ?></td>
<td><?= e($cliente['email']) ?></td>
<td><?= e($cliente['edad']) ?></td>
<td class="acciones-tabla">
<a class="btn pequeno" href="?editar=<?= e($cliente['id_cliente']) ?>">Editar</a>
<form method="post" onsubmit="return confirm('¿Eliminar este cliente? Si tiene pedidos asociados, MySQL puede impedir la eliminación.');">
<input type="hidden" name="accion" value="eliminar">
<input type="hidden" name="id_cliente" value="<?= e($cliente['id_cliente']) ?>">
<button class="btn pequeno peligro" type="submit">Eliminar</button>
</form>
</td>
</tr>
<?php endforeach; ?>
<?php if (!$clientesOrdenados): ?><tr><td colspan="5" class="vacio">No hay clientes registrados.</td></tr><?php endif; ?>
</tbody>
</table>
</div>
</section>
</main>
</body>
</html>
