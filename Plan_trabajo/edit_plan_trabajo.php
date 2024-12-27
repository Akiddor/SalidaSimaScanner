<?php
require '../backend/db/db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "SELECT * FROM PlanTrabajo WHERE id = $id";
    $result = mysqli_query($enlace, $query);

    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_array($result);
        $nifco_numero = $row['nifco_numero'];
        $fecha = $row['fecha'];
        $piezas = $row['piezas'];
    }
}

if (isset($_POST['update'])) {
    $id = $_GET['id'];
    $nifco_numero = $_POST['nifco_numero'];
    $fecha = $_POST['fecha'];
    $piezas = $_POST['piezas'];

    // Verificar si el NIFCO existe en la tabla Modelos
    $modelCheckQuery = "SELECT * FROM Modelos WHERE nifco_numero = '$nifco_numero'";
    $modelCheckResult = mysqli_query($enlace, $modelCheckQuery);

    if (mysqli_num_rows($modelCheckResult) == 0) {
        $message = "El número de NIFCO '$nifco_numero' no existe en la base de datos de Modelos.";
        $messageType = "error";
    } else {
        // Verificar si ya existe un registro para el mismo NIFCO y fecha, excluyendo el registro actual
        $checkQuery = "SELECT * FROM PlanTrabajo WHERE nifco_numero = '$nifco_numero' AND fecha = '$fecha' AND id != $id";
        $checkResult = mysqli_query($enlace, $checkQuery);

        if (mysqli_num_rows($checkResult) > 0) {
            $message = "El número de NIFCO '$nifco_numero' ya tiene un plan de trabajo para la fecha '$fecha'.";
            $messageType = "error";
        } else {
            $query = "UPDATE PlanTrabajo SET nifco_numero = '$nifco_numero', fecha = '$fecha', piezas = $piezas WHERE id = $id";
            if (mysqli_query($enlace, $query)) {
                $message = "Plan de trabajo actualizado exitosamente.";
                $messageType = "success";
                header("Location: add_plan_trabajo.php?message=" . urlencode($message) . "&messageType=" . urlencode($messageType));
                exit();
            } else {
                $message = "Error al actualizar el plan de trabajo: " . mysqli_error($enlace);
                $messageType = "error";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Plan de Trabajo</title>
    <link rel="stylesheet" href="/Plan_trabajo/add_plan.css">
    <!-- Estilos de Font Awesome y fuentes de Google Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <a href="../index.php" class="home-icon"><i class="fas fa-home"></i></a>
    </header>

    <main>
        <h1>Editar Plan de Trabajo</h1>

        <?php if (isset($message)): ?>
            <div class="notification <?php echo $messageType; ?> show">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form action="edit_plan_trabajo.php?id=<?php echo $_GET['id']; ?>" method="POST" class="center-form">
            <label for="nifco_numero">Número de NIFCO:</label>
            <input type="text" id="nifco_numero" name="nifco_numero" value="<?php echo $nifco_numero; ?>" required>

            <label for="fecha">Fecha:</label>
            <input type="date" id="fecha" name="fecha" value="<?php echo $fecha; ?>" required>

            <label for="piezas">Piezas a trabajar:</label>
            <input type="number" id="piezas" name="piezas" value="<?php echo $piezas; ?>" required>

            <button type="submit" name="update">Actualizar Plan de Trabajo</button>
        </form>
    </main>
</body>
</html>