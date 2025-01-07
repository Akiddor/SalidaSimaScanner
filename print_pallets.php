<?php
require 'backend/db/db.php';

$pallet_ids = isset($_GET['pallets']) ? explode(',', $_GET['pallets']) : [];
$folio = isset($_GET['folio']) ? $_GET['folio'] : '';

if (empty($pallet_ids)) {
    die('No se seleccionaron pallets para imprimir.');
}

// Inicializar la variable $pallets como un array vacío
$pallets = [];
$total_quantity = 0; // Inicializar la variable para la sumatoria
$nifcoCounts = []; // Inicializar la variable para el conteo de NIFCO

// Obtener los detalles de los pallets seleccionados
foreach ($pallet_ids as $pallet_id) {
    $query = "SELECT cs.*, m.numero_parte, m.nifco_numero FROM Cajas_scanned cs JOIN Modelos m ON cs.part_id = m.id WHERE cs.pallet_id = $pallet_id";
    $result = mysqli_query($enlace, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        $pallets[$pallet_id][] = $row;
        $total_quantity += $row['quantity']; // Sumar la cantidad al total

        // Contar NIFCO
        $nifco_numero = $row['nifco_numero'];
        if (!isset($nifcoCounts[$nifco_numero])) {
            $nifcoCounts[$nifco_numero] = ['count' => 0, 'quantity' => 0];
        }
        $nifcoCounts[$nifco_numero]['count']++;
        $nifcoCounts[$nifco_numero]['quantity'] += $row['quantity'];
    }
}

// Obtener la fecha actual en el formato solicitado
$meses = [
    1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
    5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
    9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
];
$fecha_actual = date('j') . '/' . $meses[intval(date('n'))] . '/' . date('Y');
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
                <p><strong>Fecha:</strong> <?php echo $fecha_actual; ?></p>
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
                        <?php 
                        foreach ($items as $pallet): 
                        ?>
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

        <!-- Resumen de NIFCO para cada pallet -->
        <section class="nifco-summary">
            <?php $pallet_counter = 1; ?>
            <?php foreach ($pallets as $pallet_id => $items): ?>
                <h5>Resumen de Pallet <?php echo $pallet_counter++; ?></h5>
                <table>
                    <thead>
                        <tr>
                            <th>NIFCO</th>
                            <th>Quantity</th>
                            <th>Boxes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $palletNifcoCounts = [];
                        $totalBoxes = 0;
                        foreach ($items as $pallet) {
                            $nifco_numero = $pallet['nifco_numero'];
                            if (!isset($palletNifcoCounts[$nifco_numero])) {
                                $palletNifcoCounts[$nifco_numero] = ['count' => 0, 'quantity' => 0];
                            }
                            $palletNifcoCounts[$nifco_numero]['count']++;
                            $palletNifcoCounts[$nifco_numero]['quantity'] += $pallet['quantity'];
                            $totalBoxes++;
                        }
                        ?>
                        <?php foreach ($palletNifcoCounts as $nifco => $count): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($nifco); ?></td>
                                <td><?php echo number_format($count['quantity']); ?></td>
                                <td><?php echo $count['count']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td><strong>Total</strong></td>
                            <td><strong><?php echo number_format(array_sum(array_column($palletNifcoCounts, 'quantity'))); ?></strong></td>
                            <td><strong><?php echo $totalBoxes; ?></strong></td>
                        </tr>
                    </tbody>
                </table>
            <?php endforeach; ?>     
        </section>

        <!-- Resumen total de NIFCO y total de cantidades -->
        <div class="nifco-summary">
            <h5>Resumen Total de Packing</h5>
            <table>
                <thead>
                    <tr>
                        <th>NIFCO</th>
                        <th>Quantity</th>
                        <th>Boxes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $totalBoxes = 0; ?>
                    <?php foreach ($nifcoCounts as $nifco => $count): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($nifco); ?></td>
                            <td><?php echo number_format($count['quantity']); ?></td>
                            <td><?php echo $count['count']; ?></td>
                        </tr>
                        <?php $totalBoxes += $count['count']; ?>
                    <?php endforeach; ?>
                    <tr>
                        <td><strong>Total</strong></td>
                        <td><strong><?php echo number_format(array_sum(array_column($nifcoCounts, 'quantity'))); ?></strong></td>
                        <td><strong><?php echo $totalBoxes; ?></strong></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <section class="total-quantity">
            <h2>Total Quantity: <?php echo number_format($total_quantity); ?></h2>
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