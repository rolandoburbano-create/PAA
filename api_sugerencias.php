<?php
require 'conexion.php';
// Indicamos que este archivo devuelve datos en formato JSON
header('Content-Type: application/json; charset=utf-8');

// Capturamos el texto que el usuario está escribiendo
$busqueda = isset($_GET['q']) ? trim($_GET['q']) : '';

// Si escriben menos de 3 caracteres, no buscamos para no sobrecargar la base de datos
if (strlen($busqueda) < 3) {
    echo json_encode([]);
    exit;
}

try {
    // Buscamos descripciones únicas que contengan las palabras clave
    // Traemos también el UNSPSC y la modalidad asociados a esa descripción histórica
    $stmt = $pdo->prepare("SELECT DISTINCT descripcion, codigos_unspsc, modalidad_seleccion 
                           FROM adquisiciones_paa 
                           WHERE descripcion LIKE ? 
                           LIMIT 8"); // Máximo 8 sugerencias para no saturar la pantalla
                           
    $stmt->execute(["%$busqueda%"]);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($resultados);
} catch (Exception $e) {
    echo json_encode([]);
}
?>