<?php
require '../../backend/db/db.php';

$day_id = isset($_GET['day_id']) ? intval($_GET['day_id']) : 0;

// Obtener la fecha correspondiente al day_id desde la base de datos
$day_query = "SELECT day_date FROM calidad_days WHERE id = $day_id";
$day_result = mysqli_query($enlace, $day_query);
$day_fecha = '';
if ($day_result && mysqli_num_rows($day_result) > 0) {
    $day_row = mysqli_fetch_assoc($day_result);
    $day_fecha = $day_row['day_date'];
}

// Obtener el último número de serie registrado
$last_serial_query = "SELECT serial_number FROM calidad_cajas_scanned WHERE day_id = $day_id ORDER BY scan_timestamp DESC LIMIT 1";
$last_serial_result = mysqli_query($enlace, $last_serial_query);
$last_serial = '';
if ($last_serial_result && mysqli_num_rows($last_serial_result) > 0) {
    $last_serial_row = mysqli_fetch_assoc($last_serial_result);
    $last_serial = $last_serial_row['serial_number'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Ítem</title>
    <link rel="stylesheet" href="./css/add_item_calidad.css">
    <link rel="icon" href="/img/simafa.png" type="image/sima">
</head>
<body>
    <div class="container">
        <h2>Agregar Ítem | CALIDAD</h2>
        <h2>Estas en el Día: <?php echo htmlspecialchars($day_fecha); ?></h2>

        <!-- Mostrar mensaje de éxito o error si está presente -->
        <?php if (isset($_GET['message']) && isset($_GET['messageType'])): ?>
            <div id="notification" class="notification <?php echo htmlspecialchars($_GET['messageType']); ?> show">
                <?php echo htmlspecialchars($_GET['message']); ?>
            </div>
        <?php endif; ?>

        <!-- Mostrar el último número de serie registrado -->
        <?php if ($last_serial): ?>
            <div id="last-serial" class="notification info show">
                Último número de serie registrado: <?php echo htmlspecialchars($last_serial); ?>
            </div>
        <?php endif; ?>

        <!-- Formulario para agregar un nuevo ítem -->
        <form id="addItemForm" name="addItemForm" method="POST" action="back_add_item_calidad.php">
            <input type="hidden" name="day_id" value="<?php echo htmlspecialchars($day_id); ?>">
            <label for="nifco_numero">Número de PARTE:</label>
            <input type="text" id="nifco_numero" name="nifco_numero" required>
            <label for="serial_number">Número de Serie:</label>
            <input type="text" id="serial_number" name="serial_number" required>
            <label for="quantity">Cantidad:</label>
            <input type="number" id="quantity" name="quantity" required>
            <button type="submit" name="add_item">
                <span>Agregar Ítem</span>
            </button>
        </form>
    </div>
    <script src="./js/add_item_calidad.js"></script>
    <script>
        // Enfocar el primer campo de entrada cuando se cargue la página
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('nifco_numero').focus();
        });
    </script>
</body>
</html>