<?php
require 'backend/db/db.php';
require 'backindex.php';

// Solo mostrar notificaciones si vienen de una acción
$message = isset($_GET['message']) ? $_GET['message'] : '';
$messageType = isset($_GET['messageType']) ? $_GET['messageType'] : '';




// Obtener las fechas de los días en producción ordenadas de los más recientes a los más antiguos
$dateQuery = "SELECT day_date FROM Days WHERE status = 'produccion' ORDER BY day_date DESC";
$dateResult = mysqli_query($enlace, $dateQuery);

// Contar el número de días o acordeones
$numDays = mysqli_num_rows($dateResult);

// Manejar la eliminación de registros
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // Obtener el serial_number del registro que se va a eliminar
    $getSerialQuery = "SELECT serial_number FROM Cajas_scanned WHERE id = $delete_id";
    $serialResult = mysqli_query($enlace, $getSerialQuery);
    if ($serialResult && mysqli_num_rows($serialResult) > 0) {
        $serialRow = mysqli_fetch_assoc($serialResult);
        $serial_number = $serialRow['serial_number'];

        // Cambiar el estado del registro en calidad_cajas_scanned a 'Entrada'
        $updateStatusQuery = "UPDATE calidad_cajas_scanned SET status = 'Entrada' WHERE serial_number = '$serial_number'";
        if (mysqli_query($enlace, $updateStatusQuery)) {
            // Eliminar el registro de Cajas_scanned
            $deleteQuery = "DELETE FROM Cajas_scanned WHERE id = $delete_id";
            if (mysqli_query($enlace, $deleteQuery)) {
                $message = "Registro eliminado y estado actualizado exitosamente.";
                $messageType = "success";
            } else {
                $message = "Error al eliminar el registro: " . mysqli_error($enlace);
                $messageType = "error";
            }
        } else {
            $message = "Error al actualizar el estado del registro: " . mysqli_error($enlace);
            $messageType = "error";
        }
    } else {
        $message = "Registro no encontrado.";
        $messageType = "error";
    }
}

// Obtener las fechas de los días en producción ordenadas de los más recientes a los más antiguos
$dateQuery = "SELECT day_date FROM Days WHERE status = 'produccion' ORDER BY day_date DESC";
$dateResult = mysqli_query($enlace, $dateQuery);

// Contar el número de días o acordeones
$numDays = mysqli_num_rows($dateResult);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sima Solutions | EMBARQUES</title>
    <!-- Estilos de Font Awesome y fuentes de Google Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <link rel="stylesheet" href="/css/index.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">


</head>

