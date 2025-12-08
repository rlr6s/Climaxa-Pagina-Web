<?php
require_once("config/database.php");
$conn = getDBConnection();

echo "<h2>Estructura de la Base de Datos CLIMAXA</h2>";

// Verificar si la tabla existe
$sql = "SHOW TABLES LIKE 'usuarios'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "✅ La tabla 'usuarios' existe<br><br>";
    
    // Mostrar estructura de la tabla
    echo "<h3>Estructura de la tabla 'usuarios':</h3>";
    $sql_structure = "DESCRIBE usuarios";
    $result_structure = $conn->query($sql_structure);
    
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($row = $result_structure->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Mostrar usuarios existentes
    echo "<h3>Usuarios registrados:</h3>";
    $sql_users = "SELECT id, nombre, correo, creado_en FROM usuarios";
    $result_users = $conn->query($sql_users);
    
    if ($result_users->num_rows > 0) {
        echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Correo</th><th>Fecha Creación</th></tr>";
        
        while ($row = $result_users->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['nombre'] . "</td>";
            echo "<td>" . $row['correo'] . "</td>";
            echo "<td>" . $row['creado_en'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No hay usuarios registrados";
    }
    
} else {
    echo "❌ La tabla 'usuarios' NO existe";
}

$conn->close();
?>