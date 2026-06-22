<?php 
require 'auth.php'; 
require 'conexion.php'; 
require 'includes/csrf.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    die("Acceso denegado. Solo el administrador puede gestionar usuarios.");
}

$mensaje = '';
$tipo_mensaje = '';

$DEPENDENCIAS = [
    'Secretaría de Gobierno y Participación Ciudadana',
    'Secretaría Administrativa y Financiera',
    'Oficina Asesora de Planeación',
    'Secretaría de Infraestructura',
    'Secretaría de Bienestar y Desarrollo Social',
    'Secretaría de Desarrollo Productivo y Ambiental',
    'Oficina Asesora Jurídica',
    'Concejo Municipal',
    'Personería Municipal'
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    csrf_guard();

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $nombre_completo = trim($_POST['nombre_completo'] ?? '');
    $dependencia = trim($_POST['dependencia'] ?? '');
    $rol = trim($_POST['rol'] ?? 'funcionario');

    if (empty($username) || empty($password) || empty($nombre_completo) || empty($dependencia)) {
        $mensaje = "Todos los campos son obligatorios.";
        $tipo_mensaje = "alert-error";
    } elseif (!in_array($dependencia, $DEPENDENCIAS)) {
        $mensaje = "Dependencia no válida.";
        $tipo_mensaje = "alert-error";
    } else {
        $stmt_check = $pdo->prepare("SELECT id FROM usuarios WHERE username = ?");
        $stmt_check->execute([$username]);

        if ($stmt_check->fetch()) {
            $mensaje = "El nombre de usuario '$username' ya existe.";
            $tipo_mensaje = "alert-error";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt_insert = $pdo->prepare("INSERT INTO usuarios (username, password, nombre_completo, dependencia, rol) VALUES (?, ?, ?, ?, ?)");
            $stmt_insert->execute([$username, $hash, $nombre_completo, $dependencia, $rol]);

            $mensaje = "Usuario '$username' creado exitosamente.";
            $tipo_mensaje = "alert-success";
        }
    }
}

$stmt_usuarios = $pdo->query("SELECT id, username, nombre_completo, dependencia, rol FROM usuarios ORDER BY id ASC");
$usuarios = $stmt_usuarios->fetchAll(PDO::FETCH_ASSOC);

if (!isset($_SESSION['usuario_nombre']) && isset($_SESSION['nombre_completo'])) {
    $_SESSION['usuario_nombre'] = $_SESSION['nombre_completo'];
}
include_once 'header.php'; 
?>

<div class="max-w-6xl mx-auto space-y-6">
    <div class="navbar bg-base-100 shadow-md rounded-xl border border-base-300 px-6">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-users-gear text-primary text-xl"></i>
            <h1 class="text-xl font-bold text-gray-800 tracking-tight">Gestión de Usuarios</h1>
        </div>
    </div>

    <?php if($mensaje): ?>
        <div class="alert <?php echo $tipo_mensaje; ?> text-sm font-bold rounded-lg flex items-center gap-2 shadow-sm">
            <i class="fa-solid fa-circle-check text-lg"></i>
            <span><?php echo $mensaje; ?></span>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
        <div class="lg:col-span-2">
            <div class="card bg-base-100 shadow-md border border-base-300">
                <div class="card-body p-6">
                    <h2 class="card-title text-primary text-lg">
                        <i class="fa-solid fa-user-plus"></i> Nuevo Usuario
                    </h2>

                    <form method="POST" class="space-y-4 mt-2">
                        <?php echo campo_csrf(); ?>

                        <div class="form-control">
                            <label class="label"><span class="label-text font-semibold text-gray-600">Usuario</span></label>
                            <input type="text" name="username" required class="input input-bordered w-full focus:border-primary text-sm" placeholder="Ej: secretaria_gobierno" autocomplete="off">
                        </div>

                        <div class="form-control">
                            <label class="label"><span class="label-text font-semibold text-gray-600">Contraseña</span></label>
                            <input type="password" name="password" required class="input input-bordered w-full focus:border-primary text-sm" placeholder="••••••••">
                        </div>

                        <div class="form-control">
                            <label class="label"><span class="label-text font-semibold text-gray-600">Nombre completo</span></label>
                            <input type="text" name="nombre_completo" required class="input input-bordered w-full focus:border-primary text-sm" placeholder="Ej: Secretaría de Gobierno" autocomplete="off">
                        </div>

                        <div class="form-control">
                            <label class="label"><span class="label-text font-semibold text-gray-600">Dependencia</span></label>
                            <select name="dependencia" required class="select select-bordered w-full focus:border-primary text-sm">
                                <option value="">Seleccione una dependencia...</option>
                                <?php foreach($DEPENDENCIAS as $dep): ?>
                                    <option value="<?php echo $dep; ?>"><?php echo $dep; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-control">
                            <label class="label"><span class="label-text font-semibold text-gray-600">Rol</span></label>
                            <select name="rol" required class="select select-bordered w-full focus:border-primary text-sm">
                                <option value="funcionario">Funcionario</option>
                                <option value="supervisor">Supervisor (solo lectura)</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="btn btn-primary text-white font-bold w-full normal-case">
                                <i class="fa-solid fa-floppy-disk mr-1"></i> Crear Usuario
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="lg:col-span-3">
            <div class="card bg-base-100 shadow-md border border-base-300 overflow-hidden">
                <div class="card-body p-0">
                    <div class="overflow-x-auto w-full">
                        <table class="table table-zebra w-full text-xs table-pin-rows">
                            <thead class="bg-base-200 text-gray-700 font-bold uppercase tracking-wider text-[11px] border-b border-base-300">
                                <tr>
                                    <th class="py-4">ID</th>
                                    <th>Usuario</th>
                                    <th>Nombre</th>
                                    <th>Dependencia</th>
                                    <th>Rol</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($usuarios)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-8 text-gray-500 font-medium">
                                            <i class="fa-solid fa-users-slash text-3xl opacity-30 mb-2 block"></i>
                                            No hay usuarios registrados.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($usuarios as $u): ?>
                                        <tr class="hover:bg-base-200 transition-colors border-b border-base-200">
                                            <td class="font-mono font-bold text-gray-700"><?php echo $u['id']; ?></td>
                                            <td class="font-mono font-semibold text-gray-800"><?php echo htmlspecialchars($u['username']); ?></td>
                                            <td class="font-medium text-gray-600"><?php echo htmlspecialchars($u['nombre_completo']); ?></td>
                                            <td class="text-gray-600"><?php echo htmlspecialchars($u['dependencia']); ?></td>
                                            <td>
                                                <span class="badge badge-sm font-bold p-2 h-auto
                                                    <?php echo $u['rol'] === 'admin' ? 'badge-primary' : ($u['rol'] === 'supervisor' ? 'badge-warning' : 'badge-ghost'); ?>">
                                                    <?php echo ucfirst($u['rol']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once 'footer.php'; ?>
