    </main>
        <footer class="bg-white border-t border-gray-200 py-4 px-6 text-center text-sm text-gray-500">
            <p>&copy; <?php echo date('Y'); ?> Alcaldía del Municipio de Silvia, Cauca. Sistema alineado a la Ley 80/93 y Ley 1474/11.</p>
        </footer>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const btnToggle = document.getElementById('btnToggleSidebar');
            
            if(btnToggle) {
                btnToggle.addEventListener('click', () => {
                    // Alterna la clase en la etiqueta <body> (o html)
                    const isMini = document.documentElement.classList.toggle('sidebar-mini');
                    
                    // Guarda la preferencia en el navegador para que se recuerde al navegar
                    localStorage.setItem('sidebarColapsado', isMini);
                });
            }
        });
    </script>
</body>
</html>