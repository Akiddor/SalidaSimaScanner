<?php
// Asegurarnos de que no haya salida antes de los headers
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Verificar la ruta correcta al archivo de base de datos
require_once '../backend/db/db.php';

// Configurar headers
header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];
     

try {
    // Verificar la conexión a la base de datos
    if (!isset($enlace) || !$enlace) {
        throw new Exception('Error de conexión a la base de datos');
    }

    // Verificar si se recibió un ID
    if (!isset($_GET['id'])) {
        throw new Exception('ID del registro no proporcionado');
    }

    // Validar el ID
    $item_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($item_id === false) {
        throw new Exception('ID inválido');
    }

    // Eliminar el registro
    $delete_query = "DELETE FROM calidad_cajas_scanned WHERE id = ?";
    $stmt = mysqli_prepare($enlace, $delete_query);
    
    if (!$stmt) {
        throw new Exception('Error al preparar la consulta: ' . mysqli_error($enlace));
    }

    mysqli_stmt_bind_param($stmt, "i", $item_id);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Error al ejecutar la consulta: ' . mysqli_stmt_error($stmt));
    }

    if (mysqli_affected_rows($enlace) > 0) {
        $response['success'] = true;
        $response['message'] = 'Registro eliminado exitosamente';
    } else {
        $response['message'] = 'No se encontró el registro para eliminar';
    }

    mysqli_stmt_close($stmt);

} catch (Exception $e) {
    error_log('Error en delete_item_calidad.php: ' . $e->getMessage());
    $response['message'] = 'Error del servidor: ' . $e->getMessage();
} finally {
    echo json_encode($response);
    exit;
}