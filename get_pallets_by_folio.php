<?php
require 'backend/db/db.php';

// Habilitar reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Establecer headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    // Verificar si se recibió el ID del folio
    if (!isset($_GET['folio_id'])) {
        throw new Exception('No se proporcionó ID del folio');
    }

    $folioId = intval($_GET['folio_id']);

    // Verificar que la conexión existe
    if (!isset($enlace)) {
        throw new Exception('Error de conexión a la base de datos');
    }

    // Consulta para obtener el folio
    $folioQuery = "SELECT folio_number FROM Folios WHERE id = $folioId";
    $folioResult = mysqli_query($enlace, $folioQuery);

    if (!$folioResult) {
        throw new Exception('Error en la consulta del folio: ' . mysqli_error($enlace));
    }

    $folioData = mysqli_fetch_assoc($folioResult);

    if (!$folioData) {
        throw new Exception('Folio no encontrado');
    }

    // Consulta para obtener los pallets
    $palletsQuery = "SELECT id FROM Pallets WHERE folio_id = $folioId ORDER BY id ASC";
    $palletsResult = mysqli_query($enlace, $palletsQuery);

    if (!$palletsResult) {
        throw new Exception('Error en la consulta de pallets: ' . mysqli_error($enlace));
    }

    $pallets = [];
    while ($pallet = mysqli_fetch_assoc($palletsResult)) {
        $pallets[] = ['id' => $pallet['id']];
    }

    // Preparar respuesta
    $response = [
        'success' => true,
        'folio_number' => $folioData['folio_number'],
        'pallets' => $pallets,
        'message' => count($pallets) . ' pallets encontrados'
    ];

    echo json_encode($response);

} catch (Exception $e) {
    // Enviar respuesta de error
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'details' => 'Error en el servidor'
    ]);
}
