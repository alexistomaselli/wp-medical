<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register Theme Customizer settings
 */
function medical_customize_register($wp_customize)
{
    // Section for Home Settings
    $wp_customize->add_section('medical_theme_options', array(
        'title' => __('Configuración de la Home', 'medical-theme'),
        'priority' => 30,
        'description' => __('Opciones personalizadas para la página de inicio del tema Medical.', 'medical-theme'),
    ));

    // Video URL Setting
    $wp_customize->add_setting('medical_video_promocional', array(
        'default' => '',
        'transport' => 'refresh',
        'sanitize_callback' => 'esc_url_raw',
    ));

    // Video Upload Control
    $wp_customize->add_control(new WP_Customize_Upload_Control($wp_customize, 'medical_video_promocional', array(
        'label' => __('Video Promocional', 'medical-theme'),
        'description' => __('Selecciona un video (MP4) para mostrar en el modal de la página de inicio.', 'medical-theme'),
        'section' => 'medical_theme_options',
        'settings' => 'medical_video_promocional',
        'mime_type' => 'video',
    )));

    // Video Poster Setting
    $wp_customize->add_setting('medical_video_poster', array(
        'default' => '',
        'transport' => 'refresh',
        'sanitize_callback' => 'esc_url_raw',
    ));

    // Video Poster Control
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'medical_video_poster', array(
        'label' => __('Imagen de Portada (Poster)', 'medical-theme'),
        'description' => __('Selecciona una imagen para mostrar antes de reproducir el video.', 'medical-theme'),
        'section' => 'medical_theme_options',
        'settings' => 'medical_video_poster',
    )));

    // Autoplay Behavior Setting
    $wp_customize->add_setting('medical_video_autoplay', array(
        'default' => 'none',
        'transport' => 'refresh',
        'sanitize_callback' => 'sanitize_text_field',
    ));

    // Autoplay Behavior Control
    $wp_customize->add_control('medical_video_autoplay', array(
        'label' => __('Comportamiento de Autoreproducción', 'medical-theme'),
        'description' => __('Elige cuándo debe abrirse automáticamente el video.', 'medical-theme'),
        'section' => 'medical_theme_options',
        'settings' => 'medical_video_autoplay',
        'type' => 'select',
        'choices' => array(
            'none' => __('Solo al hacer click (Desactivado)', 'medical-theme'),
            'always' => __('Siempre (Cada carga de página)', 'medical-theme'),
            'once' => __('Solo la primera vez (Por sesión)', 'medical-theme'),
        ),
    ));
}

add_action('customize_register', 'medical_customize_register');
