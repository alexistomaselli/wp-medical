<?php
/**
 * Plugin Name: WooCommerce Login Restriction
 * Description: Restringe la compra y muestra un mensaje de advertencia si el usuario no ha iniciado sesión.
 * Version: 1.2.0
 * Author: Alexis Tomaselli
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 1. Muestra el mensaje informativo.
 * IMPORTANTE: Usamos 'woocommerce_single_product_summary' con prioridad 25.
 * Cambiamos el hook porque 'woocommerce_before_add_to_cart_button' NO se ejecuta 
 * si el producto no es comprable (is_purchasable = false).
 */
add_action('woocommerce_single_product_summary', function () {
    if (!is_user_logged_in()) {
        echo '<div class="wc-login-restriction-alert" style="padding: 15px; background-color: #fff3cd; border: 1px solid #ffeeba; color: #856404; border-radius: 4px; margin-bottom: 20px; font-weight: 500; clear: both;">';
        echo '⚠️ Debes iniciar sesión para comprar';
        echo '</div>';
    }
}, 25);

/**
 * 2. REGLA DE NEGOCIO: Bloquea la capacidad de compra de forma lógica.
 * El uso de prioridad 9999 asegura que sobreescriba cualquier otra lógica.
 * Este filtro hará que WooCommerce oculte el botón de "Agregar al carrito" automáticamente.
 */
add_filter('woocommerce_is_purchasable', function ($is_purchasable) {
    if (!is_user_logged_in()) {
        return false;
    }
    return $is_purchasable;
}, 9999);

/**
 * 3. SEGURIDAD EXTRA: Oculta el selector de cantidad y precios en algunos temas.
 */
add_filter('woocommerce_is_sold_individually', function ($return, $product) {
    if (!is_user_logged_in()) {
        return true;
    }
    return $return;
}, 10, 2);
