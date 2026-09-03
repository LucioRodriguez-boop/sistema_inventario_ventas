<?php

session_start();


// Verificar sesión
if (!isset($_SESSION['nombre'])) {

    header("Location: login.php");
    exit();

}


// CONEXIÓN A LA BASE DE DATOS

include("conexion.php");


// Verificar conexión

if (!$conn || $conn->connect_error) {

    die("Error de conexión a la base de datos.");

}



// ==============================
// BUSCADOR
// ==============================

$busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : "";



if ($busqueda !== "") {


    $sql = "SELECT 
                p.id,
                p.nombre_producto,
                c.nombre_categoria,
                p.stock,
                p.precio

            FROM productos p

            INNER JOIN categorias c
            ON p.categoria_id = c.id

            WHERE p.nombre_producto LIKE ?
            OR c.nombre_categoria LIKE ?

            ORDER BY p.id ASC";



    $stmt = $conn->prepare($sql);



    if (!$stmt) {

        die("Error al preparar la consulta: " . $conn->error);

    }

 // Escapar caracteres especiales de LIKE

    $escapado = str_replace(
        ['\\', '%', '_'],
        ['\\\\', '\\%', '\\_'],
        $busqueda
    );

 $param_busqueda = "%" . $escapado . "%";



    $stmt->bind_param(
        "ss",
        $param_busqueda,
        $param_busqueda
    );



    $stmt->execute();



    $resultado = $stmt->get_result();



} else {

     $sql = "SELECT 
                p.id,
                p.nombre_producto,
                c.nombre_categoria,
                p.stock,
                p.precio

            FROM productos p

            INNER JOIN categorias c
            ON p.categoria_id = c.id

            ORDER BY p.id ASC";

    $resultado = $conn->query($sql);


}

// Verificar consulta

if ($resultado === false) {

    die("Error al ejecutar la consulta: " . $conn->error);

}
?>



<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Inventario</title>

<style>

body {
    font-family:'Segoe UI',
    Tahoma,
    Geneva,
    Verdana,
    sans-serif;
    background:#f8fafc;
    padding:20px;
}

.container {
    max-width:1000px;
    margin:auto;
    background:white;
    padding:20px;
    border-radius:8px;
    box-shadow:0 4px 6px rgba(0,0,0,.05);
}


.header {
    display:flex;
    justify-content:space-between;
    align-items:center;
    border-bottom:2px solid #e2e8f0;
    padding-bottom:15px;
    margin-bottom:20px;
}


h2 {
    color:#0f172a;
    margin:0 0 10px 0;
}


.btn-salir {
    background:#ef4444;
    color:white;
    text-decoration:none;
    padding:8px 15px;
    border-radius:5px;
    font-weight:bold;
}


.btn-salir:hover {
    background:#dc2626;

}


.btn-nuevo {
    background:#3b82f6;
    color:white;
    padding:10px 15px;
    text-decoration:none;
    border-radius:5px;
    font-weight:bold;
}


form {
    margin-bottom:20px;
}


input {
    padding:8px;
    width:250px;
    border:1px solid #cbd5e1;
    border-radius:5px;
}


.btn-buscar {
    background:#10b981;
    color:white;
    border:none;
    padding:8px 15px;
    border-radius:5px;
    cursor:pointer;
}


.btn-limpiar {
    background:#64748b;
    color:white;
    padding:8px 15px;
    text-decoration:none;
    border-radius:5px;
}


table {
    width:100%;
    border-collapse:collapse;
}

th,
td {
    padding:12px;
    text-align:left;
    border-bottom:1px solid #e2e8f0;
}

th {
    background:#f1f5f9;
}


tr:hover {
    background:#f8fafc;
}



.stock-bajo {
    color:red;
    font-weight:bold;
}


.btn-editar {
    background:#f59e0b;
    color:white;
    padding:6px 10px;
    text-decoration:none;
    border-radius:5px;
}


.btn-eliminar {
    background:#ef4444;
    color:white;
    padding:6px 10px;
    text-decoration:none;
    border-radius:5px;
    border:none;
    cursor:pointer;
    font-size:inherit;
    font-family:inherit;
}


</style>

</head>
<body>

<div class="container">

    <div class="header">

        <div>

||        <h2>
                Catálogo de Inventario
            </h2>
          </div>

        <div>
            <span>
                Usuario:
                <strong>
                    <?php echo htmlspecialchars($_SESSION['nombre']); ?>
                </strong>
            </span>
            <br><br>

            <a href="nuevo_producto.php"
               class="btn-nuevo">
                + Nuevo Producto
            </a>

            <a href="logout.php"
               class="btn-salir">
                Cerrar Sesión
            </a>

        </div>

    </div>

    <!-- BUSCADOR -->
    <form method="GET">
        <input type="text"
            name="buscar"
            placeholder="Buscar producto o categoría..."
            value="<?php echo htmlspecialchars($busqueda); ?>"        >

        <button class="btn-buscar">🔍 Buscar</button>


        <a href="inventario.php"
           class="btn-limpiar">
            Limpiar
        </a>

    </form>

    <table>

        <thead>
            <tr>
                <th>Código</th>
                <th>Nombre del Producto</th>
                <th>Categoría</th>
                <th>Stock</th>
                <th>Precio Unitario</th>
                <th>Acciones</th>
            </tr>

        </thead>

        <tbody>

<?php

        if ($resultado->num_rows > 0) {

            while ($fila = $resultado->fetch_assoc()) {

                $claseStock = ($fila['stock'] < 10)
                    ? "stock-bajo"
                    : "";        
?>


            <tr>
                <td>
                    <?php echo (int)$fila['id']; ?>
                </td>

                <td>
                    <?php echo htmlspecialchars($fila['nombre_producto']); ?>
                </td>

                <td>
                    <?php echo htmlspecialchars($fila['nombre_categoria']); ?>
                </td>

                <td class="<?php echo $claseStock; ?>">

                    <?php echo (int)$fila['stock']; ?>
                    unds.

                </td>

                <td>
                    $
                    <?php echo number_format((float)$fila['precio'],2); ?>
                </td>

                <td>
                    <a href="editar_producto.php?id=<?php echo (int)$fila['id']; ?>"
                       class="btn-editar">
                        ✏️ Editar
                    </a>

                    <form action="eliminar_producto.php"
                          method="POST"
                          style="display:inline;"
                          onsubmit="return confirm('¿Seguro que deseas eliminar este producto?');">


                        <input 
                            type="hidden"
                            name="id"
                            value="<?php echo (int)$fila['id']; ?>"
                        >


                        <button 
                            type="submit"
                            class="btn-eliminar">
                            🗑️ Eliminar

                        </button>
                    </form>
                </td>
            </tr>


        <?php
            }
        } else {

        ?>


            <tr>
                <td colspan="6" style="text-align:center;">
                    No hay productos registrados.
                </td>
            </tr>            
        <?php

        }

        if (isset($stmt)) {
            $stmt->close();
        }

        $conn->close();

        ?>

        </tbody>
    </table>

</div>

</body>
</html>