<?php
require 'backend/db/db.php';

$response = ['success' => false, 'message' => ''];

if (isset($_GET['id'])) {
    $pallet_id = $_GET['id'];
    $deleteQuery = "DELETE FROM Pallets WHERE id = $pallet_id";
    if (mysqli_query($enlace, $deleteQuery)) {
        $response['success'] = true;
        $response['message'] = 'Pallet eliminado exitosamente.';
    } else {
        $response['message'] = 'Error al eliminar el pallet: ' . mysqli_error($enlace);
    }
} else {
    $response['message'] = 'ID de pallet no proporcionado.';
}

header('Content-Type: application/json');
echo json_encode($response);
?>