<?php 
require 'auth.php'; 
require 'conexion.php'; 
require 'includes/csrf.php';

// Verificación estricta de rol
$es_supervisor = (isset($_SESSION['rol']) && $_SESSION['rol'] === 'supervisor');

try {
    $stmt = $pdo->query("SELECT * FROM adquisiciones_paa ORDER BY id DESC");
    $procesos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $procesos = [];
}

if (!isset($_SESSION['usuario_nombre']) && isset($_SESSION['nombre_completo'])) {
    $_SESSION['usuario_nombre'] = $_SESSION['nombre_completo'];
}
include_once 'header.php'; 
?>

<div class="space-y-6 w-full">
    <div class="navbar bg-base-100 shadow-md rounded-xl border border-base-300 px-6 justify-between flex-wrap gap-4">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-table-list text-primary text-xl"></i>
            <h1 class="text-xl font-bold text-gray-800 tracking-tight hidden lg:block">Consolidado PAA</h1>
            <h1 class="text-xl font-bold text-gray-800 tracking-tight lg:hidden">PAA</h1>
        </div>
        
        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto justify-end">
            
            <div class="relative w-full md:w-64">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
                </div>
                <input type="text" id="buscadorTabla" class="input input-sm input-bordered w-full pl-9 focus:border-primary text-sm shadow-inner" placeholder="Buscar proceso, modalidad, UNSPSC...">
            </div>

            <span id="contadorRegistros" class="badge badge-primary font-bold px-3 py-3 h-auto">
                Total: <?php echo count($procesos); ?>
            </span>
            
            <a href="exportar_secop.php" class="btn btn-sm btn-success shadow-sm text-white font-bold normal-case rounded-lg">
                <i class="fa-solid fa-file-csv"></i> Exportar
            </a>
        </div>
    </div>

    <div class="card bg-base-100 shadow-md border border-base-300 overflow-hidden">
        <div class="overflow-x-auto w-full">
            <table class="table table-zebra w-full text-xs table-pin-rows table-pin-cols">
                <thead class="bg-base-200 text-gray-700 font-bold uppercase tracking-wider text-[11px] border-b border-base-300">
                    <tr>
                        <th class="py-4">UNSPSC</th>
                        <th>Descripción del Objeto</th>
                        <th>Inicio</th>
                        <th>Plazo</th>
                        <th>Modalidad</th>
                        <th>Fuente de Recursos</th>
                        <th class="text-right">Valor Total</th>
                        <th class="text-right">Vigencia Actual</th>
                        <th class="text-center">V. Futuras</th>
                        <th>Responsable</th>
                        <?php if(!$es_supervisor): ?>
                            <th class="text-center">Acciones</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                
                <tbody id="cuerpoTabla">
                    <?php if (empty($procesos)): ?>
                        <tr class="fila-vacia">
                            <td colspan="<?php echo $es_supervisor ? 10 : 11; ?>" class="text-center py-8 text-gray-500 font-medium">
                                <i class="fa-solid fa-folder-open text-3xl opacity-30 mb-2 block"></i>
                                No se encontraron líneas de adquisición registradas en el sistema.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($procesos as $proceso): 
                            $valor_total = '$ ' . number_format($proceso['valor_total_estimado'], 2, ',', '.');
                            $valor_vigencia = '$ ' . number_format($proceso['valor_vigencia_actual'], 2, ',', '.');
                            $clase_vf = ($proceso['requiere_vigencias_futuras'] === 'SI') ? 'badge-warning font-bold' : 'badge-ghost opacity-60';
                        ?>
                            <tr class="hover:bg-base-200 transition-colors border-b border-base-200 fila-datos">
                                <td class="font-bold text-gray-900 font-mono"><?php echo htmlspecialchars($proceso['codigos_unspsc']); ?></td>
                                <td class="max-w-xs whitespace-normal font-medium text-gray-600 leading-relaxed">
                                    <div class="line-clamp-2 hover:line-clamp-none transition-all duration-200" title="<?php echo htmlspecialchars($proceso['descripcion']); ?>">
                                        <?php echo htmlspecialchars($proceso['descripcion']); ?>
                                    </div>
                                </td>
                                <td class="font-semibold text-gray-700"><?php echo htmlspecialchars($proceso['fecha_estimada_inicio']); ?></td>
                                <td class="font-semibold text-center text-gray-700 whitespace-nowrap">
                                    <?php echo intval($proceso['duracion_meses']); ?> mes<?php echo intval($proceso['duracion_meses']) > 1 ? 'es' : ''; ?>
                                </td>
                                <td class="whitespace-normal">
                                    <span class="badge badge-sm font-semibold border-gray-300 bg-gray-50 text-gray-700 px-2 py-2 h-auto text-center leading-tight">
                                        <?php echo str_replace('_', ' ', $proceso['modalidad_seleccion']); ?>
                                    </span>
                                </td>
                                <td class="max-w-[150px] whitespace-normal text-gray-600 font-medium">
                                    <?php echo htmlspecialchars($proceso['fuente_recursos']); ?>
                                </td>
                                <td class="text-right font-bold text-gray-900 font-mono whitespace-nowrap"><?php echo $valor_total; ?></td>
                                <td class="text-right font-bold text-primary font-mono whitespace-nowrap"><?php echo $valor_vigencia; ?></td>
                                <td class="text-center">
                                    <span class="badge badge-sm p-2 <?php echo $clase_vf; ?>" title="Estado: <?php echo htmlspecialchars($proceso['estado_vigencias_futuras']); ?>">
                                        <?php echo htmlspecialchars($proceso['requiere_vigencias_futuras']); ?>
                                    </span>
                                </td>
                                <td class="text-gray-500 font-semibold whitespace-nowrap"><?php echo htmlspecialchars($proceso['contacto_responsable']); ?></td>
                                <?php if(!$es_supervisor): ?>
                                    <td class="text-center whitespace-nowrap">
                                        <div class="join shadow-sm">
                                            <a href="editar_paa.php?id=<?php echo $proceso['id']; ?>" class="btn btn-xs join-item btn-square btn-outline border-base-300 hover:btn-primary flex items-center justify-center" title="Editar Registro">
                                                <i class="fa-solid fa-pen-to-square text-[10px]"></i>
                                            </a>
                                            <form method="POST" action="eliminar_paa.php" onsubmit="return confirm('¿Está seguro de eliminar esta línea de adquisición?');">
                                                <?php echo campo_csrf(); ?>
                                                <input type="hidden" name="id" value="<?php echo $proceso['id']; ?>">
                                                <button type="submit" class="btn btn-xs join-item btn-square btn-outline border-base-300 hover:btn-error" title="Eliminar Registro">
                                                    <i class="fa-solid fa-trash text-[10px]"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                        
                        <tr id="mensajeSinResultados" class="hidden">
                            <td colspan="<?php echo $es_supervisor ? 10 : 11; ?>" class="text-center py-8 text-gray-500 font-medium bg-base-100">
                                <i class="fa-solid fa-magnifying-glass-minus text-3xl opacity-30 mb-2 block text-error"></i>
                                No se encontraron procesos que coincidan con la búsqueda.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if(isset($_GET['status']) && $_GET['status'] === 'edited'): ?>
        <div class="alert alert-success shadow-sm text-sm rounded-lg font-bold flex items-center gap-2">
            <i class="fa-solid fa-circle-check text-lg"></i>
            <span>¡Excelente! El registro se ha actualizado correctamente en la base de datos.</span>
        </div>
    <?php elseif(isset($_GET['status']) && $_GET['status'] === 'deleted'): ?>
        <div class="alert alert-error shadow-sm text-sm rounded-lg font-bold flex items-center gap-2">
            <i class="fa-solid fa-trash text-lg"></i>
            <span>El registro ha sido eliminado del sistema.</span>
        </div>
    <?php endif; ?>