<body>

    <header>
        <!-- Enlaces a la página de inicio y al historial -->
        <a href="index.php" class="home-icon"><i class="fas fa-home"></i></a>
        <nav>
            <a href="/Calidad/scann.php">CALIDAD</a>
            <a href="historial.php" class="history-icon"><i class="fas fa-history"></i> Historial</a>
        </nav>
    </header>

    <main>
        <h1>Registros Embarques | Sima Solutions</h1>

        <!-- Sistema de notificaciones -->
        <div id="notification-container">
            <?php if ($message && $messageType): ?>
                <div id="notification" class="notification <?php echo htmlspecialchars($messageType); ?>">
                    <span id="notification-message"><?php echo htmlspecialchars($message); ?></span>
                    <button class="notification-close">&times;</button>
                </div>
            <?php endif; ?>
        </div>




        <!-- Formulario para crear un nuevo día -->
        <h2>Crear Nuevo Día</h2>
        <form id="createDayForm" name="createDayForm" method="POST" class="center-form">
            <label for="day_date">Fecha del Día:</label>
            <input type="date" id="day_date" name="day_date" required>
            <button type="submit" name="create_day">
                <span>Crear Día</span>
            </button>
        </form>


        <!-- Sección de folios y pallets -->
        <div class="accordion custom-accordion">
            <p>Total de días/acordeones: <?php echo $numDays; ?></p>
            <?php if ($numDays > 0): ?>
                <?php while ($dateRow = mysqli_fetch_assoc($dateResult)): ?>
                    <div class="accordion-item custom-accordion-item">
                        <div class="accordion-header custom-accordion-header">
                            <h3><?php echo date("d/m/Y", strtotime($dateRow['day_date'])); ?></h3>
                            <form method="POST" action="backindex.php" style="display:inline;">
                                <input type="hidden" name="archive_day" value="<?php echo $dateRow['day_date']; ?>">
                                <button type="submit" class="btn-archive-day">Archivar Día</button>
                            </form>
                        </div>
                        <div class="accordion-body custom-accordion-body" style="display: none;">
                            <!-- Formulario para crear un nuevo folio -->
                            <form id="createFolioForm-<?php echo $dateRow['day_date']; ?>" name="createFolioForm" method="POST"
                                class="center-form">
                                <input type="hidden" name="folio_date" value="<?php echo $dateRow['day_date']; ?>">
                                <button type="submit" name="create_folio">
                                    <span>Crear Folio</span>
                                </button>
                            </form>

                            <!-- Sección de folios -->
                            <div class="accordion custom-accordion">
                                <?php
                                $foliosQuery = "SELECT * FROM Folios WHERE DATE(departure_date) = '" . $dateRow['day_date'] . "' ORDER BY folio_number ASC";
                                $foliosResult = mysqli_query($enlace, $foliosQuery);

                                if ($foliosResult && mysqli_num_rows($foliosResult) > 0):
                                    while ($folio = mysqli_fetch_assoc($foliosResult)):
                                        ?>
                                        <div class="accordion-item custom-accordion-item" id="folio-<?php echo $folio['id']; ?>"
                                            data-folio-id="<?php echo $folio['id']; ?>">
                                            <div class="accordion-header custom-accordion-header">
                                                <h4><?php echo htmlspecialchars($folio['folio_number']); ?></h4>
                                                <button class="btn-delete-folio" data-folio-id="<?php echo $folio['id']; ?>">
                                                    <i class="fas fa-trash"></i> Eliminar Folio
                                                </button>


                                                <button class="btn-imprimir-folio" data-folio-id="<?php echo $folio['id']; ?>">
                                                    <i class="fas fa-print"></i> Imprimir Folio
                                                </button>
                                            </div>
                                            <div class="accordion-body custom-accordion-body" style="display: none;">
                                                <!-- Formulario para crear un nuevo pallet -->
                                                <form id="createPalletForm-<?php echo $folio['id']; ?>" name="createPalletForm"
                                                    method="POST" class="center-form">
                                                    <input type="hidden" name="pallet_date" value="<?php echo $dateRow['day_date']; ?>">
                                                    <input type="hidden" name="folio_id" value="<?php echo $folio['id']; ?>">
                                                    <button type="submit" name="create_pallet">
                                                        <span>Crear Pallet</span>
                                                    </button>
                                                </form>

                                                <!-- Sección de pallets -->
                                                <div class="accordion custom-accordion">
                                                    <?php
                                                    $palletsQuery = "SELECT * FROM Pallets WHERE folio_id = " . $folio['id'] . " ORDER BY CAST(SUBSTRING_INDEX(pallet_number, ' ', -1) AS UNSIGNED) ASC";
                                                    $palletsResult = mysqli_query($enlace, $palletsQuery);

                                                    if ($palletsResult && mysqli_num_rows($palletsResult) > 0):
                                                        while ($pallet = mysqli_fetch_assoc($palletsResult)):
                                                            ?>
                                                            <div class="accordion-item custom-accordion-item"
                                                                data-pallet-id="<?php echo $pallet['id']; ?>">
                                                                <div class="accordion-header custom-accordion-header">
                                                                    <h5><?php echo htmlspecialchars($pallet['pallet_number']); ?></h5>
                                                                    <button class="btn-show-add-item"
                                                                        data-pallet-id="<?php echo $pallet['id']; ?>"
                                                                        data-folio-id="<?php echo $folio['id']; ?>">
                                                                        <i class="fas fa-plus"></i> Agregar Item
                                                                    </button>
                                                                    <button class="btn-delete-pallet"
                                                                        data-pallet-id="<?php echo $pallet['id']; ?>">
                                                                        <i class="fas fa-trash"></i> Eliminar Pallet
                                                                    </button>

                                                                </div>
                                                                <div class="accordion-body custom-accordion-body" style="display: none;">
                                                                    <!-- Tabla de registros para el pallet -->
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
                                                                            $itemsQuery = "SELECT cs.*, m.numero_parte, m.nifco_numero FROM Cajas_scanned cs JOIN Modelos m ON cs.part_id = m.id WHERE cs.pallet_id = " . $pallet['id'];
                                                                            $itemsResult = mysqli_query($enlace, $itemsQuery);

                                                                            $nifcoCounts = [];
                                                                            $totalQuantity = 0;
                                                                            $totalBoxes = 0;

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
                                                                                    $totalBoxes++;
                                                                                    ?>
                                                                                    <tr data-item-id="<?php echo $item['id']; ?>">
                                                                                        <td class="custom-td">
                                                                                            <?php echo htmlspecialchars($item['numero_parte']); ?>
                                                                                        </td>
                                                                                        <td class="custom-td">
                                                                                            <?php echo htmlspecialchars($item['nifco_numero']); ?>
                                                                                        </td>
                                                                                        <td class="custom-td">
                                                                                            <?php echo htmlspecialchars($item['serial_number']); ?>
                                                                                        </td>
                                                                                        <td class="custom-td">
                                                                                            <?php echo htmlspecialchars($item['quantity']); ?>
                                                                                        </td>
                                                                                        <td class="custom-td">
                                                                                            <button class="btn-delete-item"
                                                                                                data-item-id="<?php echo $item['id']; ?>">Eliminar</button>
                                                                                        </td>
                                                                                    </tr>
                                                                                    <?php
                                                                                endwhile;
                                                                            else:
                                                                                ?>
                                                                                <tr>
                                                                                    <td class="custom-td" colspan="5">No hay registros para este
                                                                                        pallet.</td>
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
                                                                                    <span
                                                                                        class="nifco-number"><?php echo htmlspecialchars($nifco); ?></span>:
                                                                                    <?php echo number_format($count['quantity']); ?>
                                                                                    (<?php echo $count['count']; ?> CAJAS)
                                                                                </li>
                                                                            <?php endforeach; ?>
                                                                        </ul>
                                                                        <p>Total de cantidades: <?php echo number_format($totalQuantity); ?></p>
                                                                        <p>Total de cajas: <?php echo number_format($totalBoxes); ?></p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <?php
                                                        endwhile;
                                                    else:
                                                        ?>
                                                        <p>No hay pallets disponibles para este folio.</p>
                                                        <?php
                                                    endif;
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                    endwhile;
                                else:
                                    ?>
                                    <p>No hay folios disponibles para esta fecha.</p>
                                    <?php
                                endif;
                                ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="no-dates-message">No hay registros de fechas disponibles.</p>
            <?php endif; ?>
        </div>

    </main>

    <script src="index.js"></script>
</body>

</html>