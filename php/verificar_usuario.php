<?php
/**
 * Script para verificar si un usuario ya existe
 * Utilizado por Ajax para validación en tiempo real
 */

// Habilitar manejo de errores
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Headers para JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Incluir configuración de base de datos
require_once 'config.php';

// Verificar que la petición sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['existe' => false, 'message' => 'Método no permitido']);
    exit;
}

// Obtener conexión a la base de datos
$conexion = obtenerConexion();
if (!$conexion) {
    http_response_code(500);
    echo json_encode(['existe' => false, 'message' => 'Error de conexión a la base de datos']);
    exit;
}

// Obtener y validar datos POST
$tipoDocumento = $_POST['tipo_documento'] ?? '';
$documento = $_POST['documento'] ?? '';

// Validar que los datos estén presentes
if (empty($tipoDocumento) || empty($documento)) {
    cerrarConexion($conexion);
    echo json_encode(['existe' => false, 'message' => 'Datos incompletos']);
    exit;
}

// Validar formato del documento (solo números)
if (!preg_match('/^\d+$/', $documento)) {
    cerrarConexion($conexion);
    echo json_encode(['existe' => false, 'message' => 'Formato de documento inválido']);
    exit;
}

// Validar tipo de documento
$tiposValidos = ['CC', 'CE', 'PA', 'TI'];
if (!in_array($tipoDocumento, $tiposValidos)) {
    cerrarConexion($conexion);
    echo json_encode(['existe' => false, 'message' => 'Tipo de documento inválido']);
    exit;
}

// Preparar consulta para verificar si el usuario existe
$sql = "SELECT id FROM usuarios WHERE tipo_documento = ? AND documento = ?";
$stmt = $conexion->prepare($sql);

if (!$stmt) {
    error_log("Error en prepare: " . $conexion->error);
    cerrarConexion($conexion);
    echo json_encode(['existe' => false, 'message' => 'Error al verificar usuario']);
    exit;
}

// Ejecutar consulta
$stmt->bind_param("ss", $tipoDocumento, $documento);
$stmt->execute();
$result = $stmt->get_result();
$existe = $result->num_rows > 0;
$stmt->close();

// Cerrar conexión
cerrarConexion($conexion);

// Retornar resultado
echo json_encode([
    'existe' => $existe,
    'message' => $existe ? 'Usuario ya registrado' : 'Usuario disponible'
]);

?>

