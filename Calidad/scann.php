<?php
require '../backend/db/db.php';

// Obtener las fechas de los días en producción ordenadas de los más recientes a los más antiguos
$dateQuery = "SELECT id, day_date FROM calidad_days WHERE status = 'produccion' ORDER BY day_date DESC";
$dateResult = mysqli_query($enlace, $dateQuery);

// Contar el número de días o acordeones
$numDays = mysqli_num_rows($dateResult);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sima Solutions | CALIDAD</title>
    <!-- Estilos de Font Awesome y fuentes de Google Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Poppins:wght@400;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="./css/scann.css">
    <link rel="icon" href="/img/simafa.png" type="image/sima">

</head>

<body>
    
<header>
    <!-- Enlaces a la página de inicio y al historial -->
    <a href="scann.php" class="home-icon"><i class="fas fa-home"></i></a>    <nav>
        <a href="../index.php">EMBARQUES</a>
        <a href="../add_modelo.php">Agregar Numero de parte</a>
        <a href="/Plan_trabajo/add_plan_trabajo.php">Plan de trabajo</a>
        <a href="./historial_calidad/historial_calidad.php" class="history-icon"><i class="fas fa-history"></i> Historial</a>
    </nav>
</header>

<main>
    <h1>Registro Calidad | Sima Solutions</h1>

    <!-- Mostrar mensaje de éxito o error si está presente -->
    <?php if (isset($_GET['message']) && isset($_GET['messageType'])): ?>
        <div id="notification" class="notification <?php echo htmlspecialchars($_GET['messageType']); ?> show">
            <?php echo htmlspecialchars($_GET['message']); ?>
        </div>
    <?php endif; ?>

    <!-- Formulario para crear un nuevo día -->
    <h2>Crear Nuevo Día</h2>
    <form id="createDayForm" name="createDayForm" method="POST" action="back_scann.php" class="center-form">
        <label for="day_date">Fecha del Día:</label>
        <input type="date" id="day_date" name="day_date" required>
        <button type="submit" name="create_day">
            <span>Crear Día</span>
        </button>
    </form>

    <!-- Sección de días -->
    <div class="accordion custom-accordion">
        <p>Total de días/acordeones: <?php echo $numDays; ?></p>
        <?php if ($numDays > 0): ?>
            <?php while ($dateRow = mysqli_fetch_assoc($dateResult)): ?>
                <div class="accordion-item custom-accordion-item">
                    <div class="accordion-header custom-accordion-header">
                        <h3><?php echo date("d/m/Y", strtotime($dateRow['day_date'])); ?></h3>
                        <form method="POST" action="back_scann.php" style="display:inline;">
                            <input type="hidden" name="archive_day" value="<?php echo $dateRow['day_date']; ?>">
                            <button type="submit" class="btn-archive-day">Archivar Día</button>
                        </form>
                    </div>
                    <div class="accordion-body custom-accordion-body" style="display: none;">
                        <!-- Botón para abrir la ventana de agregar ítem -->
                        <button onclick="window.open('./add_item_calidad/add_item_calidad.php?day_id=<?php echo $dateRow['id']; ?>', '_blank', 'width=600,height=400');">
                            Agregar Ítem
                        </button>

                        <!-- Tabla de registros para el día -->
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th class="custom-th">Número de Parte</th>
                                    <th class="custom-th">NIFCO</th>
                                    <th class="custom-th">Serial</th>
                                    <th class="custom-th">Cantidad</th>
                                    <th class="custom-th">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $itemsQuery = "SELECT cs.*, m.numero_parte, m.nifco_numero FROM calidad_cajas_scanned cs JOIN Modelos m ON cs.part_id = m.id WHERE DATE(cs.scan_timestamp) = '" . $dateRow['day_date'] . "'";
                                $itemsResult = mysqli_query($enlace, $itemsQuery);

                                $nifcoCounts = [];
                                $totalQuantity = 0;

                                if ($itemsResult && mysqli_num_rows($itemsResult) > 0):
                                    while ($item = mysqli_fetch_assoc($itemsResult)):
                                        $nifco = $item['nifco_numero'];
                                        $quantity = $item['quantity'];

                                        if (!isset($nifcoCounts[$nifco])) {
                                            $nifcoCounts[$nifco] = ['count' => 0, 'quantity' => 0];
                                        }
                                        $nifcoCounts[$nifco]['count']++;
                                        $nifcoCounts[$nifco]['quantity'] += $quantity;
                                        $totalQuantity += $quantity;
                                        ?>
                                        <tr data-item-id="<?php echo $item['id']; ?>" class="<?php echo $item['status'] == 'Salida' ? 'status-salida' : ''; ?>">
                                            <td class="custom-td"><?php echo htmlspecialchars($item['numero_parte']); ?></td>
                                            <td class="custom-td"><?php echo htmlspecialchars($item['nifco_numero']); ?></td>
                                            <td class="custom-td"><?php echo htmlspecialchars($item['serial_number']); ?></td>
                                            <td class="custom-td"><?php echo htmlspecialchars($item['quantity']); ?></td>
                                            <td class="custom-td">
                                                <button class="btn-edit-item" data-item-id="<?php echo $item['id']; ?>">Editar</button>
                                                <button class="btn-delete-item" data-item-id="<?php echo $item['id']; ?>">Eliminar</button>
                                            </td>
                                        </tr>
                                        <?php
                                    endwhile;
                                else:
                                    ?>
                                    <tr>
                                        <td class="custom-td" colspan="6">No hay registros para este día.</td>
                                    </tr>
                                    <?php
                                endif;
                                ?>
                            </tbody>
                        </table>
                        <!-- Mostrar conteo de NIFCO y total de cantidades -->
                        <div class="nifco-summary">
                            <h5>Resumen de NIFCO</h5>
                            <ul>
                                <?php foreach ($nifcoCounts as $nifco => $count): ?>
                                    <li>
                                        <span class="nifco-number"><?php echo htmlspecialchars($nifco); ?></span>: 
                                        <?php echo number_format($count['quantity']); ?> 
                                        (<?php echo $count['count']; ?> veces)
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <p>Total de cantidades: <?php echo number_format($totalQuantity); ?></p>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="no-dates-message">No hay registros de fechas disponibles.</p>
        <?php endif; ?>
    </div>
</main>

<script src="scann.js"></script>
</body>

</html>