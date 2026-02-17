<?php
/**
 * The template for displaying Medico archives
 *
 * @package Medical_Health
 */

get_header();
?>

<div class="content-wrapper">
    <header class="page-header">
        <h1 class="page-title">Nuestro Staff Médico</h1>

        <div class="medico-filters">
            <form action="<?php echo esc_url(get_post_type_archive_link('medico')); ?>" method="get">
                <select name="especialidad_filter" id="especialidad_filter">
                    <option value="">Todas las especialidades</option>
                    <?php
                    $specialties = get_terms(array(
                        'taxonomy' => 'especialidad',
                        'hide_empty' => false,
                    ));
                    foreach ($specialties as $specialty) {
                        $selected = (isset($_GET['especialidad_filter']) && $_GET['especialidad_filter'] == $specialty->slug) ? 'selected' : '';
                        echo '<option value="' . esc_attr($specialty->slug) . '" ' . $selected . '>' . esc_html($specialty->name) . '</option>';
                    }
                    ?>
                </select>

                <select name="sede_filter" id="sede_filter">
                    <option value="">Todas las sedes</option>
                    <?php
                    $sedes = get_posts(array(
                        'post_type' => 'sede',
                        'posts_per_page' => -1,
                        'orderby' => 'title',
                        'order' => 'ASC',
                    ));
                    foreach ($sedes as $sede) {
                        $selected = (isset($_GET['sede_filter']) && $_GET['sede_filter'] == $sede->ID) ? 'selected' : '';
                        echo '<option value="' . esc_attr($sede->ID) . '" ' . $selected . '>' . esc_html($sede->post_title) . '</option>';
                    }
                    ?>
                </select>
                <button type="submit">Filtrar</button>
            </form>
        </div>
    </header>

    <div class="medico-grid-archive">
        <?php
        if (have_posts()):
            while (have_posts()):
                the_post();
                // We'll show a simple card for each doctor
                ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class('medico-card'); ?>>
                    <div class="medico-card-image">
                        <?php the_post_thumbnail('medium'); ?>
                    </div>
                    <div class="medico-card-content">
                        <h2><a href="<?php the_permalink(); ?>">
                                <?php the_title(); ?>
                            </a></h2>
                        <?php
                        $terms = get_the_terms(get_the_ID(), 'especialidad');
                        if ($terms && !is_wp_error($terms)):
                            echo '<p class="medico-specialty">' . esc_html($terms[0]->name) . '</p>';
                        endif;
                        ?>
                        <a href="<?php the_permalink(); ?>" class="view-profile">Ver Perfil</a>
                    </div>
                </article>
                <?php
            endwhile;
            the_posts_navigation();
        else:
            echo '<p>No se encontraron médicos con ese criterio.</p>';
        endif;
        ?>
    </div>
</div>

<style>
    .medico-grid-archive {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 30px;
        padding: 40px 0;
    }

    .medico-card {
        background: var(--white);
        border: none;
        border-radius: var(--border-radius);
        overflow: hidden;
        transition: all 0.3s ease;
        box-shadow: var(--shadow-soft);
        display: flex;
        flex-direction: column;
    }

    .medico-card:hover {
        transform: translateY(-10px);
        box-shadow: var(--shadow-hover);
    }

    .medico-card-image img {
        width: 100%;
        height: 300px;
        object-fit: cover;
        border-bottom: 5px solid var(--primary-color);
    }

    .medico-card-content {
        padding: 25px;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
        text-align: center;
    }

    .medico-card-content h2 {
        margin: 0 0 10px 0;
        font-size: 1.4rem;
    }

    .medico-card-content h2 a {
        text-decoration: none;
        color: var(--text-heading);
    }

    .medico-specialty {
        color: var(--primary-color);
        font-family: var(--font-body);
        font-weight: 500;
        font-size: 0.95rem;
        margin-bottom: 20px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .view-profile {
        margin-top: auto;
    }

    /* Filters Styling */
    .medico-filters {
        margin-bottom: 40px;
        background: var(--white);
        padding: 25px;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-soft);
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .medico-filters select {
        padding: 12px 20px;
        border-radius: var(--border-radius);
        border: 1px solid #eee;
        font-family: var(--font-body);
        margin-right: 15px;
        min-width: 250px;
        outline: none;
    }

    .medico-filters button {
        /* Styles inherited from global button */
    }

    .page-title {
        text-align: center;
        margin-bottom: 40px;
        font-size: 2.5rem;
        position: relative;
    }

    .page-title::after {
        content: '';
        display: block;
        width: 60px;
        height: 4px;
        background: var(--primary-color);
        margin: 15px auto 0;
        border-radius: 2px;
    }
</style>

<?php
get_footer();
