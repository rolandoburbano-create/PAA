<?php
session_start();
require 'conexion.php';

// Solución al error fatal: Definimos BASE_URL temporalmente si tu sistema no lo ha definido aún
if (!defined('BASE_URL')) {
    define('BASE_URL', ''); 
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capturamos el campo 'username' (que en tu HTML visualmente se llama Correo Institucional)
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT id, username, password, nombre_completo, dependencia, rol FROM usuarios WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['nombre_completo'] = $user['nombre_completo'];
        $_SESSION['dependencia'] = $user['dependencia'];
        $_SESSION['rol'] = $user['rol'] ?: 'funcionario';
        
        session_regenerate_id(true);
        
        header("Location: ver_paa.php");
        exit;
    } else {
        $error = "Usuario o contraseña incorrectos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - PAA</title>
    
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.10.2/dist/full.min.css" rel="stylesheet" type="text/css" />
    <script>var _w=console.warn;console.warn=function(m){if(typeof m==='string'&&m.indexOf('cdn.tailwindcss.com')>-1)return;_w.apply(console,arguments)};document.addEventListener('DOMContentLoaded',function(){setTimeout(function(){console.warn=_w},500)})</script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

     <style>
        [data-theme="light"] {
            /* --p (Primary): Verde Institucional Puro (#008000) */
            --p: 52% 0.177 142.5; 
            /* --pf (Primary Focus): Verde ligeramente más oscuro para el efecto 'Hover' al pasar el mouse */
            --pf: 46% 0.177 142.5; 
            /* --pc (Primary Content): Texto blanco sobre los botones verdes */
            --pc: 100% 0 0; 
            
            /* --n (Neutral): Verde MUY oscuro casi negro para el menú lateral */
            --n: 28% 0.08 142.5; 
            /* --nc (Neutral Content): Texto blanco sobre el menú lateral */
            --nc: 100% 0 0; 
        }
    </style>
</head>
<body class="bg-base-200">
    <?php
    $nombre = "SICAS";
    $color = ($nombre == "SICAS") ? "#018001" : "#ffffff";
    ?>
   
    <div class="min-h-screen w-full flex items-center justify-center p-4">
        
        <div class="card w-full max-w-md bg-base-100 shadow-2xl border-t-8 border-primary">
            <div class="card-body">
                
                <div class="text-center mb-6">
                    <h1 class="text-2xl font-bold text-gray-800">Alcaldía de Silvia</h1>
                    
                    <img src="<?php echo BASE_URL; ?>assets/img/escudo.png" 
                         alt="Escudo Alcaldía de Silvia" 
                         class="h-28 w-auto mx-auto mb-4 drop-shadow-md">
                    
                    <span class="badge border-0" style="background-color: <?php echo $color; ?>; color: white;">
                        <?php echo ucfirst($nombre); ?>
                    </span>
                    <br></br>
                    <div class="badge badge-primary badge-outline font-semibold mt-2 p-3 text-xs md:text-sm text-center h-auto leading-relaxed">
                        Plan Anual de Adquisiciones Alcaldía de Silvia
                    </div>
                </div>

                <?php if(!empty($error)): ?>
                    <div class="alert alert-error p-3 text-sm font-semibold rounded-lg flex items-center gap-2 mb-2">
                        <i class="fa-solid fa-circle-exclamation text-lg"></i>
                        <span><?php echo $error; ?></span>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" class="space-y-4">
                    
                    <div class="form-control w-full">
                        <label class="label">
                            <span class="label-text font-semibold text-gray-700">Correo Institucional</span>
                        </label>
                        <label class="input input-bordered flex items-center gap-2 focus-within:border-primary transition-colors">
                            <i class="fa-solid fa-envelope opacity-50 text-primary"></i>
                            <input type="text" name="username" required class="grow" placeholder="usuario o admin" />
                        </label>
                    </div>

                    <div class="form-control w-full">
                        <label class="label">
                            <span class="label-text font-semibold text-gray-700">Contraseña</span>
                        </label>
                        <label class="input input-bordered flex items-center gap-2 focus-within:border-primary transition-colors">
                            <i class="fa-solid fa-lock opacity-50 text-primary"></i>
                            <input type="password" name="password" required class="grow" placeholder="••••••••" />
                        </label>
                    </div>

                    <div class="form-control mt-6">
                        <button type="submit" class="btn btn-primary text-lg shadow-lg text-white">
                            <i class="fa-solid fa-right-to-bracket mr-2"></i> Ingresar
                        </button>
                    </div>
                </form>

                <div class="divider text-xs opacity-50 uppercase tracking-widest my-4">Seguridad</div>
                <div class="text-center text-[10px] opacity-60 leading-relaxed text-gray-500">
                    &copy; <?php echo date('Y'); ?> Municipio de Silvia, Cauca. <br>
                    Leyes 80 de 1993 y 1474 de 2011.
                </div>
            </div>
        </div>

    </div>

</body>
</html>