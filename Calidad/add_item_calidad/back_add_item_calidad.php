<?php
// Activar reporte de errores para depuración
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Verificar la conexión a la base de datos
require '../../backend/db/db.php';

if (!$enlace) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Debug: Ver qué datos están llegando
echo "Datos POST recibidos:\n";
var_dump($_POST);

$response = [
    'success' => false,
    'message' => '',
    'debug' => ''
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Debug: Imprimir los valores que estamos recibiendo
        echo "\nValores recibidos:\n";
        echo "day_id: " . $_POST['day_id'] . "\n";
        echo "nifco_numero: " . $_POST['nifco_numero'] . "\n";
        echo "serial_number: " . $_POST['serial_number'] . "\n";
        echo "quantity: " . $_POST['quantity'] . "\n";

        // Validar y sanitizar las entradas
        $day_id = filter_var($_POST['day_id'], FILTER_VALIDATE_INT);
        $numero_parte = mysqli_real_escape_string($enlace, $_POST['nifco_numero']);
        $serial_number = mysqli_real_escape_string($enlace, $_POST['serial_number']);
        $quantity = filter_var($_POST['quantity'], FILTER_VALIDATE_INT);
        $status = 'Entrada';

        // Debug: Imprimir los valores después de la sanitización
        echo "\nValores sanitizados:\n";
        echo "day_id: $day_id\n";
        echo "numero_parte: $numero_parte\n";
        echo "serial_number: $serial_number\n";
        echo "quantity: $quantity\n";

        // Obtener la fecha del día seleccionado
        $dayQuery = "SELECT day_date FROM calidad_days WHERE id = '$day_id' AND status = 'produccion'";
        $dayResult = mysqli_query($enlace, $dayQuery);

        if (!$dayResult) {
            throw new Exception("Error en la consulta: " . mysqli_error($enlace));
        }

        if ($dayRow = mysqli_fetch_assoc($dayResult)) {
            $selectedDate = $dayRow['day_date'];
            echo "\nFecha seleccionada: $selectedDate\n";

            // Obtener el ID del número de parte
            $partQuery = "SELECT id, nifco_numero FROM Modelos WHERE numero_parte = '$numero_parte'";
            $partResult = mysqli_query($enlace, $partQuery);

            if (!$partResult) {
                throw new Exception("Error al buscar número de parte: " . mysqli_error($enlace));
            }

            if ($row = mysqli_fetch_assoc($partResult)) {
                $part_id = $row['id'];
                $nifco_numero = $row['nifco_numero'];

                // Verificar duplicados en toda la tabla
                $checkQuery = "SELECT id FROM calidad_cajas_scanned WHERE serial_number = '$serial_number'";
                $checkResult = mysqli_query($enlace, $checkQuery);

                if (!$checkResult) {
                    throw new Exception("Error al verificar duplicados: " . mysqli_error($enlace));
                }

                if (mysqli_num_rows($checkResult) == 0) {
                    // Insertar el nuevo registro
                    $insertQuery = "INSERT INTO calidad_cajas_scanned 
                                  (part_id, serial_number, quantity, scan_timestamp, status, day_id)
                                  VALUES 
                                  ($part_id, '$serial_number', $quantity, '$selectedDate', '$status', $day_id)";

                    if (!mysqli_query($enlace, $insertQuery)) {
                        throw new Exception("Error al insertar: " . mysqli_error($enlace));
                    }

                    $response['success'] = true;
                    $response['message'] = "Ítem agregado exitosamente";
                    $response['nifco_numero'] = $nifco_numero;
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
        echo "\nError: " . $e->getMessage() . "\n";
    }
} else {
    $response['message'] = "Método no válido";
}

// Asegurar que no haya más output antes del JSON
ob_clean();
header('Content-Type: application/json');
echo json_encode($response);
?>