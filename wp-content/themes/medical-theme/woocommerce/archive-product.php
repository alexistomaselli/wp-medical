<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * @package Medical
 */

get_header();
?>

<main id="primary" class="site-main">
    <div class="woocommerce-container">
        <?php
        if (woocommerce_product_loop()) {

            /**
             * Hook: woocommerce_before_shop_loop.
             */
            do_action('woocommerce_before_shop_loop');

            woocommerce_product_loop_start();

            if (wc_get_loop_prop('total')) {
                while (have_posts()) {
                    the_post();
                    /**
                     * Hook: woocommerce_shop_loop.
                     */
                    do_action('woocommerce_shop_loop');

                    wc_get_template_part('content', 'product');
                }
            }

            woocommerce_product_loop_end();

            /**
             * Hook: woocommerce_after_shop_loop.
             */
            do_action('woocommerce_after_shop_loop');
        } else {
            /**
             * Hook: woocommerce_no_products_found.
             */
            do_action('woocommerce_no_products_found');
        }
        ?>
    </div>
</main>

<?php
get_footer();
