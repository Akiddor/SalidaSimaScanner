<?php
require 'backend/db/db.php';

$response = ['success' => false, 'message' => ''];

if (isset($_GET['id'])) {
    $item_id = intval($_GET['id']);

    // Obtener el serial_number del registro que se va a eliminar
    $getSerialQuery = "SELECT serial_number FROM Cajas_scanned WHERE id = $item_id";
    $serialResult = mysqli_query($enlace, $getSerialQuery);
    if ($serialResult && mysqli_num_rows($serialResult) > 0) {
        $serialRow = mysqli_fetch_assoc($serialResult);
        $serial_number = $serialRow['serial_number'];

        // Cambiar el estado del registro en calidad_cajas_scanned a 'Entrada'
        $updateStatusQuery = "UPDATE calidad_cajas_scanned SET status = 'Entrada' WHERE serial_number = '$serial_number'";
        if (mysqli_query($enlace, $updateStatusQuery)) {
            // Eliminar el registro de Cajas_scanned
            $deleteQuery = "DELETE FROM Cajas_scanned WHERE id = $item_id";
            if (mysqli_query($enlace, $deleteQuery)) {
                $response['success'] = true;
                $response['message'] = "Registro eliminado y estado actualizado exitosamente.";
            } else {
                $response['message'] = "Error al eliminar el registro: " . mysqli_error($enlace);
            }
        } else {
            $response['message'] = "Error al actualizar el estado del registro: " . mysqli_error($enlace);
        }
    } else {
        $response['message'] = "Registro no encontrado.";
    }
} else {
    $response['message'] = "ID de item no proporcionado.";
}

header('Content-Type: application/json');
echo json_encode($response);
?>