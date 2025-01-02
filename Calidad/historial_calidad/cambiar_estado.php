<?php
require '../../backend/db/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['day_id'])) {
        $day_id = intval($_POST['day_id']);

        // Cambiar el estado del día a 'produccion'
        $updateQuery = "UPDATE calidad_days SET status = 'produccion' WHERE id = $day_id";
        if (mysqli_query($enlace, $updateQuery)) {
            $message = "Día cambiado a producción exitosamente.";
            $messageType = "success";
        } else {
            $message = "Error al cambiar el estado del día: " . mysqli_error($enlace);
            $messageType = "error";
        }

        // Redirigir a scann.php con mensaje
        header("Location: ../scann.php?message=" . urlencode($message) . "&messageType=" . urlencode($messageType));
        exit();
    }
}
?>