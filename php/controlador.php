<?php
/**
 * Controlador principal para el manejo del formulario
 * Procesa datos POST, valida y almacena en base de datos
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

/**
 * Función para validar y sanitizar datos de entrada
 * @param mixed $data - Datos a validar
 * @param string $type - Tipo de validación
 * @return mixed - Datos validados o false si no son válidos
 */
function validarDato($data, $type = 'string') {
    if (!isset($data) || $data === '') {
        return false;
    }
    
    switch ($type) {
        case 'string':
            return trim(htmlspecialchars(strip_tags($data), ENT_QUOTES, 'UTF-8'));
        
        case 'int':
            $int = filter_var($data, FILTER_VALIDATE_INT);
            return ($int !== false) ? $int : false;
        
        case 'documento':
            // Solo números
            if (preg_match('/^\d+$/', $data)) {
                return trim($data);
            }
            return false;
        
        case 'nombre':
            // Solo letras, números y espacios
            if (preg_match('/^[A-Za-z0-9\s]+$/', $data)) {
                return trim($data);
            }
            return false;
        
        case 'tipo_documento':
            $tiposValidos = ['CC', 'CE', 'PA', 'TI'];
            if (in_array($data, $tiposValidos)) {
                return $data;
            }
            return false;
        
        case 'genero':
            $generosValidos = ['M', 'F', 'O', 'PN'];
            if (in_array($data, $generosValidos)) {
                return $data;
            }
            return false;
        
        case 'coordenada':
            $float = filter_var($data, FILTER_VALIDATE_FLOAT);
            return ($float !== false) ? $float : false;
        
        default:
            return false;
    }
}

/**
 * Verificar si un usuario ya existe
 * @param mysqli $conexion - Conexión a la base de datos
 * @param string $tipoDocumento - Tipo de documento
 * @param string $documento - Número de documento
 * @param int|null $userId - ID del usuario (para excluir en modificación)
 * @return bool - true si existe, false si no existe
 */
function usuarioExiste($conexion, $tipoDocumento, $documento, $userId = null) {
    $sql = "SELECT id FROM usuarios WHERE tipo_documento = ? AND documento = ?";
    $params = [$tipoDocumento, $documento];
    $types = "ss";
    
    // Si estamos modificando, excluir el usuario actual
    if ($userId) {
        $sql .= " AND id != ?";
        $params[] = $userId;
        $types .= "i";
    }
    
    $stmt = $conexion->prepare($sql);
    if (!$stmt) {
        error_log("Error en prepare: " . $conexion->error);
        return false;
    }
    
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $existe = $result->num_rows > 0;
    $stmt->close();
    
    return $existe;
}

/**
 * Insertar nuevo usuario en la base de datos
 * @param mysqli $conexion - Conexión a la base de datos
 * @param array $datos - Array con los datos del usuario
 * @return array - Array con success y message
 */
function insertarUsuario($conexion, $datos) {
    $sql = "INSERT INTO usuarios (tipo_documento, documento, nombre, edad, genero, preferencias, latitud, longitud, fecha_creacion) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $conexion->prepare($sql);
    if (!$stmt) {
        error_log("Error en prepare: " . $conexion->error);
        return ['success' => false, 'message' => 'Error al preparar la consulta'];
    }
    
    $stmt->bind_param("sssissss", 
        $datos['tipo_documento'],
        $datos['documento'],
        $datos['nombre'],
        $datos['edad'],
        $datos['genero'],
        $datos['preferencias'],
        $datos['latitud'],
        $datos['longitud']
    );
    
    if ($stmt->execute()) {
        $stmt->close();
        return ['success' => true, 'message' => 'Usuario registrado exitosamente'];
    } else {
        $error = $stmt->error;
        $stmt->close();
        error_log("Error al insertar: " . $error);
        return ['success' => false, 'message' => 'Error al registrar el usuario: ' . $error];
    }
}

/**
 * Actualizar usuario existente
 * @param mysqli $conexion - Conexión a la base de datos
 * @param array $datos - Array con los datos del usuario
 * @return array - Array con success y message
 */
