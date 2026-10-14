# EDA311_HITO3_IbañezSofia

Proyecto práctico: aplicación CRUD con PHP, MySQL, lista doblemente enlazada y Bubble Sort.

## 1. Requisitos

- PHP 8.0 o superior.
- MySQL.
- Apache .
- Base de datos `proyecto_estructura`.
- Git y GitHub.

## 2. Estructura

```text
EDA311_HITO3_ApellidoNombre/
│
├── db.php
├── estructuras.php
├── funciones.php
├── index.php
├── clientes.php
├── pedidos.php
├── productos.php
└── css/
    └── estilos.css
```

## 3. Base de datos

La aplicación está preparada para las tablas:

- `clientes`
- `pedidos`
- `productos`

y utiliza exactamente los campos proporcionados por el docente.

La conexión se encuentra en `db.php`:

```php
$host = 'localhost';
$dbname = 'tienda_ibañez';
$user = 'root';
$pass = '';
```

Si tu instalación utiliza otra contraseña para MySQL, cambia únicamente `$pass`.

## 4. Lista doblemente enlazada

`estructuras.php` contiene:

- `Nodo`: representa cada elemento y mantiene `anterior` y `siguiente`.
- `ListaDoble`: administra la cabeza, cola y cantidad de nodos.
- `insertarFinal()`: agrega un nodo al final.
- `recorrer()`: recorre la lista desde la cabeza.
- `bubbleSort()`: ordena directamente los nodos.
- `cargarLista()`: recupera datos de MySQL y los introduce en la lista.

El Bubble Sort NO convierte la lista en un arreglo. Se recorren los nodos y se intercambian los datos entre nodos vecinos.

## 5. Criterios de ordenamiento

- Clientes: por `nombre`, ascendente.
- Productos: por `nombre`, ascendente.
- Pedidos: por `fecha`, ascendente.

## 6. Ejecución

1. Copiar la carpeta dentro de `htdocs` si utilizas XAMPP.
2. Iniciar Apache y MySQL.
3. Crear/importar la base de datos del docente.
4. Confirmar que la base de datos se llame `proyecto_estructura`.
5. Abrir:

`http://localhost/EDA311_HITO3_ApellidoNombre/`

## 7. Git

Se recomienda realizar commits pequeños y descriptivos, por ejemplo:

```text
git init
git add db.php
git commit -m "Configura conexión PDO con MySQL"

git add estructuras.php
git commit -m "Implementa lista doblemente enlazada y Bubble Sort"

git add clientes.php
git commit -m "Implementa CRUD de clientes"

git add productos.php
git commit -m "Implementa CRUD de productos"

git add pedidos.php
git commit -m "Implementa CRUD de pedidos"

git add css/
git commit -m "Agrega interfaz gráfica del proyecto"

git add README.md
git commit -m "Documenta instalación y funcionamiento"
```

Después crear el repositorio público con el nombre:

`EDA311_HITO3_ApellidoNombre`

y subir el proyecto.

## 8. Nota sobre pedidos

La tabla `pedidos` tiene una clave foránea hacia `clientes`. Por eso el formulario de pedidos permite seleccionar solamente clientes existentes. También es posible que MySQL impida eliminar un cliente que tenga pedidos asociados, dependiendo de las reglas de la clave foránea.
