<?php
/**
 * The template for displaying all pages
 *
 * @package Medical
 */

get_header();
?>

<main id="primary" class="site-main">
    <div class="container container-page py-10">
        <?php
        while (have_posts()):
            the_post();
            ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <header class="entry-header mb-8">
                    <?php the_title('<h1 class="entry-title text-3xl font-bold">', '</h1>'); ?>
                </header>

                <div class="entry-content">
                    <?php
                    the_content();

                    wp_link_pages(array(
                        'before' => '<div class="page-links">' . esc_html__('Pages:', 'medical-theme'),
                        'after' => '</div>',
                    ));
                    ?>
                </div>
            </article>
            <?php
        endwhile;
        ?>
    </div>
</main>

<?php
get_footer();
