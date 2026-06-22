<?php 
require 'auth.php'; 
require 'conexion.php'; 
require 'includes/csrf.php';

// Bloqueamos el acceso si es supervisor
if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'supervisor') {
    die("Acceso denegado. No tiene permisos para editar.");
}

// Capturar el ID que viene en la URL
$id_proceso = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Buscar el registro exacto en la base de datos
$stmt = $pdo->prepare("SELECT * FROM adquisiciones_paa WHERE id = ?");
$stmt->execute([$id_proceso]);
$registro = $stmt->fetch(PDO::FETCH_ASSOC);

// Si alguien pone un ID que no existe, lo regresamos al visor
if (!$registro) {
    header("Location: ver_paa.php");
    exit;
}

if (!isset($_SESSION['usuario_nombre']) && isset($_SESSION['nombre_completo'])) {
    $_SESSION['usuario_nombre'] = $_SESSION['nombre_completo'];
}
include_once 'header.php'; 

// Arrays para construir los Selects dinámicamente
$meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
$modalidades = ['LICITACION', 'REGIMEN_ESPECIAL', 'SUBASTA', 'CONCURSO_MERITOS', 'SELECCION_ABREVIADA', 'CONTRATACION_DIRECTA', 'CONTRATACION_MINIMA_CUANTIA', 'CONCURSO_MERITOS_ABIERTO', 'PROCESOS_SALUD', 'SELECCION_ABREVIADA_LIT_H_NUM_2_ART_2_LEY_1150_DE_2007', 'ASOCIACION_PUBLICO_PRIVADA', 'ASOCIACION_PUBLICO_PRIVADA_INICIATIVA_PRIVADA', 'LICITACION OBRA PUBLICA', 'CONTRATOS Y CONVENIOS CON MAS DE DOS PARTES'];
$fuentes = ['Recursos propios', 'Recursos de crédito', 'Sistema General de Participaciones - SGP', 'Sistema General de Regalías - SGR', 'Presupuesto General de la Nación – PGN', 'Recursos Propios (Alcaldías, Gobernaciones y Resguardos Indígenas)', 'Recursos en especie', 'Recursos privados/cooperación', 'Otros recursos', 'Asignación Especial del Sistema General de Participación para Resguardos Indígenas - AESGPRI'];
?>

