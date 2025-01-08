<?php
session_start();
ob_start();
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'backend/db/db.php';
$response = ['success' => false, 'message' => ''];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $serial_number = strtoupper(mysqli_real_escape_string($enlace, $_POST['serial_number']));
        $pallet_id = mysqli_real_escape_string($enlace, $_POST['pallet_id']);
        $folio_id = mysqli_real_escape_string($enlace, $_POST['folio_id']);

        // Limpiar el valor del número de serie
        $serial_number = preg_replace('/^1S/', '', $serial_number);
        $serial_number = preg_replace('/[\s-]/', '', $serial_number);

        // Verificar si el número de serie existe en calidad_cajas_scanned y tiene estado 'Entrada'
        $check_serial_query = "SELECT * FROM calidad_cajas_scanned WHERE serial_number = '$serial_number' AND status = 'Entrada'";
        $check_serial_result = mysqli_query($enlace, $check_serial_query);

        if (!$check_serial_result) {
            throw new Exception("Error en la consulta SQL: " . mysqli_error($enlace));
        }

        if (mysqli_num_rows($check_serial_result) > 0) {
            $row = mysqli_fetch_assoc($check_serial_result);
            $quantity = $row['quantity'];
            $part_id = $row['part_id'];

            // Verificar si el número de parte es válido
            $check_part_query = "SELECT * FROM Modelos WHERE id = '$part_id'";
            $check_part_result = mysqli_query($enlace, $check_part_query);

            if (!$check_part_result || mysqli_num_rows($check_part_result) == 0) {
                throw new Exception("Número de parte no válido. Reinicie para continuar.");
            }

            // Actualizar el estado del registro en calidad_cajas_scanned a 'Salida'
            $update_status_query = "UPDATE calidad_cajas_scanned SET status = 'Salida' WHERE serial_number = '$serial_number'";
            if (!mysqli_query($enlace, $update_status_query)) {
                throw new Exception("Error al actualizar el estado del registro: " . mysqli_error($enlace));
            }

            // Insertar el registro en la tabla Cajas_scanned
            $insert_query = "INSERT INTO Cajas_scanned (serial_number, pallet_id, part_id, quantity) VALUES ('$serial_number', '$pallet_id', '$part_id', '$quantity')";
            if (!mysqli_query($enlace, $insert_query)) {
                throw new Exception("Error al agregar el registro: " . mysqli_error($enlace));
            }

            $response['success'] = true;
            $response['message'] = "Registro agregado exitosamente.";
        } else {
            throw new Exception("El número de serie no existe en la tabla de calidad o ya ha sido marcado como 'Salida'.");
        }
    } else {
        throw new Exception("Método de solicitud no válido.");
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log($e->getMessage());
} finally {
    ob_end_clean();
    echo json_encode($response);
    exit;
}
?>