function actualizarUsuario($conexion, $datos) {
    $sql = "UPDATE usuarios SET 
            tipo_documento = ?,
            documento = ?,
            nombre = ?,
            edad = ?,
            genero = ?,
            preferencias = ?,
            latitud = ?,
            longitud = ?,
            fecha_actualizacion = NOW()
            WHERE id = ?";
    
    $stmt = $conexion->prepare($sql);
    if (!$stmt) {
        error_log("Error en prepare: " . $conexion->error);
        return ['success' => false, 'message' => 'Error al preparar la consulta'];
    }
    
    $stmt->bind_param("sssissssi",
        $datos['tipo_documento'],
        $datos['documento'],
        $datos['nombre'],
        $datos['edad'],
        $datos['genero'],
        $datos['preferencias'],
        $datos['latitud'],
        $datos['longitud'],
        $datos['user_id']
    );
    
    if ($stmt->execute()) {
        $stmt->close();
        return ['success' => true, 'message' => 'Usuario actualizado exitosamente'];
    } else {
        $error = $stmt->error;
        $stmt->close();
        error_log("Error al actualizar: " . $error);
        return ['success' => false, 'message' => 'Error al actualizar el usuario: ' . $error];
    }
}

// Verificar que la petición sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Obtener conexión a la base de datos
$conexion = obtenerConexion();
if (!$conexion) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']);
    exit;
}

// Array para almacenar errores de validación
$errores = [];

// Validar y obtener datos POST
$tipoDocumento = validarDato($_POST['tipo_documento'] ?? '', 'tipo_documento');
if (!$tipoDocumento) {
    $errores[] = 'Tipo de documento inválido o no proporcionado';
}

$documento = validarDato($_POST['documento'] ?? '', 'documento');
if (!$documento) {
    $errores[] = 'Documento inválido. Solo se permiten números';
}

$nombre = validarDato($_POST['nombre'] ?? '', 'nombre');
if (!$nombre) {
    $errores[] = 'Nombre inválido. Solo se permiten letras, números y espacios';
}

$edad = validarDato($_POST['edad'] ?? '', 'int');
if (!$edad || $edad < 18 || $edad > 120) {
    $errores[] = 'Edad inválida. Debe ser mayor de 18 años y menor de 120';
}

$genero = validarDato($_POST['genero'] ?? '', 'genero');
if (!$genero) {
    $errores[] = 'Género inválido o no proporcionado';
}

// Validar preferencias (array)
$preferencias = $_POST['preferencias'] ?? [];
if (!is_array($preferencias) || empty($preferencias)) {
    $errores[] = 'Debe seleccionar al menos una preferencia';
} else {
    // Convertir array a JSON
    $preferenciasJson = json_encode($preferencias, JSON_UNESCAPED_UNICODE);
    if ($preferenciasJson === false) {
        $errores[] = 'Error al procesar las preferencias';
    }
}

// Coordenadas (opcionales)
$latitud = validarDato($_POST['latitud'] ?? '', 'coordenada');
$longitud = validarDato($_POST['longitud'] ?? '', 'coordenada');

// Si no hay coordenadas, establecer NULL
$latitud = $latitud !== false ? $latitud : null;
$longitud = $longitud !== false ? $longitud : null;

// Obtener user_id si existe (para modificación)
$userId = validarDato($_POST['user_id'] ?? '', 'int');
$userId = $userId !== false ? $userId : null;

// Si hay errores de validación, retornarlos
if (!empty($errores)) {
    cerrarConexion($conexion);
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => implode('. ', $errores)]);
    exit;
}

// Verificar si el usuario ya existe (solo en creación o si cambió documento en modificación)
if (!$userId || ($userId && $documento)) {
    if (usuarioExiste($conexion, $tipoDocumento, $documento, $userId)) {
        cerrarConexion($conexion);
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Este usuario ya está registrado']);
        exit;
    }
}

// Preparar datos para insertar/actualizar
$datos = [
    'tipo_documento' => $tipoDocumento,
    'documento' => $documento,
    'nombre' => $nombre,
    'edad' => $edad,
    'genero' => $genero,
    'preferencias' => $preferenciasJson,
    'latitud' => $latitud,
    'longitud' => $longitud
];

// Insertar o actualizar según corresponda
if ($userId) {
    // Modo actualización
    $datos['user_id'] = $userId;
    $resultado = actualizarUsuario($conexion, $datos);
} else {
    // Modo creación
    $resultado = insertarUsuario($conexion, $datos);
}

// Cerrar conexión
cerrarConexion($conexion);

// Retornar respuesta
if ($resultado['success']) {
    http_response_code(200);
} else {
    http_response_code(400);
}

echo json_encode($resultado);

?>

