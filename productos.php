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
        $stmt = $pdo->prepare("SELECT * FROM productos WHERE id_producto = ?");
        $stmt->execute([$id]);
        $editar = $stmt->fetch() ?: null;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    try {
        if ($accion === 'guardar') {
            $id = filter_input(INPUT_POST, 'id_producto', FILTER_VALIDATE_INT);
            $nombre = trim($_POST['nombre'] ?? '');
            $categoria = trim($_POST['categoria'] ?? '');
            $precio = filter_input(INPUT_POST, 'precio', FILTER_VALIDATE_FLOAT);
            $stock = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);

            if ($nombre === '' || $categoria === '' || $precio === false || $precio <= 0 || $stock === false || $stock < 0) {
                throw new InvalidArgumentException('Verifica los datos. El precio debe ser mayor que 0 y el stock no puede ser negativo.');
            }

            if ($id) {
                $stmt = $pdo->prepare("UPDATE productos SET nombre = ?, categoria = ?, precio = ?, stock = ? WHERE id_producto = ?");
                $stmt->execute([$nombre, $categoria, $precio, $stock, $id]);
                flash('success', 'Producto modificado correctamente.');
            } else {
                $stmt = $pdo->prepare("INSERT INTO productos (nombre, categoria, precio, stock) VALUES (?, ?, ?, ?)");
                $stmt->execute([$nombre, $categoria, $precio, $stock]);
                flash('success', 'Producto registrado correctamente.');
            }

            redireccionar('productos.php');
        }

        if ($accion === 'eliminar') {
            $id = filter_input(INPUT_POST, 'id_producto', FILTER_VALIDATE_INT);
            if (!$id) throw new InvalidArgumentException('ID inválido.');

            $stmt = $pdo->prepare("DELETE FROM productos WHERE id_producto = ?");
            $stmt->execute([$id]);
            flash('success', 'Producto eliminado correctamente.');
            redireccionar('productos.php');
        }
    } catch (Throwable $e) {
        flash('error', $e instanceof InvalidArgumentException ? $e->getMessage() : 'No se pudo realizar la operación. El nombre del producto debe ser único.');
        redireccionar('productos.php');
    }
}

$lista = cargarLista($pdo, 'productos');
$lista->bubbleSort('nombre', true);
$productosOrdenados = $lista->recorrer();
$flash = obtenerFlash();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Productos - CRUD EDA311</title>
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
<h1><?= $editar ? 'Modificar producto' : 'Registrar producto' ?></h1>
<form method="post" class="formulario">
<input type="hidden" name="accion" value="guardar">
<?php if ($editar): ?><input type="hidden" name="id_producto" value="<?= e($editar['id_producto']) ?>"><?php endif; ?>

<label>Nombre
<input type="text" name="nombre" maxlength="100" required value="<?= e($editar['nombre'] ?? '') ?>">
</label>
<label>Categoría
<input type="text" name="categoria" maxlength="50" required value="<?= e($editar['categoria'] ?? '') ?>">
</label>
<label>Precio
<input type="number" name="precio" min="0.01" step="0.01" required value="<?= e($editar['precio'] ?? '') ?>">
</label>
<label>Stock
<input type="number" name="stock" min="0" required value="<?= e($editar['stock'] ?? '') ?>">
</label>

<div class="acciones">
<button class="btn primario" type="submit"><?= $editar ? 'Guardar cambios' : 'Registrar' ?></button>
<?php if ($editar): ?><a class="btn secundario" href="productos.php">Cancelar</a><?php endif; ?>
</div>
</form>
</section>

<section class="panel">
<div class="titulo-tabla">
<div><h2>Productos ordenados</h2><p>Bubble Sort por nombre, aplicado directamente sobre la lista doblemente enlazada.</p></div>
<span class="badge"><?= $lista->cantidad() ?> registros</span>
</div>

<div class="tabla-scroll">
<table>
<thead><tr><th>ID</th><th>Nombre</th><th>Categoría</th><th>Precio</th><th>Stock</th><th>Acciones</th></tr></thead>
<tbody>
<?php foreach ($productosOrdenados as $producto): ?>
<tr>
<td><?= e($producto['id_producto']) ?></td>
<td><?= e($producto['nombre']) ?></td>
<td><?= e($producto['categoria']) ?></td>
<td>Bs <?= number_format((float)$producto['precio'], 2) ?></td>
<td><?= e($producto['stock']) ?></td>
<td class="acciones-tabla">
<a class="btn pequeno" href="?editar=<?= e($producto['id_producto']) ?>">Editar</a>
<form method="post" onsubmit="return confirm('¿Eliminar este producto?');">
<input type="hidden" name="accion" value="eliminar">
<input type="hidden" name="id_producto" value="<?= e($producto['id_producto']) ?>">
<button class="btn pequeno peligro" type="submit">Eliminar</button>
</form>
</td>
</tr>
<?php endforeach; ?>
<?php if (!$productosOrdenados): ?><tr><td colspan="6" class="vacio">No hay productos registrados.</td></tr><?php endif; ?>
</tbody>
</table>
</div>
</section>
</main>
</body>
</html>
