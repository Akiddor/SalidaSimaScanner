<?php
// Incluir archivo de conexión a la base de datos
require '../backend/db/db.php';

// Función para obtener los planes de trabajo por fecha
function obtenerPlanesPorFecha($conexion) {
    $query = "SELECT pt.*, 
                     COALESCE(SUM(cs.quantity), 0) as piezas_registradas,
                     cs.status
              FROM PlanTrabajo pt
              LEFT JOIN Modelos m ON pt.nifco_numero = m.nifco_numero
              LEFT JOIN calidad_cajas_scanned cs ON m.id = cs.part_id
              GROUP BY pt.id, pt.fecha, pt.nifco_numero, cs.status
              ORDER BY pt.fecha DESC, pt.nifco_numero";
   
    $resultado = $conexion->query($query);
   
    $planesPorFecha = array();
    while ($row = $resultado->fetch_assoc()) {
        $fecha = date('Y-m-d', strtotime($row['fecha']));
        $planesPorFecha[$fecha][] = $row;
    }
   
    return $planesPorFecha;
}

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fecha = $_POST['fecha'];
    $nifcos = $_POST['nifco_numero'];
    $piezas = $_POST['piezas'];
   
    $exito = true;
    $mensaje = '';
    $errores = [];
    $nifcosInsertados = 0;
   
    // Iniciar transacción
    $enlace->begin_transaction();
   
    try {
        // Recorrer cada NIFCO y sus piezas
        for ($i = 0; $i < count($nifcos); $i++) {
            $nifco = $enlace->real_escape_string($nifcos[$i]);
            $cantidad = intval($piezas[$i]);
           
            try {
                // Verificar si el NIFCO existe en la tabla Modelos
                $checkNifcoQuery = "SELECT id FROM Modelos WHERE nifco_numero = ?";
                $stmt = $enlace->prepare($checkNifcoQuery);
                $stmt->bind_param("s", $nifco);
                $stmt->execute();
                $resultadoNifco = $stmt->get_result();
               
                if ($resultadoNifco->num_rows == 0) {
                    throw new Exception("El NIFCO $nifco no existe en la tabla Modelos");
                }
               
                // Verificar si ya existe un plan para este NIFCO en esta fecha
                $checkQuery = "SELECT id FROM PlanTrabajo WHERE fecha = ? AND nifco_numero = ?";
                $stmt = $enlace->prepare($checkQuery);
                $stmt->bind_param("ss", $fecha, $nifco);
                $stmt->execute();
                $resultado = $stmt->get_result();
               
                if ($resultado->num_rows > 0) {
                    throw new Exception("Ya existe un plan de trabajo para el NIFCO $nifco en la fecha $fecha");
                }
               
                // Insertar nuevo plan de trabajo
                $insertQuery = "INSERT INTO PlanTrabajo (fecha, nifco_numero, piezas) VALUES (?, ?, ?)";
                $stmt = $enlace->prepare($insertQuery);
                $stmt->bind_param("ssi", $fecha, $nifco, $cantidad);
               
                if (!$stmt->execute()) {
                    throw new Exception("Error al guardar el plan de trabajo para el NIFCO $nifco");
                }
                $nifcosInsertados++;
            } catch (Exception $e) {
                $errores[] = $e->getMessage();
            }
        }
       
        // Confirmar transacción
        $enlace->commit();
        if ($nifcosInsertados > 0) {
            $mensaje = "Plan de trabajo guardado correctamente.";
            $tipoMensaje = "success";
        } else {
            $mensaje = "No se pudo guardar ningún plan de trabajo";
            $tipoMensaje = "error";
        }
       
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $enlace->rollback();
        $mensaje = $e->getMessage();
        $tipoMensaje = "error";
        $exito = false;
    }
   
    // Agregar errores al mensaje si existen
    if (!empty($errores)) {
        if ($nifcosInsertados > 0) {
            $mensaje .= " Algunos NIFCOs no se pudieron guardar. Errores: " . implode(", ", $errores);
            $tipoMensaje = "warning";
        } else {
            $mensaje .= " Errores: " . implode(", ", $errores);
        }
    }
   
    // Redirigir con mensaje
    header("Location: add_plan_trabajo.php?message=" . urlencode($mensaje) . "&messageType=" . $tipoMensaje);
    exit();
}

// Manejar eliminación de plan de trabajo
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
   
    $deleteQuery = "DELETE FROM PlanTrabajo WHERE id = ?";
    $stmt = $enlace->prepare($deleteQuery);
    $stmt->bind_param("i", $id);
   
    if ($stmt->execute()) {
        header("Location: add_plan_trabajo.php?message=Plan eliminado correctamente&messageType=success");
    } else {
        header("Location: add_plan_trabajo.php?message=Error al eliminar el plan&messageType=error");
    }
    exit();
}

// Obtener todos los planes de trabajo para mostrar en la página
$planesPorFecha = obtenerPlanesPorFecha($enlace);
?>