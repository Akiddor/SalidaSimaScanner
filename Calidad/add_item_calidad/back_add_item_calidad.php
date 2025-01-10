<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require '../../backend/db/db.php';

$response = [
    'success' => false,
    'message' => '',
    'nifco_numero' => '',
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $day_id = filter_var($_POST['day_id'], FILTER_VALIDATE_INT);
        $numero_parte = mysqli_real_escape_string($enlace, $_POST['nifco_numero']);
        $serial_number = mysqli_real_escape_string($enlace, $_POST['serial_number']);
        $quantity = filter_var($_POST['quantity'], FILTER_VALIDATE_INT);
        $status = 'Entrada';

        $dayQuery = "SELECT day_date FROM calidad_days WHERE id = '$day_id' AND status = 'produccion'";
        $dayResult = mysqli_query($enlace, $dayQuery);

        if ($dayRow = mysqli_fetch_assoc($dayResult)) {
            $selectedDate = $dayRow['day_date'];

            $partQuery = "SELECT id, nifco_numero FROM Modelos WHERE numero_parte = '$numero_parte'";
            $partResult = mysqli_query($enlace, $partQuery);

            if ($row = mysqli_fetch_assoc($partResult)) {
                $part_id = $row['id'];
                $nifco_numero = $row['nifco_numero'];

                $checkQuery = "SELECT id FROM calidad_cajas_scanned WHERE serial_number = '$serial_number'";
                $checkResult = mysqli_query($enlace, $checkQuery);

                if (mysqli_num_rows($checkResult) == 0) {
                    $insertQuery = "INSERT INTO calidad_cajas_scanned 
                                  (part_id, serial_number, quantity, scan_timestamp, status, day_id)
                                  VALUES 
                                  ($part_id, '$serial_number', $quantity, '$selectedDate', '$status', $day_id)";

                    if (mysqli_query($enlace, $insertQuery)) {
                        $response['success'] = true;
                        $response['message'] = "Ítem agregado exitosamente";
                        $response['nifco_numero'] = $nifco_numero;
                    } else {
                        throw new Exception("Error al insertar: " . mysqli_error($enlace));
                    }
                } else {
                    throw new Exception("El número de serie ya existe");
                }
            } else {
                throw new Exception("Número de parte no encontrado");
            }
        } else {
            throw new Exception("Día no encontrado o no está en producción");
        }
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
} else {
    $response['message'] = "Método no válido";
}

header('Content-Type: application/json');
echo json_encode($response);
?>
