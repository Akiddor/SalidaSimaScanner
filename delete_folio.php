<?php
require 'backend/db/db.php';

// Configurar headers
header('Content-Type: application/json');

// Habilitar reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Inicializar respuesta
$response = ['success' => false, 'message' => ''];

try {
    // Log para debugging
    error_log('Petición recibida en delete_folio.php');
    error_log('POST data: ' . print_r($_POST, true));

    // Verificar si es una petición POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido. Se requiere POST.');
    }

    // Verificar si se recibió el ID del folio
    if (!isset($_POST['folio_id'])) {
        throw new Exception('ID de folio no proporcionado');
    }

    // Obtener y validar el ID del folio
    $folio_id = intval($_POST['folio_id']);
    if ($folio_id <= 0) {
        throw new Exception('ID de folio no válido');
    }

    // Iniciar transacción
    mysqli_begin_transaction($enlace);

    try {
        // Eliminar los pallets asociados
        $delete_pallets = "DELETE FROM Pallets WHERE folio_id = ?";
        $stmt_pallets = mysqli_prepare($enlace, $delete_pallets);
        mysqli_stmt_bind_param($stmt_pallets, 'i', $folio_id);
        
        if (!mysqli_stmt_execute($stmt_pallets)) {
            throw new Exception('Error al eliminar los pallets: ' . mysqli_error($enlace));
        }

        // Eliminar el folio
        $delete_folio = "DELETE FROM Folios WHERE id = ?";
        $stmt_folio = mysqli_prepare($enlace, $delete_folio);
        mysqli_stmt_bind_param($stmt_folio, 'i', $folio_id);
        
        if (!mysqli_stmt_execute($stmt_folio)) {
            throw new Exception('Error al eliminar el folio: ' . mysqli_error($enlace));
        }

        // Confirmar transacción
        mysqli_commit($enlace);

        $response['success'] = true;
        $response['message'] = 'Folio y pallets asociados eliminados exitosamente';
        
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        mysqli_rollback($enlace);
        throw $e;
    }

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    error_log('Error en delete_folio.php: ' . $e->getMessage());
}

// Enviar respuesta
echo json_encode($response);
