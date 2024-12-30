<?php
require '../../backend/db/db.php';

$response = ['success' => false, 'message' => '', 'debug' => ''];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $day_id = $_POST['day_id'];
    $numero_parte = $_POST['nifco_numero'];
    $serial_number = $_POST['serial_number'];
    $quantity = $_POST['quantity'];
    $status = 'Entrada'; // Establecer el estado automáticamente a "Entrada"

    // Verificar si el número de parte existe en la tabla Modelos
    $checkQuery = "SELECT id, nifco_numero FROM Modelos WHERE numero_parte = '$numero_parte'";
    $checkResult = mysqli_query($enlace, $checkQuery);

    if (mysqli_num_rows($checkResult) > 0) {
        $row = mysqli_fetch_assoc($checkResult);
        $part_id = $row['id'];
        $nifco_numero = $row['nifco_numero'];

        // Verificar si el número de serie ya existe
        $serialCheckQuery = "SELECT id FROM calidad_cajas_scanned WHERE serial_number = '$serial_number'";
        $serialCheckResult = mysqli_query($enlace, $serialCheckQuery);

        if (mysqli_num_rows($serialCheckResult) == 0) {
            // Insertar el nuevo ítem en la base de datos
            $insertQuery = "INSERT INTO calidad_cajas_scanned (part_id, serial_number, quantity, scan_timestamp, status) 
                            VALUES ('$part_id', '$serial_number', '$quantity', NOW(), '$status')";
            if (mysqli_query($enlace, $insertQuery)) {
                // Insertar en el historial
                $insertHistorialQuery = "INSERT INTO historial_calidad (day_id, part_id, serial_number, quantity, scan_timestamp, status) 
                                         VALUES ('$day_id', '$part_id', '$serial_number', '$quantity', NOW(), '$status')";
                mysqli_query($enlace, $insertHistorialQuery);

                $response['success'] = true;
                $response['message'] = "Ítem agregado exitosamente.";
                $response['nifco_numero'] = $nifco_numero;
                $response['debug'] = "Query executed successfully: $insertQuery";
            } else {
                $response['message'] = "Error al agregar el ítem: " . mysqli_error($enlace);
                $response['debug'] = "Query failed: $insertQuery";
            }
        } else {
            $response['message'] = "El número de serie ya existe.";
            $response['debug'] = "Serial number already exists: $serial_number";
        }
    } else {
        $response['message'] = "El número de parte no existe en la base de datos.";
        $response['debug'] = "Part number not found: $numero_parte";
    }
} else {
    $response['message'] = "Método de solicitud no válido.";
    $response['debug'] = "Invalid request method: " . $_SERVER['REQUEST_METHOD'];
}

header('Content-Type: application/json');
echo json_encode($response);
?>  