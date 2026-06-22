<?php
require 'auth.php'; 
require 'conexion.php'; 

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="paa_secop_I_' . date('Ymd_His') . '.csv"');

$output = fopen('php://output', 'w');

// Encabezados exactos exigidos por el formato
fputcsv($output, [
    'Códigos UNSPSC', 'Descripción', 'Fecha estimada de inicio de proceso de selección (mes)', 
    'Duración estimada del contrato (número de mes(es))', 'Modalidad de selección', 
    'Fuente de los recursos', 'Valor total estimado', 'Valor estimado en la vigencia actual', 
    '¿Se requieren vigencias futuras?', 'Estado de solicitud de vigencias futuras', 
    'Datos de contacto del responsable'
], ';');

// Seleccionamos "duracion_meses" en lugar de "duracion_dias"
$stmt = $pdo->query("SELECT codigos_unspsc, descripcion, fecha_estimada_inicio, 
                     duracion_meses, modalidad_seleccion, fuente_recursos, 
                     valor_total_estimado, valor_vigencia_actual, 
                     requiere_vigencias_futuras, estado_vigencias_futuras, 
                     contacto_responsable FROM adquisiciones_paa");

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    // SECOP I exige números sin formatos extraños, forzamos 2 decimales sin separador de miles
    $row['valor_total_estimado'] = number_format($row['valor_total_estimado'], 2, '.', '');
    $row['valor_vigencia_actual'] = number_format($row['valor_vigencia_actual'], 2, '.', '');
    
    fputcsv($output, $row, ';');
}

fclose($output);
exit;
?>