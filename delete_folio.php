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
        // Obtener todos los `pallet_id` del folio que queremos eliminar
        $pallets_query = "SELECT id FROM Pallets WHERE folio_id = ?";
        $stmt_pallets_query = mysqli_prepare($enlace, $pallets_query);
        mysqli_stmt_bind_param($stmt_pallets_query, 'i', $folio_id);
        mysqli_stmt_execute($stmt_pallets_query);
        $result_pallets = mysqli_stmt_get_result($stmt_pallets_query);

        // Si no existen pallets asociados, podemos pasar directamente al resto del borrado
        $pallet_ids = [];
        while ($row = mysqli_fetch_assoc($result_pallets)) {
            $pallet_ids[] = $row['id'];
        }

        // Si hay pallets relacionados, tomar sus `part_id` asociados a registros en `Cajas_scanned`
        if (!empty($pallet_ids)) {
            $pallet_ids_placeholder = implode(',', array_fill(0, count($pallet_ids), '?')); // Para la consulta IN
            $pallet_ids_params = str_repeat('i', count($pallet_ids)); // Tipos de parámetros

            // Obtener todos los part_id desde `Cajas_scanned` asociados a los pallets
            $scanned_parts_query = "SELECT part_id FROM Cajas_scanned WHERE pallet_id IN ($pallet_ids_placeholder)";
            $stmt_scanned_parts = mysqli_prepare($enlace, $scanned_parts_query);

            // Bind dinámico
            mysqli_stmt_bind_param($stmt_scanned_parts, $pallet_ids_params, ...$pallet_ids);
            mysqli_stmt_execute($stmt_scanned_parts);
            $result_scanned_parts = mysqli_stmt_get_result($stmt_scanned_parts);

            // Si existen registros escaneados relacionados, cambiar los estados de los correspondientes en `calidad_cajas_scanned`
            $part_ids = [];
            while ($row = mysqli_fetch_assoc($result_scanned_parts)) {
                $part_ids[] = $row['part_id'];
            }

            if (!empty($part_ids)) {
                $part_ids_placeholder = implode(',', array_fill(0, count($part_ids), '?'));
                $part_ids_params = str_repeat('i', count($part_ids));

                $update_quality_query = "UPDATE calidad_cajas_scanned SET status = 'Entrada' WHERE part_id IN ($part_ids_placeholder)";
                $stmt_update_quality = mysqli_prepare($enlace, $update_quality_query);
                mysqli_stmt_bind_param($stmt_update_quality, $part_ids_params, ...$part_ids);

                if (!mysqli_stmt_execute($stmt_update_quality)) {
                    throw new Exception('Error al actualizar registros de calidad: ' . mysqli_error($enlace));
                }
            }
        }

        // Eliminar todos los pallets asociados al folio
        $delete_pallets = "DELETE FROM Pallets WHERE folio_id = ?";
        $stmt_delete_pallets = mysqli_prepare($enlace, $delete_pallets);
        mysqli_stmt_bind_param($stmt_delete_pallets, 'i', $folio_id);
        if (!mysqli_stmt_execute($stmt_delete_pallets)) {
            throw new Exception('Error al eliminar los pallets: ' . mysqli_error($enlace));
        }

        // Eliminar el folio
        $delete_folio = "DELETE FROM Folios WHERE id = ?";
        $stmt_delete_folio = mysqli_prepare($enlace, $delete_folio);
        mysqli_stmt_bind_param($stmt_delete_folio, 'i', $folio_id);
        if (!mysqli_stmt_execute($stmt_delete_folio)) {
            throw new Exception('Error al eliminar el folio: ' . mysqli_error($enlace));
        }

        // Confirmar transacción
        mysqli_commit($enlace);

        $response['success'] = true;
        $response['message'] = 'Folio, pallets y registros relacionados en "calidad" eliminados exitosamente, y estado actualizado a "Entrada".';
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
