<!DOCTYPE html>
<?php
// Consultar notificaciones sin leer SOLO si es administrador
$total_notificaciones = 0;
if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin') {
    require_once 'conexion.php';
    try {
        $stmt_notif = $pdo->query("SELECT COUNT(id) as total FROM notificaciones_sistema WHERE leido = 0");
        $total_notificaciones = $stmt_notif->fetch(PDO::FETCH_ASSOC)['total'];
    } catch (Exception $e) {}
}
?>

<html lang="es" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plan Anual de Adquisiciones - Alcaldía de Silvia</title>
    
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.10.2/dist/full.min.css" rel="stylesheet" type="text/css" />
    <script>var _w=console.warn;console.warn=function(m){if(typeof m==='string'&&m.indexOf('cdn.tailwindcss.com')>-1)return;_w.apply(console,arguments)};document.addEventListener('DOMContentLoaded',function(){setTimeout(function(){console.warn=_w},500)})</script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        [data-theme="light"] {
            --p: 52% 0.177 142.5; 
            --pf: 46% 0.177 142.5; 
            --pc: 100% 0 0; 
            --n: 28% 0.88 142.5; 
            --nc: 100% 0 0; 
        }

        /* =======================================
           ANIMACIONES DEL MINI SIDEBAR
           ======================================= */
        #sidebar { transition: width 0.3s ease; overflow-x: hidden; }
        #sidebar .sidebar-text { transition: opacity 0.2s ease; white-space: nowrap; }
        #sidebar-logo { transition: height 0.3s ease; }
        
        /* CORRECCIÓN: Se eliminó 'body' para que el botón actúe sobre toda la estructura */
        .sidebar-mini #sidebar { width: 5rem; /* Equivale a w-20 de Tailwind */ }
        .sidebar-mini #sidebar .sidebar-text { opacity: 0; display: none; }
        .sidebar-mini #sidebar-logo { height: 2.5rem; margin-bottom: 0; }
        .sidebar-mini #sidebar .menu a { justify-content: center; padding-left: 0; padding-right: 0; }
        .sidebar-mini #sidebar .menu i { margin-right: 0 !important; font-size: 1.25rem; }
        .sidebar-mini #sidebar .menu-title { display: none; }
    </style>

    <script>
        if (localStorage.getItem('sidebarColapsado') === 'true') {
            document.documentElement.classList.add('sidebar-mini');
        }
    </script>
</head>

<body class="bg-base-200 flex h-screen overflow-hidden text-base-content">
    <?php include_once 'sidebar.php'; ?>
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <div class="navbar bg-primary text-primary-content shadow-md z-10 px-4">
            <div class="flex-1 flex items-center">
                <button id="btnToggleSidebar" class="btn btn-square btn-ghost hover:bg-white/20">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                <a class="text-lg font-bold ml-2 hidden sm:block">Plan Anual de Adquisiciones (PAA)</a>
                <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
                <a href="gestionar_usuarios.php" class="btn btn-sm btn-ghost ml-4 hidden md:inline-flex items-center gap-1 border-white/20">
                    <i class="fa-solid fa-users-gear"></i> Usuarios
                </a>
                <?php endif; ?>
            </div>

            
            <div class="flex-none gap-4">
                <div class="flex items-center gap-2">
                    
                    <div class="avatar placeholder">
                        <div class="bg-neutral-focus text-neutral-content rounded-full w-8">
                            <i class="fa-solid fa-user text-xs"></i>
                        </div>
                    </div>
                    
                    <span class="text-sm font-medium hidden md:inline">
                        <?php echo htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario', ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                </div>
                <a href="logout.php" class="btn btn-sm btn-ghost border-white/20">
                    Salir
                </a>
            </div>
        </div>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-base-200 p-6">