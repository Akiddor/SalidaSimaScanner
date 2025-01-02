<?php
require '../backend/db/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['create_day'])) {
        // Crear un nuevo día
        $day_date = $_POST['day_date'];

        // Verificar si la fecha ya existe
        $checkDayQuery = "SELECT * FROM calidad_days WHERE day_date = '$day_date'";
        $checkDayResult = mysqli_query($enlace, $checkDayQuery);

        if (mysqli_num_rows($checkDayResult) > 0) {
            $message = "La fecha ya existe.";
            $messageType = "error";
        } else {
            $insertDayQuery = "INSERT INTO calidad_days (day_date) VALUES ('$day_date')";
            if (mysqli_query($enlace, $insertDayQuery)) {
                $message = "Día creado exitosamente.";
                $messageType = "success";
            } else {
                $message = "Error al crear el día: " . mysqli_error($enlace);
                $messageType = "error";
            }
        }
    } elseif (isset($_POST['archive_day'])) {
        // Archivar un día
        $archive_day = $_POST['archive_day'];
        $archiveQuery = "UPDATE calidad_days SET status = 'archivado' WHERE day_date = '$archive_day'";
        if (mysqli_query($enlace, $archiveQuery)) {
            $message = "Día archivado exitosamente.";
            $messageType = "success";
        } else {
            $message = "Error al archivar el día: " . mysqli_error($enlace);
            $messageType = "error";
        }
    } elseif (isset($_POST['delete_id'])) {
        // Eliminar un registro
        $delete_id = $_POST['delete_id'];
        $deleteQuery = "DELETE FROM PlanTrabajo WHERE id = $delete_id";
        if (mysqli_query($enlace, $deleteQuery)) {
            $message = "Plan de trabajo eliminado exitosamente.";
            $messageType = "success";
        } else {
            $message = "Error al eliminar el plan de trabajo: " . mysqli_error($enlace);
            $messageType = "error";
        }
    }

    // Redirigir con mensaje
    header("Location: scann.php?message=" . urlencode($message) . "&messageType=" . urlencode($messageType));
    exit();
}
?>