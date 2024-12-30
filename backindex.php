<?php

// Incluir el archivo de conexión a la base de datos
require 'backend/db/db.php';

// Inicializar variables para los mensajes
$message = '';
$messageType = '';

// Constantes para estados y mensajes
define('STATUS_ARCHIVADO', 'archivado');
define('STATUS_PRODUCCION', 'produccion');

/**
 * Función para archivar un día.
 *
 * @param mysqli $enlace Enlace a la base de datos
 * @param string $day_date Fecha del día a archivar
 * @return bool
 */
function archiveDay($enlace, $day_date) {
    $query = "UPDATE Days SET status = ? WHERE day_date = ?";
    $stmt = $enlace->prepare($query);
    $status = STATUS_ARCHIVADO;

    $stmt->bind_param('ss', $status, $day_date);
    return $stmt->execute();
}

// Crear día
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_day'])) {
        $day_date = $enlace->real_escape_string($_POST['day_date']);
        $query = "SELECT 1 FROM Days WHERE day_date = ?";
        $stmt = $enlace->prepare($query);
        $stmt->bind_param('s', $day_date);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $message = "Ya existe un registro para esta fecha: $day_date";
            $messageType = 'error';
        } else {
            $insertQuery = "INSERT INTO Days (day_date, status) VALUES (?, ?)";
            $stmtInsert = $enlace->prepare($insertQuery);
            $status = STATUS_PRODUCCION;

            $stmtInsert->bind_param('ss', $day_date, $status);
            if ($stmtInsert->execute()) {
                $message = "Día creado exitosamente: $day_date";
                $messageType = 'success';
            } else {
                $message = "Error al crear el día: " . $stmtInsert->error;
                $messageType = 'error';
            }
        }
    }

    // Crear folio
    if (isset($_POST['create_folio'])) {
        $folio_date = $enlace->real_escape_string($_POST['folio_date']);
        $query = "SELECT id FROM Days WHERE day_date = ?";
        $stmt = $enlace->prepare($query);
        $stmt->bind_param('s', $folio_date);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $day_id = $row['id'];

            $folioQuery = "SELECT COALESCE(MAX(CAST(SUBSTRING(folio_number, 7) AS UNSIGNED)), 0) + 1 AS next_folio 
                           FROM Folios WHERE folio_number LIKE 'SIMA-%'";
            $folioResult = $enlace->query($folioQuery);
            $next_folio = $folioResult->fetch_assoc()['next_folio'];
            $folio_number = 'SIMA-' . str_pad($next_folio, 6, '0', STR_PAD_LEFT);

            $insertFolioQuery = "INSERT INTO Folios (folio_number, day_id, departure_date) VALUES (?, ?, ?)";
            $stmtFolio = $enlace->prepare($insertFolioQuery);
            $stmtFolio->bind_param('sis', $folio_number, $day_id, $folio_date);

            if ($stmtFolio->execute()) {
                $message = "Folio creado exitosamente: $folio_number";
                $messageType = 'success';
            } else {
                $message = "Error al crear el folio: " . $stmtFolio->error;
                $messageType = 'error';
            }
        } else {
            $message = "No se encontró el día para crear el folio.";
            $messageType = 'error';
        }
    }

    // Crear pallet
    if (isset($_POST['create_pallet'])) {
        $pallet_date = $enlace->real_escape_string($_POST['pallet_date']);
        $folio_id = $enlace->real_escape_string($_POST['folio_id']);

        $countPalletsQuery = "SELECT COUNT(*) as pallet_count FROM Pallets WHERE folio_id = ?";
        $stmt = $enlace->prepare($countPalletsQuery);
        $stmt->bind_param('i', $folio_id);
        $stmt->execute();
        $countResult = $stmt->get_result();
        $pallet_count = $countResult->fetch_assoc()['pallet_count'];

        $pallet_number = 'Pallet ' . ($pallet_count + 1);

        $insertPalletQuery = "INSERT INTO Pallets (folio_id, pallet_number) VALUES (?, ?)";
        $stmtInsert = $enlace->prepare($insertPalletQuery);
        $stmtInsert->bind_param('is', $folio_id, $pallet_number);

        if ($stmtInsert->execute()) {
            $updateFolioQuery = "UPDATE Folios SET total_pallets = total_pallets + 1 WHERE id = ?";
            $stmtUpdate = $enlace->prepare($updateFolioQuery);
            $stmtUpdate->bind_param('i', $folio_id);
            $stmtUpdate->execute();

            $message = "Pallet creado exitosamente: $pallet_number";
            $messageType = 'success';
        } else {
            $message = "Error al crear el pallet: " . $stmtInsert->error;
            $messageType = 'error';
        }
    }

    // Archivar día manualmente
    if (isset($_POST['archive_day'])) {
        $day_date = $enlace->real_escape_string($_POST['archive_day']);
        if (archiveDay($enlace, $day_date)) {
            $message = "Día archivado exitosamente: $day_date";
            $messageType = 'success';
        } else {
            $message = "Error al archivar el día.";
            $messageType = 'error';
        }
    }

    // Redirigir con mensaje
    header("Location: index.php?message=" . urlencode($message) . "&messageType=" . urlencode($messageType));
    exit;
}

// Obtener fechas únicas de la tabla Days
$dateQuery = "SELECT DISTINCT day_date as pallet_date FROM Days ORDER BY day_date DESC";
$dateResult = $enlace->query($dateQuery);
?>
