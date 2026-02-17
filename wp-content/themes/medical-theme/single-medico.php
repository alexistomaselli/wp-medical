<?php
/**
 * The template for displaying all single posts of 'medico' post type
 *
 * @package Medical_Health
 */

get_header();

$foto = get_field('foto_perfil');
?>

<main id="primary" class="site-main">
    <div class="medico-header-bg"></div>

    <div class="content-wrapper">
        <?php
        while (have_posts()):
            the_post();
            ?>
            <div class="medico-profile-layout">
                <main class="medico-main-content">
                    <div class="profile-header-premium">
                        <div class="profile-photo-area">
                            <?php 
                            if ($foto): 
                                // Handle both array and object return formats
                                if (is_array($foto)) {
                                    $foto_url = $foto['url'];
                                    $foto_alt = $foto['alt'];
                                } elseif (is_numeric($foto)) {
                                    // ID
                                    $foto_url = wp_get_attachment_url($foto);
                                    $foto_alt = get_the_title();
                                } elseif (is_object($foto) && isset($foto->ID)) {
                                    // WP_Post object
                                    $foto_url = wp_get_attachment_url($foto->ID);
                                    $foto_alt = $foto->post_title;
                                } else {
                                    // String URL (fallback)
                                    $foto_url = $foto;
                                    $foto_alt = get_the_title();
                                }
                            ?>
                                <img src="<?php echo esc_url($foto_url); ?>" alt="<?php echo esc_attr($foto_alt); ?>">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/400x500?text=Médico" alt="Sin foto">
                            <?php endif; ?>
                        </div>
                        <div class="profile-info-area">
                            <?php if ($badge = get_field('etiqueta_certificacion')): ?>
                                <div class="certification-badge">
                                    <span class="dashicons dashicons-verified"></span>
                                    <?php echo esc_html($badge); ?>
                                </div>
                            <?php endif; ?>

                            <h1 class="doctor-name"><?php the_title(); ?></h1>

                            <?php if ($sub = get_field('subtitulo_profesional')): ?>
                                <p class="doctor-specialty-text"><?php echo esc_html($sub); ?></p>
                            <?php endif; ?>

                            <div class="doctor-stats-grid">
                                <div class="stat-box">
                                    <div class="stat-icon star"><span class="dashicons dashicons-star-filled"></span></div>
                                    <div class="stat-content">
                                        <span class="stat-label">CALIFICACIÓN</span>
                                        <span class="stat-value">
                                            <strong><?php echo get_field('rating_value') ?: '4.9'; ?></strong>
                                            (<?php echo get_field('rating_count') ?: '124'; ?> reseñas)
                                        </span>
                                    </div>
                                </div>
                                <div class="stat-box">
                                    <div class="stat-icon experience"><span class="dashicons dashicons-portfolio"></span>
                                    </div>
                                    <div class="stat-content">
                                        <span class="stat-label">EXPERIENCIA</span>
                                        <span
                                            class="stat-value"><strong><?php echo get_field('experiencia_texto') ?: '15+ Años'; ?></strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <section class="biography-section">
                        <h3>Biografía</h3>
                        <div class="bio-text">
                            <?php echo wp_kses_post(get_field('biografia_corta')); ?>
                        </div>
                    </section>

                    <section class="horarios-section-premium">
                        <div class="horarios-header">
                            <div class="title-area">
                                <span class="dashicons dashicons-clock"></span>
                                <h3>Horarios de Atención</h3>
                            </div>
                            <span class="status-badge green">DISPONIBLE PARA CITAS</span>
                        </div>

                        <div class="schedules-modern-grid">
                            <?php
                            $horarios = get_field('horarios_atencion');
                            if ($horarios): ?>
                                <?php foreach ($horarios as $h):
                                    $sede_val = isset($h['sede']) ? $h['sede'] : null;
                                    $sede_obj = is_numeric($sede_val) ? get_post($sede_val) : $sede_val;
                                    ?>
                                    <div class="schedule-card-item">
                                        <div class="day-time">
                                            <span class="day"><?php echo ucfirst($h['dia']); ?></span>
                                            <span class="time"><?php echo esc_html($h['hora_inicio']); ?> -
                                                <?php echo esc_html($h['hora_fin']); ?></span>
                                        </div>
                                        <?php if ($sede_obj): ?>
                                            <div class="sede-info">
                                                <span class="dashicons dashicons-location"></span>
                                                <?php echo esc_html($sede_obj->post_title); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <div class="schedule-card-item closed">
                                <div class="day-time">
                                    <span class="day">Sábado y Domingo</span>
                                    <span class="time">Cerrado</span>
                                </div>
                            </div>
                        </div>

                        <div class="booking-footer">
                            <?php
                            // Obtener el producto de consulta vinculado desde ACF
                            $producto_id = get_field('producto_consulta');
                            $url_reserva = $producto_id ? get_permalink($producto_id) : '#';
                            ?>
                            <a href="<?php echo esc_url($url_reserva); ?>" class="btn-book-now">
                                <span class="dashicons dashicons-calendar-alt"></span>
                                RESERVAR UNA CITA
                            </a>
                        </div>
                    </section>
                </main>

                <aside class="medico-sidebar">
                    <div class="sidebar-card contact-card">
                        <h3>Información de Contacto</h3>
                        <div class="contact-rows">
                            <div class="contact-row">
                                <div class="icon-circle"><span class="dashicons dashicons-phone"></span></div>
                                <div class="text">
                                    <span class="label">TELÉFONO</span>
                                    <span
                                        class="value"><?php echo get_field('contacto_telefono') ?: '+1 (555) 000-0000'; ?></span>
                                </div>
                            </div>
                            <div class="contact-row">
                                <div class="icon-circle"><span class="dashicons dashicons-email"></span></div>
                                <div class="text">
                                    <span class="label">CORREO ELECTRÓNICO</span>
                                    <span
                                        class="value"><?php echo get_field('contacto_email') ?: 'info@medical.com'; ?></span>
                                </div>
                            </div>
                            <div class="contact-row">
                                <div class="icon-circle"><span class="dashicons dashicons-location"></span></div>
                                <div class="text">
                                    <span class="label">UBICACIÓN</span>
                                    <span
                                        class="value"><?php echo get_field('clinica_direccion') ?: 'Sede Central'; ?></span>
                                    <a href="<?php echo esc_url(get_field('mapa_link')); ?>" class="map-link">Ver en el mapa
                                        <span class="dashicons dashicons-external"></span></a>
                                </div>
                            </div>
                        </div>
                        <div class="static-map">
                            <img src="https://api.mapbox.com/styles/v1/mapbox/light-v10/static/-58.42065,-34.58231,13,0/400x200?access_token=none"
                                alt="Vista del Mapa" style="width:100%; border-radius: 12px; margin-top: 15px;">
                        </div>
                    </div>

                    <div class="sidebar-card related-card">
                        <h3>Médicos Relacionados</h3>
                        <div class="related-list">
                            <?php
                            $related = get_field('medicos_relacionados');
                            if ($related): ?>
                                <?php foreach ($related as $rdoc): ?>
                                    <a href="<?php echo get_permalink($rdoc->ID); ?>" class="related-doctor-item">
                                        <div class="thumb">
                                            <?php echo get_the_post_thumbnail($rdoc->ID, 'thumbnail'); ?>
                                        </div>
                                        <div class="details">
                                            <span class="name"><?php echo esc_html($rdoc->post_title); ?></span>
                                            <span class="spec"><?php
                                            $rterms = get_the_terms($rdoc->ID, 'especialidad');
                                            echo $rterms ? esc_html($rterms[0]->name) : 'Especialista';
                                            ?></span>
                                        </div>
                                        <span class="dashicons dashicons-arrow-right-alt2"></span>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <a href="/medicos/" class="btn-all-doctors">Ver Todos los Médicos</a>
                    </div>
                </aside>
            </div>
            <?php
        endwhile;
        ?>
    </div>
