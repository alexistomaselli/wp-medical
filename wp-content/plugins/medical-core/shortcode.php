<?php
/**
 * Shortcode: Mensaje de Bienvenida
 */
if (!defined('ABSPATH')) {
    exit;
}

function welcome_message_shortcode()
{
    if (is_user_logged_in()) {
        $current_user = wp_get_current_user();
        $message = "¡Bienvenido , " . esc_html($current_user->display_name) . "!";
    } else {
        $message = "¡Buenos Dias!";
    }

    return '<div>' . $message . '</div>';
}
add_shortcode('welcome_message', 'welcome_message_shortcode');
