<?php 
require 'auth.php'; 
require 'conexion.php'; 
require 'includes/csrf.php';

ini_set('auto_detect_line_endings', TRUE);
ini_set('memory_limit', '256M');

if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'supervisor') {
    die("Acceso denegado. Los supervisores no pueden importar datos.");
}

$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["archivo_importar"])) {
    csrf_guard();
    $archivo = $_FILES["archivo_importar"]["tmp_name"];
    $nombre_archivo = strtolower($_FILES["archivo_importar"]["name"]);
    
    if ($_FILES["archivo_importar"]["size"] > 0) {
        
        $delimitador = "\t"; 
        
        if (strpos($nombre_archivo, '.csv') !== false) {
            $primer_linea = file_get_contents($archivo, false, null, 0, 500);
            $delimitador = (substr_count($primer_linea, ';') > substr_count($primer_linea, ',')) ? ';' : ',';
        }

        $file = fopen($archivo, "r");
        fgetcsv($file, 0, $delimitador); 
        
        $registros_exitosos = 0;
        
        $stmt = $pdo->prepare("INSERT INTO adquisiciones_paa (
            codigos_unspsc, descripcion, fecha_estimada_inicio, duracion_meses, 
            modalidad_seleccion, fuente_recursos, valor_total_estimado, 
            valor_vigencia_actual, requiere_vigencias_futuras, estado_vigencias_futuras, 
            contacto_responsable
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        // Diccionario para traducir números de Excel a Nombres de mes
        $diccionario_meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        while (($datos = fgetcsv($file, 0, $delimitador)) !== FALSE) { 
            
            $datos = array_map('trim', $datos);

            if (empty($datos[0])) {
                continue; 
            }

            $datos = array_pad($datos, 11, '');

            // Lógica inteligente para determinar el nombre del mes
            $mes_excel = $datos[2];
            $fecha_inicio_nombre = (is_numeric($mes_excel) && isset($diccionario_meses[intval($mes_excel)])) 
                                    ? $diccionario_meses[intval($mes_excel)] 
                                    : ucfirst(strtolower($mes_excel));

            $duracion_meses = floatval(str_replace(',', '.', $datos[3]));
            $v_total = floatval(preg_replace('/[^0-9.]/', '', str_replace(',', '.', $datos[6])));
            $v_vigencia = floatval(preg_replace('/[^0-9.]/', '', str_replace(',', '.', $datos[7])));

            try {
                $stmt->execute([
                    $datos[0], 
                    $datos[1], 
                    $fecha_inicio_nombre, // ¡Se guarda la variable convertida a texto!
                    $duracion_meses, 
                    $datos[4], 
                    $datos[5], 
                    $v_total, 
                    $v_vigencia, 
                    strtoupper($datos[8]), 
                    $datos[9], 
                    $datos[10] 
                ]);
                $registros_exitosos++;
            } catch (Exception $e) {
                continue; 
            }
        }
        fclose($file);
        
        $mensaje = "¡Importación finalizada! Se guardaron $registros_exitosos procesos en el PAA.";
        $tipo_mensaje = "alert-success";
    } else {
        $mensaje = "El archivo está vacío o tiene un error.";
        $tipo_mensaje = "alert-error";
    }
}

if (!isset($_SESSION['usuario_nombre']) && isset($_SESSION['nombre_completo'])) {
    $_SESSION['usuario_nombre'] = $_SESSION['nombre_completo'];
}
include_once 'header.php'; 
?>

<div class="max-w-4xl mx-auto space-y-6">
    <div class="navbar bg-base-100 shadow-md rounded-xl border border-base-300 px-6">
        <h1 class="text-xl font-bold text-gray-800 tracking-tight">
            <i class="fa-solid fa-file-import text-primary mr-2"></i> Importación Masiva del PAA
        </h1>
    </div>

    <div class="card bg-base-100 shadow-md border border-base-300 p-8">
        
        <?php if($mensaje): ?>
            <div class="alert <?php echo $tipo_mensaje; ?> text-sm font-bold rounded-lg mb-6 flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-lg"></i>
                <span><?php echo $mensaje; ?></span>
            </div>
        <?php endif; ?>

        <div class="bg-blue-50 border-l-4 border-blue-500 p-5 rounded-r-lg mb-6 text-sm text-blue-800">
            <h3 class="font-bold mb-2"><i class="fa-solid fa-lightbulb"></i> Instrucciones para evitar errores:</h3>
            <ol class="list-decimal ml-5 space-y-1">
                <li>Abra su formato de Excel original.</li>
                <li>Vaya a <b>Archivo > Guardar como</b>.</li>
                <li>Elija el formato <b>Texto (delimitado por tabulaciones) (*.txt)</b>.</li>
                <li>Suba el archivo .txt generado. Este formato es 100% seguro contra errores de puntuación.</li>
            </ol>
        </div>

        <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
            <?php echo campo_csrf(); ?>
            <div class="form-control w-full">
                <label class="label">
                    <span class="label-text font-bold text-gray-700">Seleccionar archivo (.txt delimitado por tabulaciones)</span>
                </label>
                <input type="file" name="archivo_importar" accept=".txt,.csv" required class="file-input file-input-bordered file-input-primary w-full" />
            </div>

            <div class="divider"></div>
            
            <div class="flex justify-end gap-3">
                <a href="index.php" class="btn btn-ghost normal-case">Volver al Panel</a>
                <button type="submit" class="btn btn-primary text-white font-bold normal-case">
                    <i class="fa-solid fa-cloud-arrow-up mr-2"></i> Procesar Archivo
                </button>
            </div>
        </form>
    </div>
</div>

<?php include_once 'footer.php'; ?>