</main>

<style>
    :root {
        --medical-blue: #615EFC;
        --medical-light-blue: #F0F4FF;
        --medical-text-dark: #2D3142;
        --medical-text-gray: #718096;
        --medical-white: #FFFFFF;
        --medical-green: #38A169;
        --medical-bg: #F8F9FD;
        --border-radius-lg: 24px;
        --border-radius-md: 16px;
        --shadow-premium: 0 10px 30px rgba(97, 94, 252, 0.08);
    }

    .site-main {
        background: var(--medical-bg);
        padding-bottom: 80px;
    }

    .medico-header-bg {
        height: 250px;
        background: var(--medical-blue);
        background: linear-gradient(135deg, var(--medical-blue) 0%, #8B89FF 100%);
        margin-bottom: -150px;
    }

    .medico-profile-layout {
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 30px;
        max-width: 1200px;
        margin: 0 auto;
        position: relative;
        z-index: 2;
    }

    /* Main Content Area */
    .medico-main-content {
        display: flex;
        flex-direction: column;
        gap: 30px;
    }

    /* Header Section */
    .profile-header-premium {
        display: flex;
        gap: 40px;
        background: var(--medical-white);
        padding: 40px;
        border-radius: var(--border-radius-lg);
        box-shadow: var(--shadow-premium);
        align-items: center;
    }

    .profile-photo-area {
        flex: 0 0 280px;
    }

    .profile-photo-area img {
        width: 100%;
        height: 350px;
        object-fit: cover;
        border-radius: var(--border-radius-md);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
    }

    .profile-info-area {
        flex: 1;
    }

    .certification-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: var(--medical-blue);
        font-weight: 700;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 15px;
        background: var(--medical-light-blue);
        padding: 6px 15px;
        border-radius: 30px;
    }

    .doctor-name {
        font-size: 2.8rem;
        font-weight: 800;
        color: var(--medical-text-dark);
        margin: 0 0 10px;
        line-height: 1.1;
    }

    .doctor-specialty-text {
        font-size: 1.2rem;
        color: var(--medical-text-gray);
        margin-bottom: 30px;
        font-weight: 500;
    }

    .doctor-stats-grid {
        display: flex;
        gap: 20px;
    }

    .stat-box {
        display: flex;
        align-items: center;
        gap: 15px;
        background: var(--medical-bg);
        padding: 15px 20px;
        border-radius: var(--border-radius-md);
        flex: 1;
        transition: transform 0.3s ease;
    }

    .stat-box:hover {
        transform: translateY(-5px);
    }

    .stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--medical-white);
        color: var(--medical-blue);
        font-size: 18px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    }

    .stat-label {
        display: block;
        font-size: 0.7rem;
        color: var(--medical-text-gray);
        font-weight: 700;
        letter-spacing: 0.5px;
        margin-bottom: 2px;
    }

    .stat-value {
        font-size: 0.9rem;
        color: var(--medical-text-dark);
        font-weight: 600;
    }

    /* Biography */
    .biography-section {
        background: var(--medical-white);
        padding: 40px;
        border-radius: var(--border-radius-lg);
        box-shadow: var(--shadow-premium);
    }

    .biography-section h3 {
        font-size: 1.4rem;
        margin-bottom: 20px;
        color: var(--medical-text-dark);
        font-weight: 700;
    }

    .bio-text {
        font-size: 1rem;
        line-height: 1.7;
        color: var(--medical-text-gray);
    }

    /* Schedules Section */
    .horarios-section-premium {
        background: var(--medical-white);
        padding: 40px;
        border-radius: var(--border-radius-lg);
        box-shadow: var(--shadow-premium);
    }

    .horarios-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }

    .horarios-header .title-area {
        display: flex;
        align-items: center;
        gap: 12px;
        color: var(--medical-text-dark);
    }

    .horarios-header .title-area h3 {
        margin: 0;
        font-size: 1.4rem;
        font-weight: 700;
    }

    .horarios-header .title-area .dashicons {
        color: var(--medical-blue);
        font-size: 24px;
        width: 24px;
        height: 24px;
    }

    .status-badge {
        padding: 6px 14px;
        border-radius: 30px;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .status-badge.green {
        background: #E6FFFA;
        color: var(--medical-green);
    }

    .schedules-modern-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 40px;
    }

    .schedule-card-item {
        background: var(--medical-bg);
        padding: 20px;
        border-radius: var(--border-radius-md);
        display: flex;
        flex-direction: column;
        gap: 15px;
        border: 1px solid transparent;
        transition: all 0.3s ease;
    }

    .schedule-card-item:hover {
        background: var(--medical-white);
        border-color: var(--medical-blue);
        box-shadow: 0 10px 20px rgba(97, 94, 252, 0.05);
    }

    .schedule-card-item.closed {
        background: #F4F7FE;
        opacity: 0.8;
    }

    .schedule-card-item .day {
        display: block;
        font-size: 1rem;
        font-weight: 700;
        color: var(--medical-text-dark);
    }

    .schedule-card-item .time {
        display: block;
        font-size: 0.9rem;
        color: var(--medical-text-gray);
        font-weight: 500;
    }

    .sede-info {
        font-size: 0.8rem;
        color: var(--medical-blue);
        display: flex;
        align-items: center;
        gap: 6px;
        font-weight: 600;
    }

    .btn-book-now {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        background: var(--medical-blue);
        color: white;
        text-decoration: none;
        padding: 22px;
        border-radius: 20px;
        font-weight: 700;
        letter-spacing: 1px;
        font-size: 0.95rem;
        box-shadow: 0 15px 30px rgba(97, 94, 252, 0.25);
        transition: all 0.3s ease;
    }

    .btn-book-now:hover {
        transform: translateY(-3px);
        box-shadow: 0 20px 40px rgba(97, 94, 252, 0.35);
    }

    /* Sidebar Content */
    .medico-sidebar {
        display: flex;
        flex-direction: column;
        gap: 30px;
    }

    .sidebar-card {
        background: var(--medical-white);
        padding: 30px;
        border-radius: var(--border-radius-lg);
        box-shadow: var(--shadow-premium);
    }

    .sidebar-card h3 {
        font-size: 1.25rem;
        margin-bottom: 25px;
        color: var(--medical-text-dark);
        font-weight: 700;
        padding-bottom: 15px;
        border-bottom: 2px solid var(--medical-bg);
    }

    .contact-row {
        display: flex;
        gap: 15px;
        margin-bottom: 25px;
    }

    .icon-circle {
        width: 42px;
        height: 42px;
        background: var(--medical-light-blue);
        color: var(--medical-blue);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .contact-row .label {
        display: block;
        font-size: 0.65rem;
        color: var(--medical-text-gray);
        font-weight: 700;
        margin-bottom: 4px;
        letter-spacing: 0.5px;
    }

    .contact-row .value {
        display: block;
        font-size: 0.9rem;
        color: var(--medical-text-dark);
        font-weight: 600;
        word-break: break-word;
    }

    .map-link {
        color: var(--medical-blue);
        font-size: 0.8rem;
        font-weight: 700;
        text-decoration: none;
        margin-top: 8px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    /* Related Doctors List */
    .related-doctor-item {
        display: flex;
        align-items: center;
        gap: 15px;
        text-decoration: none;
        padding: 15px 0;
        border-bottom: 1px solid var(--medical-bg);
        transition: all 0.2s ease;
    }

    .related-doctor-item:last-child {
        border-bottom: none;
    }

    .related-doctor-item:hover {
        transform: translateX(5px);
    }

    .related-doctor-item .thumb img {
        width: 55px;
        height: 55px;
        border-radius: 12px;
        object-fit: cover;
    }

    .related-doctor-item .name {
        display: block;
        font-weight: 700;
        color: var(--medical-text-dark);
        font-size: 0.9rem;
        margin-bottom: 2px;
    }

    .related-doctor-item .spec {
        font-size: 0.8rem;
        color: var(--medical-text-gray);
    }

    .related-doctor-item .dashicons {
        margin-left: auto;
        color: #CBD5E0;
        font-size: 16px;
    }

    .btn-all-doctors {
        display: block;
        text-align: center;
        margin-top: 20px;
        padding: 14px;
        background: var(--medical-bg);
        border-radius: 12px;
        color: var(--medical-blue);
        text-decoration: none;
        font-weight: 700;
        font-size: 0.85rem;
        transition: background 0.3s ease;
    }

    .btn-all-doctors:hover {
        background: var(--medical-light-blue);
    }

    @media (max-width: 1100px) {
        .medico-profile-layout {
            grid-template-columns: 1fr;
            padding: 20px;
        }

        .profile-header-premium {
            flex-direction: column;
            text-align: center;
            padding: 30px;
        }

        .doctor-stats-grid {
            justify-content: center;
        }

        .schedules-modern-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 600px) {
        .schedules-modern-grid {
            grid-template-columns: 1fr;
        }

        .doctor-stats-grid {
            flex-direction: column;
        }

        .doctor-name {
            font-size: 2rem;
        }

        .profile-photo-area {
            flex: 0 0 auto;
            width: 100%;
        }
    }
</style>

<?php
get_footer();
