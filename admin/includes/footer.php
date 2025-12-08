<?php
// admin/includes/footer.php
?>
            </div> <!-- .content-area -->
        </main> <!-- .main-content -->
    </div> <!-- .admin-container -->

    <!-- Scripts -->
    <script>
        // Toggle sidebar en móvil
        document.getElementById('menu-toggle').addEventListener('click', function() {
            document.querySelector('.admin-container').classList.toggle('sidebar-collapsed');
        });

        // Cerrar dropdown al hacer clic fuera
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.user-menu')) {
                document.querySelectorAll('.dropdown').forEach(dropdown => {
                    dropdown.style.display = 'none';
                });
            }
        });
    </script>
    
    <?php if (isset($js_files)): ?>
        <?php foreach ($js_files as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>