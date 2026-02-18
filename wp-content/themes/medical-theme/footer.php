<footer id="colophon" class="site-footer">
    <div class="footer-container">
        <div class="footer-grid">
            <!-- Column 1: Brand Info -->
            <div class="footer-col footer-brand">
                <div class="footer-logo">
                    <span class="logo-icon">🏥</span>
                    <span class="logo-text">Medical</span>
                </div>
                <p class="footer-tagline">Tecnología médica avanzada con un enfoque humano. Cuidamos lo que más importa.
                </p>
                <div class="footer-social">
                    <a href="#" class="social-icon">Instagram</a>
                    <a href="#" class="social-icon">LinkedIn</a>
                    <a href="#" class="social-icon">Twitter</a>
                </div>
            </div>

            <!-- Column 2: Navigation -->
            <div class="footer-col footer-links">
                <h4 class="footer-title">Navegación</h4>
                <ul>
                    <li><a href="<?php echo esc_url(home_url('/')); ?>">Inicio</a></li>
                    <li><a href="<?php echo esc_url(get_post_type_archive_link('medico')); ?>">Staff Médico</a></li>
                    <li><a href="#">Sedes y Horarios</a></li>
                    <li><a href="#">Portal del Paciente</a></li>
                </ul>
            </div>

            <!-- Column 3: Contact & Support -->
            <div class="footer-col footer-contact">
                <h4 class="footer-title">Contacto</h4>
                <ul class="contact-list">
                    <li><span class="icon">📍</span> Av. Santa Fe 1234, CABA</li>
                    <li><span class="icon">📞</span> +54 11 4567-8900</li>
                    <li><span class="icon">✉️</span> info@medical.com</li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="site-info">
                &copy; <?php echo date('Y'); ?> Medical Center. Todos los derechos reservados.
            </div>
            <div class="footer-developer">
                Diseñado por <a href="https://alexis.dydlabs.com/" target="_blank" rel="noopener">Alexis</a>
            </div>
            <div class="footer-legal">
                <a href="#">Privacidad</a>
                <a href="#">Términos</a>
            </div>
        </div>
    </div>
</footer>
</div><!-- #page -->

<?php wp_footer(); ?>

</body>

</html>