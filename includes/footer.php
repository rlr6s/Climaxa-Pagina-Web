    <?php if (isset($show_footer) && $show_footer): ?>
        <!-- Footer -->
        <footer class="dashboard-footer">
            <div class="footer-content">
                <div class="footer-links">
                    <a href="https://www.instagram.com/climatizacion_integral/">INSTAGRAM</a>
                    <a href="https://www.facebook.com/profile.php?id=100063950863135">FACEBOOK</a>
                    <a href="https://api.whatsapp.com/send/?phone=8492430962&app_absent=0&utm_source=ig&utm_medium=social&utm_content=link_in_bio&fbclid=PAZXh0bgNhZW0CMTEAc3J0YwZhcHBfaWQMMjU2MjgxMDQwNTU4AAGnnx-OctiE5L41iEQqoBXwYel0LrkR8dZnZWKX8IQE5Ph6ptju5hlIEE68dQg_aem_tyWDvYr021x6sY1PPtfGtQ">WHATSAPP</a>
                    <a href="https://x.com/?lang=es">TWITTER</a>
                    <a href="https://www.linkedin.com">LINKEDIN</a>
                </div>
                <div class="copyright">
                    <p>&copy; <?php echo date('Y'); ?> CLIMAXA. Todos los derechos reservados.</p>
                </div>
            </div>
        </footer>
    <?php endif; ?>
    
    <?php if (isset($js_files)): ?>
        <?php foreach ($js_files as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <script src="../assets/js/app.js"></script>
</body>
</html>