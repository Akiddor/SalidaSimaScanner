<?php
require '../backend/db/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fecha = $_POST['fecha'];
    $nifcos = $_POST['nifco_numero'];
    $piezas = $_POST['piezas'];
    $nifcosAgregados = [];

    foreach ($nifcos as $index => $nifco_numero) {
        $piezas_count = $piezas[$index];

        // Verificar si ya existe un registro para el mismo NIFCO y fecha
        if (in_array($nifco_numero, $nifcosAgregados)) {
            $message = "El número de NIFCO '$nifco_numero' ya ha sido agregado en este formulario.";
            $messageType = "error";
            break;
        }

        $checkQuery = "SELECT * FROM PlanTrabajo WHERE nifco_numero = '$nifco_numero' AND fecha = '$fecha'";
        $checkResult = mysqli_query($enlace, $checkQuery);

        if (mysqli_num_rows($checkResult) > 0) {
            $message = "El número de NIFCO '$nifco_numero' ya tiene un plan de trabajo para la fecha '$fecha'.";
            $messageType = "error";
            break;
        } else {
            $query = "INSERT INTO PlanTrabajo (nifco_numero, fecha, piezas) VALUES ('$nifco_numero', '$fecha', '$piezas_count')";
            if (mysqli_query($enlace, $query)) {
                $message = "Plan de trabajo agregado exitosamente.";
                $messageType = "success";
                $nifcosAgregados[] = $nifco_numero;
            } else {
                $message = "Error al agregar el plan de trabajo: " . mysqli_error($enlace);
                $messageType = "error";
                break;
            }
        }
    }
}

// Manejar la eliminación de registros
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $deleteQuery = "DELETE FROM PlanTrabajo WHERE id = $delete_id";
    if (mysqli_query($enlace, $deleteQuery)) {
        $message = "Plan de trabajo eliminado exitosamente.";
        $messageType = "success";
    } else {
        $message = "Error al eliminar el plan de trabajo: " . mysqli_error($enlace);
        $messageType = "error";
    }
}

// Obtener los planes de trabajo existentes
$planesQuery = "SELECT * FROM PlanTrabajo ORDER BY fecha DESC";
$planesResult = mysqli_query($enlace, $planesQuery);

// Agrupar planes de trabajo por fecha
$planesPorFecha = [];
if (mysqli_num_rows($planesResult) > 0) {
    while ($plan = mysqli_fetch_assoc($planesResult)) {
        $fecha = $plan['fecha'];
        if (!isset($planesPorFecha[$fecha])) {
            $planesPorFecha[$fecha] = [];
        }

        // Obtener la cantidad de piezas registradas para el mismo NIFCO y fecha
        $nifco = $plan['nifco_numero'];
        $registroQuery = "SELECT SUM(quantity) as total_piezas FROM Cajas_scanned cs JOIN Modelos m ON cs.part_id = m.id WHERE m.nifco_numero = '$nifco' AND DATE(cs.scan_timestamp) = '$fecha'";
        $registroResult = mysqli_query($enlace, $registroQuery);
        $registro = mysqli_fetch_assoc($registroResult);
        $piezasRegistradas = $registro['total_piezas'] ?? 0;

        // Calcular la diferencia
        $diferencia = $plan['piezas'] - $piezasRegistradas;

        $plan['piezas_registradas'] = $piezasRegistradas;
        $plan['diferencia'] = $diferencia;

        $planesPorFecha[$fecha][] = $plan;
    }
}
?>