<?php
/**
 * Medical functions and definitions
 *
 * @package Medical
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}


/**
 * Helper function to check if WooCommerce is activated
 */
function medical_is_woocommerce_activated()
{
    return class_exists('WooCommerce');
}

/**
 * Display admin notice if WooCommerce is not activated
 */
function medical_woocommerce_admin_notice()
{
    if (!medical_is_woocommerce_activated()) {
        ?>
        <div class="notice notice-error is-dismissible">
            <p><?php _e('<strong>Medical Theme:</strong> Este tema requiere que el plugin <strong>WooCommerce</strong> esté instalado y activado para funcionar correctamente.', 'medical-theme'); ?>
            </p>
        </div>
        <?php
    }
}
add_action('admin_notices', 'medical_woocommerce_admin_notice');

/**
 * Setup Theme
 */
function medical_setup()
{
    // Add default posts and comments RSS feed links to head.
    add_theme_support('automatic-feed-links');

    // Let WordPress manage the document title.
    add_theme_support('title-tag');

    // Load text domain for translations
    load_theme_textdomain('medical-theme', get_template_directory() . '/languages');

    // Enable support for Post Thumbnails on posts and pages.
    add_theme_support('post-thumbnails');

    // Switch default core markup for search form, comment form, and comments to output valid HTML5.
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ));

    // Add WooCommerce support
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
}
add_action('after_setup_theme', 'medical_setup');

/**
 * Enqueue scripts and styles.
 */
function medical_scripts()
{
    // Google Fonts
    wp_enqueue_style('medical-theme-fonts', 'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Rubik:wght@300;400;500;600;700&display=swap', array(), null);

    // Material Icons
    wp_enqueue_style('material-icons', 'https://fonts.googleapis.com/icon?family=Material+Icons', array(), null);
    wp_enqueue_style('material-symbols', 'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1', array(), null);

    // Enqueue Dashicons for the cart icon
    wp_enqueue_style('dashicons');

    // Check if Vite dev server is running (port 3000)
    $vite_dev_server = @file_get_contents('http://127.0.0.1:3000');
    $is_vite_dev = $vite_dev_server !== false;

    // Use filemtime for versioning to break cache in production
    $theme_version = wp_get_theme()->get('Version');
    $css_version = file_exists(get_stylesheet_directory() . '/style.css')
        ? filemtime(get_stylesheet_directory() . '/style.css')
        : $theme_version;

    if ($is_vite_dev) {
        // Desarrollo: cargar desde Vite dev server
        wp_enqueue_script('vite-client', 'http://127.0.0.1:3000/@vite/client', array(), null, false);
        wp_enqueue_style('medical-theme-style-dev', 'http://127.0.0.1:3000/assets/scss/style.scss', array('woocommerce-general'), null);
    } else {
        // Producción: cargar CSS compilado
        wp_enqueue_style('medical-theme-style', get_stylesheet_uri(), array('woocommerce-general'), $css_version);
    }

    // Enqueue navigation script for mobile menu
    wp_enqueue_script('medical-navigation', get_template_directory_uri() . '/assets/js/navigation.js', array(), $theme_version, true);

    // Enqueue cart script on cart page
    // Enqueue cart script on cart page
    if (medical_is_woocommerce_activated() && is_cart()) {
        wp_enqueue_script('medical-cart', get_template_directory_uri() . '/assets/js/cart.js', array('jquery'), null, true);
    }

    // Video Modal Script
    wp_enqueue_script('medical-modal', get_template_directory_uri() . '/assets/js/modal.js', array(), $theme_version, true);
}
add_action('wp_enqueue_scripts', 'medical_scripts', 20); // High priority to ensure it loads after plugins

// Remove duplicate or non-standard translation filters if needed



/**
 * Core Business Logic
 * 
 * The logic for ACF dependencies, Booking System, Custom Checkout Fields,
 * and Block Registration has been moved to the 'Medical Theme Core' plugin.
 * 
 * @see wp-content/plugins/medical-core/medical-core.php
 */

/**
 * Translate specific strings that might be missing
 */
add_filter('gettext', 'medical_translate_woocommerce_strings', 20, 3);
function medical_translate_woocommerce_strings($translated_text, $text, $domain)
{
    $case_text = strtolower($text);
    switch ($case_text) {
        case 'proceed to checkout':
            $translated_text = 'FINALIZAR COMPRA';
            break;
        case 'update cart':
            $translated_text = 'ACTUALIZAR CARRITO';
            break;
        case 'cart totals':
            $translated_text = 'TOTALES DEL CARRITO';
            break;
        case 'product':
        case 'producto':
            $translated_text = 'PRODUCTO';
            break;
        case 'price':
        case 'precio':
            $translated_text = 'PRECIO';
            break;
        case 'quantity':
        case 'cantidad':
            $translated_text = 'CANTIDAD';
            break;
        case 'subtotal':
            $translated_text = 'SUBTOTAL';
            break;
        case 'total':
            $translated_text = 'TOTAL';
            break;
        case 'remove item':
        case 'remove this item':
        case 'eliminar este artículo':
            $translated_text = 'Eliminar';
            break;
        case 'coupon:':
        case 'cupón:':
            $translated_text = 'Cupón:';
            break;
        case 'apply coupon':
        case 'aplicar cupón':
            $translated_text = 'APLICAR CUPÓN';
            break;
        case 'billing details':
        case 'detalles de facturación':
            $translated_text = 'Detalles de Facturación';
            break;
        case 'place order':
        case 'realizar el pedido':
            $translated_text = 'Realizar Pedido';
            break;
        case 'your order':
            $translated_text = 'Tu Pedido';
            break;
        case 'first name':
            $translated_text = 'Nombre';
            break;
        case 'last name':
            $translated_text = 'Apellidos';
            break;
        case 'email address':
            $translated_text = 'Correo Electrónico';
            break;
        case 'phone':
            $translated_text = 'Teléfono';
            break;
        case 'street address':
            $translated_text = 'Dirección';
            break;
        case 'town / city':
            $translated_text = 'Localidad / Ciudad';
            break;
        case 'postcode / zip':
            $translated_text = 'Código Postal';
            break;
        case 'state / county':
            $translated_text = 'Provincia';
            break;
        case 'have a coupon?':
            $translated_text = '¿Tienes un cupón?';
            break;
        case 'click here to enter your code':
            $translated_text = 'Haz clic aquí para introducir tu código';
            break;
    }
    return $translated_text;
}

/**
 * Handle AJAX Cart Count Refresh
 */
/**
 * Handle AJAX Cart Count Refresh
 */
if (medical_is_woocommerce_activated()) {
    add_filter('woocommerce_add_to_cart_fragments', 'medical_cart_count_fragments', 10, 1);
}
function medical_cart_count_fragments($fragments)
{
    ob_start();
    ?>
    <span class="cart-contents-count"><?php echo WC()->cart->get_cart_contents_count(); ?></span>
    <?php
    $fragments['span.cart-contents-count'] = ob_get_clean();
    return $fragments;
}

/**
 * Desactivar el modo 'Coming Soon' de WooCommerce por defecto
 */
if (medical_is_woocommerce_activated()) {
    update_option('woocommerce_coming_soon', 'no');
    update_option('woocommerce_store_pages_only', 'no');
}

/**
 * Customizer Additions
 */
require get_template_directory() . '/inc/customizer.php';


