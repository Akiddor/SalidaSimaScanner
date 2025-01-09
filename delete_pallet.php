<?php
require 'backend/db/db.php';

// Configurar headers
header('Content-Type: application/json');

// Habilitar reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

$response = ['success' => false, 'message' => ''];

try {
    // Verificar método de la petición
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido. Se requiere POST.');
    }

    // Verificar ID del pallet
    if (!isset($_POST['pallet_id'])) {
        throw new Exception('ID de pallet no proporcionado.');
    }

    $pallet_id = intval($_POST['pallet_id']);
    
    if ($pallet_id <= 0) {
        throw new Exception('ID de pallet no válido.');
    }

    // Primero eliminar registros relacionados en Cajas_scanned
    $deleteCajasQuery = "DELETE FROM Cajas_scanned WHERE pallet_id = ?";
    $stmtCajas = mysqli_prepare($enlace, $deleteCajasQuery);
    mysqli_stmt_bind_param($stmtCajas, 'i', $pallet_id);
    
    if (!mysqli_stmt_execute($stmtCajas)) {
        throw new Exception('Error al eliminar cajas asociadas: ' . mysqli_error($enlace));
    }

    // Luego eliminar el pallet
    $deletePalletQuery = "DELETE FROM Pallets WHERE id = ?";
    $stmtPallet = mysqli_prepare($enlace, $deletePalletQuery);
    mysqli_stmt_bind_param($stmtPallet, 'i', $pallet_id);
    
    if (!mysqli_stmt_execute($stmtPallet)) {
        throw new Exception('Error al eliminar el pallet: ' . mysqli_error($enlace));
    }

    $response['success'] = true;
    $response['message'] = 'Pallet eliminado exitosamente.';

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    error_log('Error en delete_pallet.php: ' . $e->getMessage());
}

echo json_encode($response);
