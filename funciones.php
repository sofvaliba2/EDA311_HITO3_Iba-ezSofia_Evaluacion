<?php
declare(strict_types=1);

function e(mixed $valor): string {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

function redireccionar(string $url): never {
    header("Location: $url");
    exit;
}

function flash(string $tipo, string $mensaje): void {
    $_SESSION['flash'] = ['tipo' => $tipo, 'mensaje' => $mensaje];
}

function obtenerFlash(): ?array {
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}
