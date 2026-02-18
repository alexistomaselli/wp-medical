<?php
/**
 * Author: Alexis Tomaselli
 *
 * @package Medical_Health
 */

get_header();
?>

<main id="primary" class="site-main">

    <?php
    if (have_posts()):

        if (is_home() && !is_front_page()):
            ?>
            <header>
                <h1 class="page-title screen-reader-text">
                    <?php single_post_title(); ?>
                </h1>
            </header>
            <?php
        endif;

        /* Start the Loop */
        while (have_posts()):
            the_post();
            ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <header class="entry-header">
                    <?php the_title('<h2 class="entry-title">', '</h2>'); ?>
                </header>
                <div class="entry-content">
                    <?php the_excerpt(); ?>
                </div>
            </article>
            <?php
        endwhile;

        the_posts_navigation();

    else:
        echo '<p>No se encontraron resultados.</p>';
    endif;
    ?>

</main>

<?php
get_footer();
