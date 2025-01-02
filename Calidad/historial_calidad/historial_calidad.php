<?php
require '../../backend/db/db.php';

// Obtener los días archivados ordenados de los más recientes a los más antiguos
$historialQuery = "SELECT * FROM calidad_days WHERE status = 'archivado' ORDER BY day_date DESC";
$historialResult = mysqli_query($enlace, $historialQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial - SimaSolution | CALIDAD</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/index.css">
    <link rel="icon" href="/img/simafa.png" type="image/sima">
</head>
<body>
    <header>
        <a href="../scann.php" class="home-icon"><i class="fas fa-home"></i></a>
    </header>
    <h1>Historial de Días Archivados | CALIDAD</h1>

    <div class="accordion custom-accordion">
        <?php if ($historialResult && mysqli_num_rows($historialResult) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($historialResult)): ?>
                <div class="accordion-item custom-accordion-item">
                    <div class="accordion-header custom-accordion-header">
                        <h3><?php echo date("d/m/Y", strtotime($row['day_date'])); ?></h3>
                        <form action="cambiar_estado.php" method="post" class="inline-form">
                            <input type="hidden" name="day_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" class="btn btn-primary">Mandar a Producción</button>
                        </form>
                    </div>
                    <div class="accordion-body custom-accordion-body" style="display: none;">
                        <!-- Obtener y mostrar los registros de calidad_cajas_scanned para este día -->
                        <?php
                        $day_id = $row['id'];
                        $itemsQuery = "SELECT cs.*, m.numero_parte, m.nifco_numero 
                                       FROM calidad_cajas_scanned cs 
                                       JOIN Modelos m ON cs.part_id = m.id 
                                       WHERE DATE(cs.scan_timestamp) = '" . $row['day_date'] . "'";
                        $itemsResult = mysqli_query($enlace, $itemsQuery);

                        if ($itemsResult && mysqli_num_rows($itemsResult) > 0): ?>
                            <table class="custom-table">
                                <thead>
                                    <tr>
                                        <th class="custom-th">Número de Parte</th>
                                        <th class="custom-th">NIFCO</th>
                                        <th class="custom-th">Serial</th>
                                        <th class="custom-th">Cantidad</th>
                                        <th class="custom-th">Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($item = mysqli_fetch_assoc($itemsResult)): ?>
                                        <tr class="<?php echo $item['status'] == 'Salida' ? 'status-salida' : 'status-entrada'; ?>">
                                            <td class="custom-td"><?php echo htmlspecialchars($item['numero_parte']); ?></td>
                                            <td class="custom-td"><?php echo htmlspecialchars($item['nifco_numero']); ?></td>
                                            <td class="custom-td"><?php echo htmlspecialchars($item['serial_number']); ?></td>
                                            <td class="custom-td"><?php echo htmlspecialchars($item['quantity']); ?></td>
                                            <td class="custom-td"><?php echo htmlspecialchars($item['status']); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p>No hay registros para este día.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No hay días archivados.</p>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var accordionHeaders = document.querySelectorAll('.accordion-header');
            accordionHeaders.forEach(function(header) {
                header.addEventListener('click', function() {
                    var body = header.nextElementSibling;
                    if (body.style.display === 'none' || body.style.display === '') {
                        body.style.display = 'block';
                    } else {
                        body.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>
</html>