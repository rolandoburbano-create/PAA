<?php 
require 'auth.php'; 
require_once 'conexion.php';
require_once 'includes/csrf.php';

// Sincronización de sesión: Tu header.php busca 'usuario_nombre'
if (!isset($_SESSION['usuario_nombre']) && isset($_SESSION['nombre_completo'])) {
    $_SESSION['usuario_nombre'] = $_SESSION['nombre_completo'];
}

// Verificación estricta de rol para control de accesos
$es_supervisor = (isset($_SESSION['rol']) && $_SESSION['rol'] === 'supervisor');
$bloqueo = $es_supervisor ? 'disabled' : ''; 

// ==========================================
// CÁLCULO DE TOTALES FINANCIEROS (DASHBOARD)
// ==========================================
try {
    $stmt_totales = $pdo->query("SELECT SUM(valor_total_estimado) as total_estimado, SUM(valor_vigencia_actual) as total_vigencia FROM adquisiciones_paa");
    $totales = $stmt_totales->fetch(PDO::FETCH_ASSOC);

    // Si la tabla está vacía, evitamos errores asumiendo 0
    $suma_estimado = $totales['total_estimado'] ? $totales['total_estimado'] : 0;
    $suma_vigencia = $totales['total_vigencia'] ? $totales['total_vigencia'] : 0;

    // Formatear a estilo moneda colombiana (Ej: $ 1.500.000,00)
    $formato_estimado = '$ ' . number_format($suma_estimado, 2, ',', '.');
    $formato_vigencia = '$ ' . number_format($suma_vigencia, 2, ',', '.');
} catch (Exception $e) {
    $formato_estimado = '$ 0,00';
    $formato_vigencia = '$ 0,00';
}
// ==========================================

// Incluimos la estructura inicial de SICAS
include_once 'header.php'; 
?>

