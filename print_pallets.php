<?php
require 'backend/db/db.php';

$pallet_ids = isset($_GET['pallets']) ? explode(',', $_GET['pallets']) : [];
$folio = isset($_GET['folio']) ? $_GET['folio'] : '';

if (empty($pallet_ids)) {
    die('No se seleccionaron pallets para imprimir.');
}

// Obtener el ID del folio
$folio_query = "SELECT id FROM Folios WHERE folio_number = '$folio'";
$folio_result = mysqli_query($enlace, $folio_query);
if ($folio_result && mysqli_num_rows($folio_result) > 0) {
    $folio_row = mysqli_fetch_assoc($folio_result);
    $folio_id = $folio_row['id'];

    // Insertar los pallets en la tabla folios_impresos
    foreach ($pallet_ids as $pallet_id) {
        $insert_query = "INSERT INTO folios_impresos (pallet_id, folio_id) VALUES ($pallet_id, $folio_id)";
        mysqli_query($enlace, $insert_query);
    }
} else {
    echo "<script>
          alert('El número de folio ingresado no existe.');
          window.location.href = 'index.php';
          </script>";
    exit;
}

// Inicializar la variable $pallets como un array vacío
$pallets = [];

// Obtener los detalles de los pallets seleccionados
foreach ($pallet_ids as $pallet_id) {
    $query = "SELECT cs.*, m.numero_parte, m.nifco_numero FROM Cajas_scanned cs JOIN Modelos m ON cs.part_id = m.id WHERE cs.pallet_id = $pallet_id";
    $result = mysqli_query($enlace, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        $pallets[$pallet_id][] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Packing Slip</title>
    <link rel="stylesheet" href="css/print_pallet.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="left">
                <img src="/img/Simaaa.png" alt="Logo">
                <div class="separator"></div>
                <div class="company-info">
                    <p>Servicios Para La Industria Maquiladora</p>
                    <p>Calle José Gutiérrez #407</p>
                    <p>Col. Deportistas C.P. 31124</p>
                    <p>Chihuahua, Chihuahua México</p>
                </div>
            </div>
            <div class="right">
                <p><strong>Folio:</strong> <?php echo htmlspecialchars($folio); ?></p>
            </div>
        </header>

        <section class="details">
            <div class="bill-to">
                <p><strong>Bill To:</strong></p>
                <p>NIFCO</p>
                <p>Nicolás Gogol #11301</p>
                <p>Complejo Industrial</p>
                <p>CP 31109 Chihuahua, Chih. México</p>
            </div>
            <div class="ship-to">
                <p><strong>Ship To:</strong></p>
                <p>NIFCO</p>
                <p>Nicolás Gogol #11301</p>
                <p>Complejo Industrial</p>
                <p>CP 31109 Chihuahua, Chih. México</p>
            </div>
        </section>

        <section class="table-container">
            <?php $pallet_counter = 1; ?>
            <?php foreach ($pallets as $pallet_id => $items): ?>
                <h2>Pallet <?php echo $pallet_counter++; ?></h2>
                <table>
                    <thead>
                        <tr>
                            <th>Part Number</th>
                            <th>Nifco</th>
                            <th>Serial</th>
                            <th>Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $pallet): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($pallet['numero_parte']); ?></td>
                                <td><?php echo htmlspecialchars($pallet['nifco_numero'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($pallet['serial_number']); ?></td>
                                <td><?php echo htmlspecialchars($pallet['quantity']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endforeach; ?>     
        </section>

        <div class="signatures">
            <div class="signature">
                <div class="line"></div>
                <p>Entregó:</p>
            </div>
            <div class="signature">
                <div class="line"></div>
                <p>Recibió:</p>
            </div>
        </div>

        <div class="no-print">
            <button onclick="window.print()">Imprimir</button>
        </div>
    </div>
</body>

</html>