<?php
/**
 * The template for displaying Medico archives
 *
 * @package Medical_Health
 */

get_header();

// Get dynamic stats for header
$count_medicos = wp_count_posts('medico')->publish;
$count_sedes = wp_count_posts('sede')->publish;
?>

<div class="medico-archive-container">
    <!-- Hero Section -->
    <section class="medico-archive-hero">
        <div class="hero-bg-elements">
            <div class="element-1"></div>
            <div class="element-2"></div>
        </div>
        <div class="content-wrapper">
            <div class="hero-content">
                <span class="hero-badge">Staff Médico</span>
                <h1 class="hero-title">Encuentra al Especialista <br> <span class="highlight">Ideal para Ti.</span></h1>

                <div class="hero-stats">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <span class="material-symbols-outlined">groups</span>
                        </div>
                        <div class="stat-info">
                            <span class="stat-number"><?php echo esc_html($count_medicos); ?>+</span>
                            <span class="stat-label">Especialistas</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <span class="material-symbols-outlined">home_health</span>
                        </div>
                        <div class="stat-info">
                            <span class="stat-number"><?php echo esc_html($count_sedes); ?></span>
                            <span class="stat-label">Sedes</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Filters Section -->
    <section class="medico-filters-section">
        <div class="content-wrapper">
            <div class="medico-filters-wrapper">
                <form action="<?php echo esc_url(get_post_type_archive_link('medico')); ?>" method="get">
                    <div class="filter-group">
                        <label>Buscar por Nombre</label>
                        <div class="input-with-icon">
                            <span class="material-symbols-outlined">search</span>
                            <input type="text" name="s" placeholder="Nombre del médico..."
                                value="<?php echo get_search_query(); ?>">
                        </div>
                    </div>

                    <div class="filter-group">
                        <label>Especialidad</label>
                        <div class="select-with-icon">
                            <select name="especialidad_filter" id="especialidad_filter">
                                <option value="">Todas las especialidades</option>
                                <?php
                                $specialties = get_terms(array('taxonomy' => 'especialidad', 'hide_empty' => false));
                                foreach ($specialties as $specialty) {
                                    $selected = (isset($_GET['especialidad_filter']) && $_GET['especialidad_filter'] == $specialty->slug) ? 'selected' : '';
                                    echo '<option value="' . esc_attr($specialty->slug) . '" ' . $selected . '>' . esc_html($specialty->name) . '</option>';
                                }
                                ?>
                            </select>
                            <span class="material-symbols-outlined">expand_more</span>
                        </div>
                    </div>

                    <div class="filter-group">
                        <label>Ubicación (Sede)</label>
                        <div class="select-with-icon">
                            <select name="sede_filter" id="sede_filter">
                                <option value="">Todas las sedes</option>
                                <?php
                                $sedes = get_posts(array('post_type' => 'sede', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC'));
                                foreach ($sedes as $sede) {
                                    $selected = (isset($_GET['sede_filter']) && $_GET['sede_filter'] == $sede->ID) ? 'selected' : '';
                                    echo '<option value="' . esc_attr($sede->ID) . '" ' . $selected . '>' . esc_html($sede->post_title) . '</option>';
                                }
                                ?>
                            </select>
                            <span class="material-symbols-outlined">location_on</span>
                        </div>
                    </div>

                    <div class="filter-submit">
                        <button type="submit">
                            <span class="material-symbols-outlined">tune</span>
                            Filtrar
                        </button>
                    </div>
                </form>
            </div>

            <?php if (isset($_GET['especialidad_filter']) && !empty($_GET['especialidad_filter'])): ?>
                <div class="active-filter-pill">
                    <p>Mostrando resultados para:
                        <strong><?php echo esc_html(get_term_by('slug', $_GET['especialidad_filter'], 'especialidad')->name); ?></strong>
                    </p>
                    <a href="<?php echo esc_url(get_post_type_archive_link('medico')); ?>" class="clear-filters">
                        Limpiar <span class="material-icons">close</span>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Grid Section -->
    <section class="medico-grid-section">
        <div class="content-wrapper">
            <div class="medico-grid-archive">
                <?php
                if (have_posts()):
                    while (have_posts()):
                        the_post();
                        $foto = get_field('foto_perfil');
                        $subtitulo = get_field('subtitulo_profesional');

                        // Obtener la sede desde los horarios
                        $horarios = get_field('horarios_atencion');
                        $sede_nombre = 'Sede Central';
                        if ($horarios && isset($horarios[0]['sede'])) {
                            $sede_id = is_numeric($horarios[0]['sede']) ? $horarios[0]['sede'] : $horarios[0]['sede']->ID;
                            $sede_obj = get_post($sede_id);
                            if ($sede_obj)
                                $sede_nombre = $sede_obj->post_title;
                        }

                        $disponibilidad = $sede_nombre;

                        // Determinar URL de la foto
                        $foto_url = 'https://via.placeholder.com/400x500?text=Médico';
                        if ($foto) {
                            if (is_array($foto))
                                $foto_url = $foto['url'];
                            elseif (is_numeric($foto))
                                $foto_url = wp_get_attachment_url($foto);
                            elseif (is_object($foto))
                                $foto_url = wp_get_attachment_url($foto->ID);
                            else
                                $foto_url = $foto;
                        }
                        ?>
                        <article class="doctor-card">
                            <div class="doctor-image-wrapper">
                                <a href="<?php the_permalink(); ?>" class="doctor-image">
                                    <img src="<?php echo esc_url($foto_url); ?>" alt="<?php the_title_attribute(); ?>">
                                </a>
                                <div class="availability-badge">
                                    <?php echo esc_html($disponibilidad); ?>
                                </div>
                            </div>
                            <div class="doctor-info">
                                <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                <p class="specialty">
                                    <?php
                                    $terms = get_the_terms(get_the_ID(), 'especialidad');
                                    echo ($terms && !is_wp_error($terms)) ? esc_html($terms[0]->name) : 'Especialista';
                                    ?>
                                </p>
                                <a href="<?php the_permalink(); ?>" class="view-profile-btn">
                                    Ver Perfil <span class="material-icons">arrow_forward</span>
                                </a>
                            </div>
                        </article>
                        <?php
                    endwhile;
                else:
                    echo '<p class="no-results">No se encontraron médicos con ese criterio.</p>';
                endif;
                ?>
            </div>

            <div class="archive-pagination">
                <?php the_posts_pagination(array(
                    'prev_text' => '<span class="material-icons">chevron_left</span>',
                    'next_text' => '<span class="material-icons">chevron_right</span>',
                )); ?>
            </div>
        </div>
</div>
</section>

<!-- CTA Section -->
<section class="medico-cta-section">
    <div class="container--wide">
        <div class="cta-content">
            <h2 class="cta-title">¿Listo para recibir la <br />mejor atención médica?</h2>
            <p class="cta-description">
                Conectá con nuestros especialistas hoy mismo y comenzá tu camino hacia una mejor salud con nuestros
                programas de atención especializada.
            </p>
            <div class="cta-actions">
                <a href="#" class="btn-primary-cta">
                    Agendar un Turno
                </a>
                <a href="<?php echo esc_url(site_url('/contacto')); ?>" class="btn-secondary-cta">
                    Contactar Recepción
                </a>
            </div>
        </div>
    </div>

    <!-- Elementos decorativos -->
    <span class="material-icons cta-icon icon-left">health_and_safety</span>
    <span class="material-icons cta-icon icon-right">stethoscope</span>
</section>
</div>

<?php
get_footer();
