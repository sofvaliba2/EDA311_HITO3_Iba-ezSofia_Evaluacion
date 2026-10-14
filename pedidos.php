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
        $stmt = $pdo->prepare("SELECT * FROM pedidos WHERE id_pedido = ?");
        $stmt->execute([$id]);
        $editar = $stmt->fetch() ?: null;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    try {
        if ($accion === 'guardar') {
            $id = filter_input(INPUT_POST, 'id_pedido', FILTER_VALIDATE_INT);
            $idCliente = filter_input(INPUT_POST, 'id_cliente', FILTER_VALIDATE_INT);
            $fecha = trim($_POST['fecha'] ?? '');
            $total = filter_input(INPUT_POST, 'total', FILTER_VALIDATE_FLOAT);

            $fechaValida = DateTime::createFromFormat('Y-m-d', $fecha);

            if (!$idCliente || !$fechaValida || $fechaValida->format('Y-m-d') !== $fecha || $total === false || $total < 0) {
                throw new InvalidArgumentException('Verifica cliente, fecha y total.');
            }

            $check = $pdo->prepare("SELECT id_cliente FROM clientes WHERE id_cliente = ?");
            $check->execute([$idCliente]);
            if (!$check->fetch()) {
                throw new InvalidArgumentException('El cliente seleccionado no existe.');
            }

            if ($id) {
                $stmt = $pdo->prepare("UPDATE pedidos SET id_cliente = ?, fecha = ?, total = ? WHERE id_pedido = ?");
                $stmt->execute([$idCliente, $fecha, $total, $id]);
                flash('success', 'Pedido modificado correctamente.');
            } else {
                $stmt = $pdo->prepare("INSERT INTO pedidos (id_cliente, fecha, total) VALUES (?, ?, ?)");
                $stmt->execute([$idCliente, $fecha, $total]);
                flash('success', 'Pedido registrado correctamente.');
            }

            redireccionar('pedidos.php');
        }

        if ($accion === 'eliminar') {
            $id = filter_input(INPUT_POST, 'id_pedido', FILTER_VALIDATE_INT);
            if (!$id) throw new InvalidArgumentException('ID inválido.');

            $stmt = $pdo->prepare("DELETE FROM pedidos WHERE id_pedido = ?");
            $stmt->execute([$id]);
            flash('success', 'Pedido eliminado correctamente.');
            redireccionar('pedidos.php');
        }
    } catch (Throwable $e) {
        flash('error', $e instanceof InvalidArgumentException ? $e->getMessage() : 'No se pudo realizar la operación.');
        redireccionar('pedidos.php');
    }
}

$clientes = $pdo->query("SELECT id_cliente, nombre FROM clientes ORDER BY nombre")->fetchAll();

$lista = cargarLista($pdo, 'pedidos');
$lista->bubbleSort('fecha', true);
$pedidosOrdenados = $lista->recorrer();
$flash = obtenerFlash();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pedidos - CRUD EDA311</title>
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
<h1><?= $editar ? 'Modificar pedido' : 'Registrar pedido' ?></h1>
<form method="post" class="formulario">
<input type="hidden" name="accion" value="guardar">
<?php if ($editar): ?><input type="hidden" name="id_pedido" value="<?= e($editar['id_pedido']) ?>"><?php endif; ?>

<label>Cliente
<select name="id_cliente" required>
<option value="">Selecciona un cliente</option>
<?php foreach ($clientes as $cliente): ?>
<option value="<?= e($cliente['id_cliente']) ?>" <?= isset($editar['id_cliente']) && (int)$editar['id_cliente'] === (int)$cliente['id_cliente'] ? 'selected' : '' ?>>
<?= e($cliente['nombre']) ?> (ID <?= e($cliente['id_cliente']) ?>)
</option>
<?php endforeach; ?>
</select>
</label>

<label>Fecha
<input type="date" name="fecha" required value="<?= e($editar['fecha'] ?? date('Y-m-d')) ?>">
</label>

<label>Total
<input type="number" name="total" min="0" step="0.01" required value="<?= e($editar['total'] ?? '') ?>">
</label>

<div class="acciones">
<button class="btn primario" type="submit"><?= $editar ? 'Guardar cambios' : 'Registrar' ?></button>
<?php if ($editar): ?><a class="btn secundario" href="pedidos.php">Cancelar</a><?php endif; ?>
</div>
</form>
</section>

<section class="panel">
<div class="titulo-tabla">
<div><h2>Pedidos ordenados</h2><p>Bubble Sort por fecha, aplicado directamente sobre la lista doblemente enlazada.</p></div>
<span class="badge"><?= $lista->cantidad() ?> registros</span>
</div>

<div class="tabla-scroll">
<table>
<thead><tr><th>ID</th><th>Cliente</th><th>Fecha</th><th>Total</th><th>Acciones</th></tr></thead>
<tbody>
<?php foreach ($pedidosOrdenados as $pedido): ?>
<?php
$stmtCliente = $pdo->prepare("SELECT nombre FROM clientes WHERE id_cliente = ?");
$stmtCliente->execute([$pedido['id_cliente']]);
$nombreCliente = $stmtCliente->fetchColumn() ?: 'Cliente no encontrado';
?>
<tr>
<td><?= e($pedido['id_pedido']) ?></td>
<td><?= e($nombreCliente) ?></td>
<td><?= e($pedido['fecha']) ?></td>
<td>Bs <?= number_format((float)$pedido['total'], 2) ?></td>
<td class="acciones-tabla">
<a class="btn pequeno" href="?editar=<?= e($pedido['id_pedido']) ?>">Editar</a>
<form method="post" onsubmit="return confirm('¿Eliminar este pedido?');">
<input type="hidden" name="accion" value="eliminar">
<input type="hidden" name="id_pedido" value="<?= e($pedido['id_pedido']) ?>">
<button class="btn pequeno peligro" type="submit">Eliminar</button>
</form>
</td>
</tr>
<?php endforeach; ?>
<?php if (!$pedidosOrdenados): ?><tr><td colspan="5" class="vacio">No hay pedidos registrados.</td></tr><?php endif; ?>
</tbody>
</table>
</div>
</section>
</main>
</body>
</html>
