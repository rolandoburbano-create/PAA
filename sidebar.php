<aside id="sidebar" class="w-64 bg-gradient-to-b from-[#1B5E20] to-[#2EA32E] text-white flex-none flex flex-col h-full shadow-2xl z-20">
    
    <div class="p-6 flex flex-col items-center border-b border-white/10 min-h-[140px] justify-center">
        <img src="assets/img/escudo.png" alt="Escudo" id="sidebar-logo" class="h-20 w-auto mb-2">
        <div class="text-center sidebar-text">
            <h1 class="text-s font-bold tracking-normal text-white drop-shadow-md">Alcaldía de Silvia</h1>
            <p class="text-[20px] text-green-400 font-bold drop-shadow-md">PAA</p>
        </div>
    </div>

    <?php $rol_actual = $_SESSION['rol'] ?? ''; ?>
    <ul class="menu p-4 gap-2 text-white">
        <li class="menu-title text-green-400 font-bold text-xs mt-2 drop-shadow-sm sidebar-text">GESTIÓN DE ADQUISICIONES</li>
        
        <li>
            <a href="ver_paa.php" class="hover:bg-white/10 transition-colors" title="Visor de Procesos PAA">
                <i class="fa-solid fa-table-list w-6 text-center opacity-80"></i> 
                <span class="sidebar-text">Visor de Procesos PAA</span>
            </a>
        </li>

        <li>
            <a href="index.php" class="hover:bg-white/10 transition-colors" title="Registrar Proceso PAA">
                <i class="fa-solid fa-folder-plus w-6 text-center opacity-80"></i> 
                <span class="sidebar-text">Registrar Proceso PAA</span>
            </a>
        </li>

        <?php if ($rol_actual === 'admin'): ?>
        <li>
            <a href="importar_excel.php" class="hover:bg-white/10 transition-colors" title="Carga Masiva (Excel/TXT)">
                <i class="fa-solid fa-file-import w-6 text-center opacity-80"></i> 
                <span class="sidebar-text">Carga Masiva (TXT)</span>
            </a>
        </li>

        <li>
            <a href="exportar_secop.php" class="hover:bg-white/10 transition-colors" title="Exportar a SECOP I">
                <i class="fa-solid fa-file-arrow-down w-6 text-center opacity-80"></i> 
                <span class="sidebar-text">Exportar a SECOP I</span>
            </a>
        </li>
        <?php endif; ?>
        <?php
        if ($rol_actual === 'admin'):
        ?>
        <li class="menu-title text-green-400 font-bold text-xs mt-4 drop-shadow-sm sidebar-text">ADMINISTRACIÓN</li>
        <li>
            <a href="gestionar_usuarios.php" class="hover:bg-white/10 transition-colors" title="Gestión de Usuarios">
                <i class="fa-solid fa-users-gear w-6 text-center opacity-80"></i> 
                <span class="sidebar-text">Gestión de Usuarios</span>
            </a>
        </li>
        <?php endif; ?>
    </ul>

    <div class="p-4 border-t border-white/10 mt-auto sidebar-text">
        <span class="text-[10px] text-green-300 font-mono">
            ROL: <?php echo htmlspecialchars($rol_actual, ENT_QUOTES, 'UTF-8'); ?>
        </span>
    </div>
</aside>