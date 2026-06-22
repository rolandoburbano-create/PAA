<?php
require 'auth.php'; 
require 'conexion.php';
require 'includes/csrf.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    csrf_guard();
    
    if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'supervisor') {
        header("Location: ver_paa.php");
        exit;
    }

    // Si viene un ID del formulario de edición, lo capturamos
    $id_proceso = isset($_POST['id_proceso']) ? intval($_POST['id_proceso']) : 0;
    
    $duracion_meses = floatval($_POST['duracion_meses']); 
    $contacto_responsable = $_SESSION['dependencia'];
    $descripcion = trim($_POST['descripcion']);

    if ($id_proceso > 0) {
        // ==========================================================
        // RUTA A: EDICIÓN DIRECTA POR BOTÓN "EDITAR"
        // ==========================================================
        $stmt_update = $pdo->prepare("UPDATE adquisiciones_paa SET 
            codigos_unspsc = ?, descripcion = ?, fecha_estimada_inicio = ?, duracion_meses = ?, 
            modalidad_seleccion = ?, fuente_recursos = ?, valor_total_estimado = ?, 
            valor_vigencia_actual = ?, requiere_vigencias_futuras = ?, estado_vigencias_futuras = ?
            WHERE id = ?");

        $stmt_update->execute([
            $_POST['codigos_unspsc'], $descripcion, $_POST['fecha_estimada_inicio'], $duracion_meses, 
            $_POST['modalidad_seleccion'], $_POST['fuente_recursos'], $_POST['valor_total_estimado'], 
            $_POST['valor_vigencia_actual'], $_POST['requiere_vigencias_futuras'], $_POST['estado_vigencias_futuras'], 
            $id_proceso // Actualiza justo este registro
        ]);

        // NUEVO: Registrar notificación de actualización
        $nota = $pdo->prepare("INSERT INTO notificaciones_sistema (usuario, accion, detalle) VALUES (?, ?, ?)");
        $nota->execute([
            $_SESSION['usuario_nombre'], 
            'ACTUALIZACIÓN', 
            "Modificó el objeto contractual ID $id_proceso (UNSPSC: {$_POST['codigos_unspsc']})"
        ]);

        // Redirige al visor mostrando la notificación verde de éxito
        header("Location: ver_paa.php?status=edited");
        exit;

    } else {
        // ==========================================================
        // RUTA B: LÓGICA DE REGISTRO / ACTUALIZACIÓN (Index.php)
        // ==========================================================
        $stmt_check = $pdo->prepare("SELECT id FROM adquisiciones_paa WHERE descripcion = ? LIMIT 1");
        $stmt_check->execute([$descripcion]);
        $existe = $stmt_check->fetch(PDO::FETCH_ASSOC);

        if ($existe) {
            $stmt_update_old = $pdo->prepare("UPDATE adquisiciones_paa SET codigos_unspsc=?, fecha_estimada_inicio=?, duracion_meses=?, modalidad_seleccion=?, fuente_recursos=?, valor_total_estimado=?, valor_vigencia_actual=?, requiere_vigencias_futuras=?, estado_vigencias_futuras=?, contacto_responsable=? WHERE id=?");
            $stmt_update_old->execute([$_POST['codigos_unspsc'], $_POST['fecha_estimada_inicio'], $duracion_meses, $_POST['modalidad_seleccion'], $_POST['fuente_recursos'], $_POST['valor_total_estimado'], $_POST['valor_vigencia_actual'], $_POST['requiere_vigencias_futuras'], $_POST['estado_vigencias_futuras'], $contacto_responsable, $existe['id']]);
            // NUEVO: Registrar notificación de actualización desde inicio
            $nota = $pdo->prepare("INSERT INTO notificaciones_sistema (usuario, accion, detalle) VALUES (?, ?, ?)");
            $nota->execute([
                $_SESSION['usuario_nombre'], 
                'ACTUALIZACIÓN', 
                "Actualizó datos financieros del objeto ID {$existe['id']} (UNSPSC: {$_POST['codigos_unspsc']})"
            ]);
            header("Location: index.php?status=updated");
            exit;
        } else {
            $stmt_insert = $pdo->prepare("INSERT INTO adquisiciones_paa (codigos_unspsc, descripcion, fecha_estimada_inicio, duracion_meses, modalidad_seleccion, fuente_recursos, valor_total_estimado, valor_vigencia_actual, requiere_vigencias_futuras, estado_vigencias_futuras, contacto_responsable) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt_insert->execute([$_POST['codigos_unspsc'], $descripcion, $_POST['fecha_estimada_inicio'], $duracion_meses, $_POST['modalidad_seleccion'], $_POST['fuente_recursos'], $_POST['valor_total_estimado'], $_POST['valor_vigencia_actual'], $_POST['requiere_vigencias_futuras'], $_POST['estado_vigencias_futuras'], $contacto_responsable]);
            // NUEVO: Registrar notificación de creación
            $nota = $pdo->prepare("INSERT INTO notificaciones_sistema (usuario, accion, detalle) VALUES (?, ?, ?)");
            $nota->execute([
                $_SESSION['usuario_nombre'], 
                'NUEVO REGISTRO', 
                "Registró un nuevo objeto contractual (UNSPSC: {$_POST['codigos_unspsc']})"
            ]);
            header("Location: index.php?status=success");
            exit;
        }
    }
}
?>