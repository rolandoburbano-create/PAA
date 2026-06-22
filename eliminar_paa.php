<?php
require 'auth.php';
require 'conexion.php';
require 'includes/csrf.php';

if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'supervisor') {
    header("Location: ver_paa.php");
    exit;
}

csrf_guard();

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($id <= 0) {
    header("Location: ver_paa.php");
    exit;
}

$stmt = $pdo->prepare("SELECT descripcion, codigos_unspsc FROM adquisiciones_paa WHERE id = ?");
$stmt->execute([$id]);
$registro = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$registro) {
    header("Location: ver_paa.php");
    exit;
}

$stmt_delete = $pdo->prepare("DELETE FROM adquisiciones_paa WHERE id = ?");
$stmt_delete->execute([$id]);

$nota = $pdo->prepare("INSERT INTO notificaciones_sistema (usuario, accion, detalle) VALUES (?, ?, ?)");
$nota->execute([
    $_SESSION['usuario_nombre'] ?? 'Desconocido',
    'ELIMINACIÓN',
    "Eliminó el objeto contractual ID $id (UNSPSC: {$registro['codigos_unspsc']})"
]);

header("Location: ver_paa.php?status=deleted");
exit;