<script>
// ==========================================
// BUSCADOR EN TIEMPO REAL (DOM FILTERING)
// ==========================================
document.addEventListener("DOMContentLoaded", function() {
    const buscador = document.getElementById('buscadorTabla');
    const filas = document.querySelectorAll('.fila-datos');
    const contador = document.getElementById('contadorRegistros');
    const mensajeSinResultados = document.getElementById('mensajeSinResultados');
    const totalOriginal = <?php echo count($procesos); ?>;

    if(buscador) {
        buscador.addEventListener('keyup', function() {
            let textoBusqueda = this.value.toLowerCase().trim();
            let visibles = 0;

            // Recorrer todas las filas que contienen datos
            filas.forEach(function(fila) {
                // Obtener todo el texto de la fila (ignora las etiquetas HTML)
                let contenidoFila = fila.textContent.toLowerCase();
                
                // Si el texto de búsqueda está dentro del contenido de la fila, se muestra
                if (contenidoFila.includes(textoBusqueda)) {
                    fila.style.display = '';
                    visibles++;
                } else {
                    fila.style.display = 'none';
                }
            });

            // Actualizar la insignia del contador
            if(textoBusqueda === '') {
                contador.innerHTML = `Total: ${totalOriginal}`;
                contador.classList.replace('badge-warning', 'badge-primary');
            } else {
                contador.innerHTML = `Filtrados: ${visibles}`;
                contador.classList.replace('badge-primary', 'badge-warning');
            }

            // Mostrar el mensaje de "sin resultados" si todo se ocultó
            if(mensajeSinResultados) {
                if (visibles === 0 && textoBusqueda !== '') {
                    mensajeSinResultados.classList.remove('hidden');
                } else {
                    mensajeSinResultados.classList.add('hidden');
                }
            }
        });
    }
});
</script>

<?php 
include_once 'footer.php'; 
?>