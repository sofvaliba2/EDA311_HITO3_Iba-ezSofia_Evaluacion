<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/funciones.php';

$db = new Database();
$pdo = $db->getPDO();

$conteos = [];
foreach (['clientes', 'pedidos', 'productos'] as $tabla) {
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM `$tabla`");
    $conteos[$tabla] = (int)$stmt->fetch()['total'];
}

$flash = obtenerFlash();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EDA311 - Gestión CRUD</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
<header class="hero">
    <div class="contenedor">
        <p class="eyebrow">EDA311 · HITO 3 ESTUDIANTE: SOFIA VALERIA IBAÑEZ BUSTILLOS</p>
        <h1>Gestión de clientes, pedidos y productos</h1>
        <p>Aplicación CRUD con PHP, MySQL, lista doblemente enlazada y Bubble Sort.</p>
    </div>
</header>

<main class="contenedor">
    <?php if ($flash): ?>
        <div class="alert <?= e($flash['tipo']) ?>"><?= e($flash['mensaje']) ?></div>
    <?php endif; ?>

    <section class="tarjetas">
        <a class="tarjeta" href="clientes.php">
            
            <h2>Clientes</h2>
            <strong><?= $conteos['clientes'] ?></strong>
            <span>registros</span>
        </a>

        <a class="tarjeta" href="pedidos.php">
        
            <h2>Pedidos</h2>
            <strong><?= $conteos['pedidos'] ?></strong>
            <span>registros</span>
        </a>

        <a class="tarjeta" href="productos.php">
           
            <h2>Productos</h2>
            <strong><?= $conteos['productos'] ?></strong>
            <span>registros</span>
        </a>
    </section>


</main>
</body>
</html>
