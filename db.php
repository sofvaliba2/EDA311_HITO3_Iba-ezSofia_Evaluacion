<?php
declare(strict_types=1);

class Database {
    private PDO $pdo;

    public function __construct() {
        $host = '10.0.40.85';
        $dbname = 'tienda_ibanez';
        $user = 'u_ibanez_sofia';
        $pass = 'Luz#26D4vN8';
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $this->pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            throw new RuntimeException('Error de conexión con la base de datos.');
        }
    }

    public function getPDO(): PDO {
        return $this->pdo;
    }
}
