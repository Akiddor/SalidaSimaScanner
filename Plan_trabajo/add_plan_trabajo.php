<?php require 'back_add_plan_trabajo.php';

$message = isset($_GET['message']) ? $_GET['message'] : '';
$messageType = isset($_GET['messageType']) ? $_GET['messageType'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Plan de Trabajo</title>
    <link rel="stylesheet" href="/Plan_trabajo/add_plan.css">
    <!-- Estilos de Font Awesome y fuentes de Google Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="icon" href="/img/simafa.png" type="image/sima">
</head>
<body>
    <header>
        <a href="../Calidad/scann.php" class="home-icon"><i class="fas fa-home"></i></a>
    </header>

    <main>
        <h1>Agregar Plan de Trabajo</h1>

        <?php if (!empty($message)): ?>
            <div class="notification <?php echo $messageType; ?> show">
                <?php echo nl2br($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="center-form">
            <label for="fecha">Fecha:</label>
            <input type="date" id="fecha" name="fecha" required>

            <div class="button-container">
                <button type="button" id="add-nifco" class="add-nifco-btn"><i class="fas fa-plus"></i></button>
            </div>

            <div id="nifco-container">
                <div class="nifco-entry">
                    <label for="nifco_numero[]">Número de NIFCO:</label>
                    <input type="text" id="nifco_numero[]" name="nifco_numero[]" required>

                    <label for="piezas[]">Piezas a trabajar:</label>
                    <input type="number" id="piezas[]" name="piezas[]" required>
                </div>
            </div>

            <button type="submit">Agregar Plan de Trabajo</button>
        </form>

        <h2>Planes de Trabajo Existentes</h2>
        <div class="accordion">
            <?php if (!empty($planesPorFecha)): ?>
                <?php foreach ($planesPorFecha as $fecha => $planes): ?>
                    <div class="accordion-item">
                        <button class="accordion-button"><?php echo htmlspecialchars($fecha); ?></button>
                        <div class="accordion-content">
                            <table class="custom-table">
                                <thead>
                                    <tr>
                                        <th class="custom-th">#</th>
                                        <th class="custom-th">NIFCO</th>
                                        <th class="custom-th">Piezas Plan</th>
                                        <th class="custom-th">Piezas Registradas</th>
                                        <th class="custom-th">Diferencia</th>
                                        <th class="custom-th">Adherencia (%)</th>
                                        <th class="custom-th">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $contador = 1; ?>
                                    <?php foreach ($planes as $plan): ?>
                                        <tr class="plan-row" data-piezas-plan="<?php echo $plan['piezas']; ?>" data-piezas-registradas="<?php echo $plan['piezas_registradas']; ?>">
                                            <td class="custom-td"><?php echo $contador++; ?></td>
                                            <td class="custom-td"><?php echo htmlspecialchars($plan['nifco_numero']); ?></td>
                                            <td class="custom-td"><?php echo number_format($plan['piezas']); ?></td>
                                            <td class="custom-td"><?php echo number_format($plan['piezas_registradas']); ?></td>
                                            <td class="custom-td">
                                                <?php 
                                                $diferencia = $plan['piezas_registradas'] - $plan['piezas'];
                                                if ($diferencia > 0): ?>
                                                    <span class="difference positive">+<?php echo number_format($diferencia); ?></span>
                                                <?php elseif ($diferencia < 0): ?>
                                                    <span class="difference negative"><?php echo number_format($diferencia); ?></span>
                                                <?php else: ?>
                                                    <span class="difference zero"><?php echo number_format($diferencia); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="custom-td">
                                                <?php 
                                                $adherencia = ($plan['piezas'] > 0) ? ($plan['piezas_registradas'] / $plan['piezas']) * 100 : 0;
                                                echo number_format($adherencia, 2) . '%';
                                                ?>
                                            </td>
                                            <td class="custom-td">
                                                <a href="edit_plan_trabajo.php?id=<?php echo $plan['id']; ?>" class="btn-edit">Editar</a>
                                                <a href="?delete_id=<?php echo $plan['id']; ?>" class="btn-delete" onclick="return confirm('¿Estás seguro de que deseas eliminar este plan de trabajo?');">Eliminar</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-plans-message">No hay planes de trabajo disponibles.</p>
            <?php endif; ?>
        </div>
    </main>

    <script src="/Plan_trabajo/add_plan.js"></script>
</body>
</html>