<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Item</title>
    <link rel="stylesheet" href="/css/add_item.css">
</head>
<body>
    <div class="container">
        <h2>Agregar Item</h2>
        <form id="addItemForm" method="POST" action="add_item.php">
            <input type="hidden" name="pallet_id" value="<?php echo htmlspecialchars($_GET['pallet_id']); ?>">
            <input type="hidden" name="folio_id" value="<?php echo htmlspecialchars($_GET['folio_id']); ?>">

            <label for="serial_number">Serial:</label>
            <input type="text" id="serial_number" name="serial_number" required autofocus>

            <button type="submit" name="registro">Registrar</button>
        </form>
        <div id="message" class="message"></div>
    </div>

    <script src="add_item_form.js"></script>
</body>
</html>
