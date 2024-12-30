<?php
require '../../backend/db/db.php';

$day_id = isset($_GET['day_id']) ? intval($_GET['day_id']) : 0;
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

        <!-- Mostrar mensaje de éxito o error si está presente -->
        <?php if (isset($_GET['message']) && isset($_GET['messageType'])): ?>
            <div id="notification" class="notification <?php echo htmlspecialchars($_GET['messageType']); ?> show">
                <?php echo htmlspecialchars($_GET['message']); ?>
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
</body>
</html>