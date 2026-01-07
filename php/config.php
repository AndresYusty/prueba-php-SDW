<?php
/**
 * Archivo de configuración de la base de datos
 * Configuración para conexión a MySQL
 */

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'Manchas12345.');
define('DB_NAME', 'formulario_db');

/**
 * Función para obtener conexión a la base de datos
 * @return mysqli|false - Objeto de conexión o false en caso de error
 */
function obtenerConexion() {
    $conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Verificar conexión
    if ($conexion->connect_error) {
        error_log("Error de conexión: " . $conexion->connect_error);
        return false;
    }
    
    // Establecer charset UTF-8
    $conexion->set_charset("utf8");
    
    return $conexion;
}

/**
 * Función para cerrar conexión a la base de datos
 * @param mysqli $conexion - Objeto de conexión
 */
function cerrarConexion($conexion) {
    if ($conexion) {
        $conexion->close();
    }
}

?>