<div class="max-w-5xl mx-auto space-y-6">
    <div class="navbar bg-base-100 shadow-md rounded-xl border border-base-300 px-6">
        <div class="flex-1 flex items-center gap-2">
            <i class="fa-solid fa-pen-to-square text-primary text-xl"></i>
            <h1 class="text-xl font-bold text-gray-800 tracking-tight">Edición de Objeto Contractual</h1>
        </div>
        <a href="ver_paa.php" class="btn btn-sm btn-ghost font-bold normal-case">
            <i class="fa-solid fa-arrow-left"></i> Volver al Visor
        </a>
    </div>

    <div class="card bg-base-100 shadow-md border border-base-300 p-6">
        <form action="procesar_paa.php" method="POST" class="space-y-4">
            <?php echo campo_csrf(); ?>
            <input type="hidden" name="id_proceso" value="<?php echo $registro['id']; ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold text-gray-600">Códigos UNSPSC</span></label>
                    <input type="text" name="codigos_unspsc" value="<?php echo htmlspecialchars($registro['codigos_unspsc']); ?>" required class="input input-bordered w-full focus:border-primary text-sm" />
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold text-gray-600">Fecha estimada de inicio</span></label>
                    <select id="selectMesInicio" name="fecha_estimada_inicio" required class="select select-bordered w-full focus:border-primary text-sm">
                        <?php foreach($meses as $mes): ?>
                            <option value="<?php echo $mes; ?>" <?php echo ($registro['fecha_estimada_inicio'] == $mes) ? 'selected' : ''; ?>><?php echo $mes; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-control md:col-span-2">
                    <label class="label"><span class="label-text font-semibold text-gray-600">Descripción completa del objeto</span></label>
                    <textarea name="descripcion" rows="2" required class="textarea textarea-bordered w-full focus:border-primary text-sm"><?php echo htmlspecialchars($registro['descripcion']); ?></textarea>
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold text-gray-600">Duración estimada (Meses)</span></label>
                    <input type="number" id="inputMeses" name="duracion_meses" min="1" max="12" step="1" value="<?php echo floatval($registro['duracion_meses']); ?>" required class="input input-bordered w-full focus:border-primary text-sm" />
                    <label id="errorDuracion" class="label py-1 hidden"><span class="label-text-alt font-bold text-error">Límite excedido.</span></label>
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold text-gray-600">Modalidad de selección</span></label>
                    <select name="modalidad_seleccion" required class="select select-bordered w-full focus:border-primary text-sm">
                        <?php foreach($modalidades as $mod): ?>
                            <option value="<?php echo $mod; ?>" <?php echo ($registro['modalidad_seleccion'] == $mod) ? 'selected' : ''; ?>><?php echo str_replace('_', ' ', $mod); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 md:col-span-2 gap-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold text-gray-600">Fuente de recursos</span></label>
                        <select name="fuente_recursos" required class="select select-bordered w-full focus:border-primary text-sm">
                            <?php foreach($fuentes as $fuente): ?>
                                <option value="<?php echo $fuente; ?>" <?php echo ($registro['fuente_recursos'] == $fuente) ? 'selected' : ''; ?>><?php echo $fuente; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold text-gray-600">Total estimado ($)</span></label>
                        <input type="text" name="valor_total_estimado" value="<?php echo number_format(floatval($registro['valor_total_estimado']), 2, ',', '.'); ?>" required class="input input-bordered w-full focus:border-primary text-sm moneda-mask" />
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold text-gray-600">Vigencia actual ($)</span></label>
                        <input type="text" name="valor_vigencia_actual" value="<?php echo number_format(floatval($registro['valor_vigencia_actual']), 2, ',', '.'); ?>" required class="input input-bordered w-full focus:border-primary text-sm moneda-mask" />
                    </div>
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold text-gray-600">Vigencias futuras</span></label>
                    <select name="requiere_vigencias_futuras" required class="select select-bordered w-full focus:border-primary text-sm">
                        <option value="NO" <?php echo ($registro['requiere_vigencias_futuras'] == 'NO') ? 'selected' : ''; ?>>NO</option>
                        <option value="SI" <?php echo ($registro['requiere_vigencias_futuras'] == 'SI') ? 'selected' : ''; ?>>SI</option>
                    </select>
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold text-gray-600">Estado V. futuras</span></label>
                    <select name="estado_vigencias_futuras" required class="select select-bordered w-full focus:border-primary text-sm">
                        <?php $estados_vf = ['NA', 'No solicitadas', 'Solicitadas', 'Aprobadas']; 
                        foreach($estados_vf as $evf): ?>
                            <option value="<?php echo $evf; ?>" <?php echo ($registro['estado_vigencias_futuras'] == $evf) ? 'selected' : ''; ?>><?php echo $evf; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="divider my-4"></div>
            <div class="flex justify-end gap-3">
                <button type="submit" class="btn btn-primary text-white font-bold px-8 shadow-md normal-case">
                    <i class="fa-solid fa-floppy-disk mr-1"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Reutilizamos tu lógica de JavaScript para validar que no exceda Diciembre
    const inputMeses = document.getElementById('inputMeses');
    const selectMesInicio = document.getElementById('selectMesInicio');
    const errorDuracion = document.getElementById('errorDuracion');
    const mapaMeses = { 'Enero': 1, 'Febrero': 2, 'Marzo': 3, 'Abril': 4, 'Mayo': 5, 'Junio': 6, 'Julio': 7, 'Agosto': 8, 'Septiembre': 9, 'Octubre': 10, 'Noviembre': 11, 'Diciembre': 12 };

    function validarVigencia() {
        const mesTexto = selectMesInicio.value;
        const mesesIngresados = parseInt(inputMeses.value) || 0;
        if (mesTexto && mesesIngresados > 0) {
            const maxMesesPermitidos = 12 - mapaMeses[mesTexto] + 1;
            inputMeses.max = maxMesesPermitidos;
            if (mesesIngresados > maxMesesPermitidos) {
                inputMeses.classList.add('input-error');
                errorDuracion.classList.remove('hidden');
                errorDuracion.querySelector('span').innerHTML = `Límite: ${maxMesesPermitidos} meses.`;
            } else {
                inputMeses.classList.remove('input-error');
                errorDuracion.classList.add('hidden');
            }
        }
    }
    inputMeses.addEventListener('input', validarVigencia);
    selectMesInicio.addEventListener('change', validarVigencia);

    // ==========================================
    // MÁSCARA MONETARIA (formato $ colombiano)
    // ==========================================
    function formatearMoneda(input) {
        let valor = input.value.replace(/[^\d,]/g, '');
        let partes = valor.split(',');
        if (partes.length > 2) return;
        if (partes[0]) {
            partes[0] = partes[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }
        if (partes.length === 2) {
            partes[1] = partes[1].slice(0, 2);
            input.value = partes[0] + ',' + partes[1];
        } else {
            input.value = partes[0];
        }
    }

    document.querySelectorAll('.moneda-mask').forEach(function(el) {
        el.addEventListener('input', function() {
            formatearMoneda(this);
        });
    });

    document.querySelector('form').addEventListener('submit', function() {
        document.querySelectorAll('.moneda-mask').forEach(function(el) {
            el.value = el.value.replace(/\./g, '').replace(',', '.');
        });
    });
</script>

<?php include_once 'footer.php'; ?>