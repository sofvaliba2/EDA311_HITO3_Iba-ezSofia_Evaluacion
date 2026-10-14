<?php
declare(strict_types=1);

class Nodo {
    public array $dato;
    public ?Nodo $anterior = null;
    public ?Nodo $siguiente = null;

    public function __construct(array $dato) {
        $this->dato = $dato;
    }
}

class ListaDoble {
    private ?Nodo $cabeza = null;
    private ?Nodo $cola = null;
    private int $cantidad = 0;

    public function insertarFinal(array $dato): void {
        $nuevo = new Nodo($dato);

        if ($this->cabeza === null) {
            $this->cabeza = $nuevo;
            $this->cola = $nuevo;
        } else {
            $nuevo->anterior = $this->cola;
            $this->cola->siguiente = $nuevo;
            $this->cola = $nuevo;
        }

        $this->cantidad++;
    }

    public function cantidad(): int {
        return $this->cantidad;
    }

    public function recorrer(): array {
        $resultado = [];
        $actual = $this->cabeza;

        while ($actual !== null) {
            $resultado[] = $actual->dato;
            $actual = $actual->siguiente;
        }

        return $resultado;
    }

   
    public function bubbleSort(string $campo, bool $ascendente = true): void {
        if ($this->cabeza === null || $this->cabeza->siguiente === null) {
            return;
        }

        do {
            $huboCambio = false;
            $actual = $this->cabeza;

            while ($actual !== null && $actual->siguiente !== null) {
                $siguiente = $actual->siguiente;

                $a = $actual->dato[$campo] ?? null;
                $b = $siguiente->dato[$campo] ?? null;

                $comparacion = $this->comparar($a, $b);

                $debeIntercambiar = $ascendente
                    ? $comparacion > 0
                    : $comparacion < 0;

                if ($debeIntercambiar) {
                    $temporal = $actual->dato;
                    $actual->dato = $siguiente->dato;
                    $siguiente->dato = $temporal;
                    $huboCambio = true;
                }

                $actual = $actual->siguiente;
            }
        } while ($huboCambio);
    }

    private function comparar($a, $b): int {
        if (is_numeric($a) && is_numeric($b)) {
            return $a <=> $b;
        }

        return strcasecmp((string)$a, (string)$b);
    }
}


function cargarLista(PDO $pdo, string $tabla): ListaDoble {
    $tablasPermitidas = ['clientes', 'pedidos', 'productos'];

    if (!in_array($tabla, $tablasPermitidas, true)) {
        throw new InvalidArgumentException('Tabla no permitida.');
    }

    $stmt = $pdo->query("SELECT * FROM `$tabla`");
    $lista = new ListaDoble();

    while ($registro = $stmt->fetch()) {
        $lista->insertarFinal($registro);
    }

    return $lista;
}