<div class="space-y-6">

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="card bg-base-100 shadow-sm border border-base-300 border-l-4 border-l-primary p-5">
            <span class="text-xs uppercase font-bold tracking-wider text-gray-400">Presupuesto Total Estimado</span>
            <h2 class="text-2xl font-extrabold text-primary mt-1 break-all overflow-wrap-break-word"><?php echo $formato_estimado; ?></h2>
        </div>
        <div class="card bg-base-100 shadow-sm border border-base-300 border-l-4 border-l-primary p-5">
            <span class="text-xs uppercase font-bold tracking-wider text-gray-400">Total Vigencia Actual</span>
            <h2 class="text-2xl font-extrabold text-primary mt-1 break-all overflow-wrap-break-word"><?php echo $formato_vigencia; ?></h2>
        </div>
    </div>

    <div class="card bg-base-100 shadow-md border border-base-300 p-6">
        <h3 class="text-lg font-bold text-primary mb-4 flex items-center gap-2">
            <?php echo $es_supervisor ? '<i class="fa-solid fa-eye"></i> Vista de Procesos (Solo Lectura)' : '<i class="fa-solid fa-folder-plus"></i> Registrar Nueva Línea de Adquisición'; ?>
        </h3>
        
        <?php if($es_supervisor): ?>
            <div class="alert alert-warning text-sm rounded-lg mb-4 font-medium flex items-center gap-2">
                <i class="fa-solid fa-circle-info text-lg"></i>
                <span><strong>Modo Supervisor:</strong> Su cuenta tiene restricciones de acceso y solo permite la observación de los datos del PAA.</span>
            </div>
        <?php endif; ?>

        <?php 
        // Capturar los mensajes de éxito o actualización
        $status = isset($_GET['status']) ? $_GET['status'] : ''; 
        ?>

        <?php if($status === 'success'): ?>
            <div class="alert alert-success text-sm rounded-lg mb-4 font-bold flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-lg"></i>
                <span>¡Perfecto! El nuevo proceso ha sido registrado en el PAA.</span>
            </div>
        <?php elseif($status === 'updated'): ?>
            <div class="alert alert-info shadow-sm text-sm rounded-lg mb-4 font-bold flex items-center gap-2 text-blue-900 bg-blue-100 border-blue-200">
                <i class="fa-solid fa-arrows-rotate text-lg"></i>
                <span>Este objeto contractual ya existía. Sus datos han sido actualizados correctamente.</span>
            </div>
        <?php endif; ?>

        <form action="procesar_paa.php" method="POST" class="space-y-4">
            <?php echo campo_csrf(); ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                
                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold text-gray-600">Códigos UNSPSC</span></label>
                    <input type="text" id="inputUNSPSC" name="codigos_unspsc" required <?php echo $bloqueo; ?> class="input input-bordered w-full focus:border-primary text-sm" placeholder="Ej: 43211500" />
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold text-gray-600">Fecha estimada de inicio (Mes)</span></label>
                    <select id="selectMesInicio" name="fecha_estimada_inicio" required <?php echo $bloqueo; ?> class="select select-bordered w-full focus:border-primary text-sm">
                        <option value="">Seleccione el mes...</option>
                        <?php 
                        $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                        foreach($meses as $mes) {
                            echo "<option value='$mes'>$mes</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-control md:col-span-2 relative">
                    <label class="label"><span class="label-text font-semibold text-gray-600">Descripción completa del objeto</span></label>
                    <textarea id="inputDescripcion" name="descripcion" rows="2" autocomplete="off" required <?php echo $bloqueo; ?> class="textarea textarea-bordered w-full focus:border-primary text-sm" placeholder="Empiece a escribir para ver sugerencias históricas..."></textarea>
                    
                    <ul id="menuSugerencias" class="menu bg-base-100 w-full shadow-2xl rounded-box absolute z-50 hidden border border-base-300 max-h-60 overflow-y-auto" style="top: 100%; margin-top: 4px;">
                    </ul>
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold text-gray-600">Duración estimada del contrato (Meses)</span></label>
                    <input type="number" id="inputMeses" name="duracion_meses" min="1" max="12" step="1" required <?php echo $bloqueo; ?> class="input input-bordered w-full focus:border-primary text-sm" placeholder="Ej: 4" />
                    
                    <label id="errorDuracion" class="label py-1 hidden">
                        <span class="label-text-alt font-bold text-error"><i class="fa-solid fa-triangle-exclamation"></i> Límite excedido.</span>
                    </label>
                    
                    <label class="label py-0">
                        <span class="label-text-alt font-bold text-primary">Referencia en días: <span id="calculoDias">0 días</span></span>
                    </label>
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold text-gray-600">Modalidad de selección</span></label>
                    <select id="selectModalidad" name="modalidad_seleccion" required <?php echo $bloqueo; ?> class="select select-bordered w-full focus:border-primary text-sm">
                        <option value="">Seleccione la modalidad...</option>
                        <option value="LICITACION">LICITACION</option>
                        <option value="REGIMEN_ESPECIAL">REGIMEN_ESPECIAL</option>
                        <option value="SUBASTA">SUBASTA</option>
                        <option value="CONCURSO_MERITOS">CONCURSO_MERITOS</option>
                        <option value="SELECCION_ABREVIADA">SELECCION_ABREVIADA</option>
                        <option value="CONTRATACION_DIRECTA">CONTRATACION_DIRECTA</option>
                        <option value="CONTRATACION_MINIMA_CUANTIA">CONTRATACION_MINIMA_CUANTIA</option>
                        <option value="CONCURSO_MERITOS_ABIERTO">CONCURSO_MERITOS_ABIERTO</option>
                        <option value="PROCESOS_SALUD">PROCESOS_SALUD</option>
                        <option value="SELECCION_ABREVIADA_LIT_H_NUM_2_ART_2_LEY_1150_DE_2007">SELECCION_ABREVIADA_LIT_H_NUM_2_ART_2_LEY_1150_DE_2007</option>
                        <option value="ASOCIACION_PUBLICO_PRIVADA">ASOCIACION_PUBLICO_PRIVADA</option>
                        <option value="ASOCIACION_PUBLICO_PRIVADA_INICIATIVA_PRIVADA">ASOCIACION_PUBLICO_PRIVADA_INICIATIVA_PRIVADA</option>
                        <option value="LICITACION OBRA PUBLICA">LICITACION OBRA PUBLICA</option>
                        <option value="CONTRATOS Y CONVENIOS CON MAS DE DOS PARTES">CONTRATOS Y CONVENIOS CON MAS DE DOS PARTES</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 md:col-span-2 gap-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold text-gray-600">Fuente de los recursos</span></label>
                        <select name="fuente_recursos" required <?php echo $bloqueo; ?> class="select select-bordered w-full focus:border-primary text-sm">
                            <option value="">Seleccione la fuente...</option>
                            <option value="Recursos propios">Recursos propios</option>
                            <option value="Recursos de crédito">Recursos de crédito</option>
                            <option value="Sistema General de Participaciones - SGP">Sistema General de Participaciones - SGP</option>
                            <option value="Sistema General de Regalías - SGR">Sistema General de Regalías - SGR</option>
                            <option value="Presupuesto General de la Nación – PGN">Presupuesto General de la Nación – PGN</option>
                            <option value="Recursos Propios (Alcaldías, Gobernaciones y Resguardos Indígenas)">Recursos Propios (Alcaldías, Gobernaciones y Resguardos Indígenas)</option>
                            <option value="Recursos en especie">Recursos en especie</option>
                            <option value="Recursos privados/cooperación">Recursos privados/cooperación</option>
                            <option value="Otros recursos">Otros recursos</option>
                            <option value="Asignación Especial del Sistema General de Participación para Resguardos Indígenas - AESGPRI">Asignación Especial del Sistema General de Participación para Resguardos Indígenas - AESGPRI</option>
                        </select>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold text-gray-600">Valor total estimado  ($)</span></label>
                        <input type="text" name="valor_total_estimado" required <?php echo $bloqueo; ?> class="input input-bordered w-full focus:border-primary text-sm moneda-mask" />
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold text-gray-600">Valor vigencia actual  ($)</span></label>
                        <input type="text" name="valor_vigencia_actual" required <?php echo $bloqueo; ?> class="input input-bordered w-full focus:border-primary text-sm moneda-mask" />
                    </div>
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold text-gray-600">¿Requiere vigencias futuras?</span></label>
                    <select name="requiere_vigencias_futuras" required <?php echo $bloqueo; ?> class="select select-bordered w-full focus:border-primary text-sm">
                        <option value="NO">NO</option>
                        <option value="SI">SI</option>
                    </select>
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold text-gray-600">Estado vigencias futuras</span></label>
                    <select name="estado_vigencias_futuras" required <?php echo $bloqueo; ?> class="select select-bordered w-full focus:border-primary text-sm">
                        <option value="">Seleccione el estado...</option>
                        <option value="NA">NA</option>
                        <option value="No solicitadas">No solicitadas</option>
                        <option value="Solicitadas">Solicitadas</option>
                        <option value="Aprobadas">Aprobadas</option>
                    </select>
                </div>

                <div class="form-control md:col-span-2">
                    <label class="label"><span class="label-text font-semibold text-gray-600">Oficina / Secretaría Responsable</span></label>
                    <input type="text" name="contacto_responsable" value="<?php echo htmlspecialchars($_SESSION['dependencia'] ?? 'No asignada'); ?>" readonly class="input input-bordered bg-base-200 text-gray-500 font-medium w-full text-sm cursor-not-allowed" />
                </div>

            </div>

            <?php if(!$es_supervisor): ?>
                <div class="divider my-4"></div>
                <div class="flex justify-start">
                    <button type="submit" class="btn btn-primary text-white font-bold px-6 shadow-md normal-case">
                        <i class="fa-solid fa-floppy-disk mr-1 text-sm"></i> Guardar en PAA
                    </button>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<script>
    // ==========================================
    // VALIDACIÓN DE PLAZOS Y CONVERSIÓN VISUAL
    // ==========================================
    const inputMeses = document.getElementById('inputMeses');
    const selectMesInicio = document.getElementById('selectMesInicio');
    const errorDuracion = document.getElementById('errorDuracion');
    const spanDias = document.getElementById('calculoDias');

    // Diccionario para convertir el nombre del mes a su posición numérica
    const mapaMeses = {
        'Enero': 1, 'Febrero': 2, 'Marzo': 3, 'Abril': 4,
        'Mayo': 5, 'Junio': 6, 'Julio': 7, 'Agosto': 8,
        'Septiembre': 9, 'Octubre': 10, 'Noviembre': 11, 'Diciembre': 12
    };

    function validarVigencia() {
        const mesTexto = selectMesInicio.value;
        const mesesIngresados = parseInt(inputMeses.value) || 0;

        // Mostrar la referencia en días
        spanDias.textContent = Math.round(mesesIngresados * 30) + " días";

        if (mesTexto && mesesIngresados > 0) {
            const mesNumero = mapaMeses[mesTexto];
            // Lógica: 12 meses - mes actual + 1 (Para incluir el mes que corre)
            const maxMesesPermitidos = 12 - mesNumero + 1;

            // Ajustar el HTML para que el formulario no permita enviar si está mal
            inputMeses.max = maxMesesPermitidos;

            // Validación visual
            if (mesesIngresados > maxMesesPermitidos) {
                inputMeses.classList.add('input-error');
                errorDuracion.classList.remove('hidden');
                errorDuracion.querySelector('span').innerHTML = `<i class="fa-solid fa-triangle-exclamation"></i> Si inicia en ${mesTexto}, no puede superar los ${maxMesesPermitidos} meses.`;
                // Opcional: Forzar a que el input corrija el valor al máximo permitido
                // inputMeses.value = maxMesesPermitidos;
            } else {
                inputMeses.classList.remove('input-error');
                errorDuracion.classList.add('hidden');
            }
        }
    }

    // Escuchar cambios en ambos campos
    inputMeses.addEventListener('input', validarVigencia);
    selectMesInicio.addEventListener('change', validarVigencia);

    // ==========================================
    // SISTEMA DE AUTOCOMPLETADO INTELIGENTE PAA
    // ==========================================
    const inputDesc = document.getElementById('inputDescripcion');
    const inputUNSPSC = document.getElementById('inputUNSPSC');
    const selectModalidad = document.getElementById('selectModalidad'); 
    const menuSugerencias = document.getElementById('menuSugerencias');
    
    let temporizadorBuscador;

    inputDesc.addEventListener('input', function() {
        const texto = this.value;

        if (texto.length < 3) {
            menuSugerencias.classList.add('hidden');
            return;
        }

        clearTimeout(temporizadorBuscador);
        temporizadorBuscador = setTimeout(() => {
            
            fetch(`api_sugerencias.php?q=${encodeURIComponent(texto)}`)
                .then(response => response.json())
                .then(datos => {
                    menuSugerencias.innerHTML = ''; 
                    
                    if (datos.length > 0) {
                        datos.forEach(item => {
                            const li = document.createElement('li');
                            li.innerHTML = `
                                <a class="flex flex-col items-start gap-0 py-2 border-b border-base-200 hover:bg-green-50">
                                    <span class="text-sm font-bold text-gray-800 line-clamp-1">${item.descripcion}</span>
                                    <span class="text-xs text-primary font-semibold">
                                        <i class="fa-solid fa-tag mr-1"></i> UNSPSC: ${item.codigos_unspsc} | Modalidad: ${item.modalidad_seleccion}
                                    </span>
                                </a>
                            `;
                            
                            li.addEventListener('click', () => {
                                inputDesc.value = item.descripcion;
                                inputUNSPSC.value = item.codigos_unspsc;
                                
                                if(selectModalidad) {
                                    for(let i=0; i < selectModalidad.options.length; i++) {
                                        if(selectModalidad.options[i].value === item.modalidad_seleccion) {
                                            selectModalidad.selectedIndex = i;
                                            break;
                                        }
                                    }
                                }
                                
                                menuSugerencias.classList.add('hidden'); 
                            });
                            
                            menuSugerencias.appendChild(li);
                        });
                        menuSugerencias.classList.remove('hidden'); 
                    } else {
                        menuSugerencias.classList.add('hidden');
                    }
                });
        }, 300);
    });

    document.addEventListener('click', function(e) {
        if (e.target !== inputDesc && e.target !== menuSugerencias) {
            menuSugerencias.classList.add('hidden');
        }
    });

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

<?php 
include_once 'footer.php'; 
?>