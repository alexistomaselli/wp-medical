<?php
/**
 * Plugin Name: Medical Theme Core
 * Description: Lógica de negocio esencial para el tema Medical (Campos personalizados, Reservas, WooCommerce).
 * Version: 1.0.0
 * Author: Alexis Tomaselli
 * Package: Medical_Core
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check for ACF and show admin notice if missing, and fallback
 */
function medical_check_dependencies()
{
    if (!class_exists('ACF')) {
        add_action('admin_notices', function () {
            ?>
            <div class="notice notice-error">
                <p>
                    <?php _e('⚠️ El tema <strong>Medical</strong> requiere el plugin <strong>Advanced Custom Fields (ACF)</strong> para funcionar correctamente. Por favor instálalo y actívalo.', 'medical-theme'); ?>
                </p>
            </div>
            <?php
        });
    }
}
add_action('plugins_loaded', 'medical_check_dependencies'); // Changed to plugins_loaded for a plugin

/**
 * Fallback for ACF functions to prevent fatal errors
 */
if (!function_exists('get_field')) {
    function get_field($selector, $post_id = false, $format_value = true)
    {
        return null;
    }
}
if (!function_exists('the_field')) {
    function the_field($selector, $post_id = false, $format_value = true)
    {
        return null;
    }
}
if (!function_exists('have_rows')) {
    function have_rows($selector, $post_id = false)
    {
        return false;
    }
}
if (!function_exists('the_row')) {
    function the_row()
    {
        return false;
    }
}
if (!function_exists('get_sub_field')) {
    function get_sub_field($selector, $format_value = true)
    {
        return null;
    }
}
if (!function_exists('the_sub_field')) {
    function the_sub_field($selector, $format_value = true)
    {
        return null;
    }
}

/**
 * Enqueue Booking Scripts
 */
function medical_booking_scripts()
{
    if (is_singular('product')) {
        // Flatpickr for calendar
        wp_enqueue_style('flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css', array(), '4.6.13');
        wp_enqueue_script('flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.js', array(), '4.6.13', true);

        // Booking script (depends on jQuery and Flatpickr)
        // Note: Using get_template_directory_uri() assumes the script is in the theme.
        // It's coupled, but works for a theme-specific plugin.
        wp_enqueue_script('medical-booking', get_template_directory_uri() . '/assets/js/booking.js', array('jquery', 'flatpickr'), '1.0.1', true);

        // Get product and linked doctor info
        $product = wc_get_product(get_the_ID());
        $doctor_data = array();

        if ($product && $product->get_id()) {
            $product_id = $product->get_id();

            // Find linked doctor
            $args = array(
                'post_type' => 'medico',
                'meta_query' => array(
                    array(
                        'key' => 'producto_consulta',
                        'value' => $product_id,
                        'compare' => '='
                    )
                ),
                'posts_per_page' => 1
            );
            $medico_query = new WP_Query($args);

            if ($medico_query->have_posts()) {
                $medico_query->the_post();

                // Safely get ACF data
                $schedule = function_exists('get_field') ? get_field('horarios_atencion') : array();

                $doctor_data = array(
                    'id' => get_the_ID(),
                    'name' => get_the_title(),
                    'schedule' => $schedule
                );
                wp_reset_postdata();
            }
        }

        // Localize script with WooCommerce data
        if (function_exists('wc_get_checkout_url')) {
            wp_localize_script('medical-booking', 'medical_booking_params', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'checkout_url' => wc_get_checkout_url(),
                'nonce' => wp_create_nonce('medical_booking'),
                'doctor' => $doctor_data
            ));
        }
    }
}
add_action('wp_enqueue_scripts', 'medical_booking_scripts');

/**
 * Register ACF Blocks
 */
function medical_register_blocks()
{
    // Register block type from THEME directory if easier for now
    register_block_type(get_template_directory() . '/blocks/medico-info');
}
add_action('init', 'medical_register_blocks');

/**
 * Modify Main Query for Médico Filtering
 */
function medical_medico_archive_filter($query)
{
    if (!is_admin() && $query->is_main_query() && is_post_type_archive('medico')) {
        $meta_query = array();
        $tax_query = array();

        // Specialty Filter
        if (isset($_GET['especialidad_filter']) && !empty($_GET['especialidad_filter'])) {
            $tax_query[] = array(
                'taxonomy' => 'especialidad',
                'field' => 'slug',
                'terms' => sanitize_text_field($_GET['especialidad_filter']),
            );
        }

        // Sede Filter (ACF Post Object multiple)
        if (isset($_GET['sede_filter']) && !empty($_GET['sede_filter'])) {
            $sede_id = sanitize_text_field($_GET['sede_filter']);
            $meta_query[] = array(
                'key' => 'sedes_atencion',
                'value' => '"' . $sede_id . '"',
                'compare' => 'LIKE',
            );
        }

        if (!empty($tax_query)) {
            $query->set('tax_query', $tax_query);
        }

        if (!empty($meta_query)) {
            $query->set('meta_query', $meta_query);
        }
    }
}
add_action('pre_get_posts', 'medical_medico_archive_filter');

/**
 * WooCommerce Customizations
 */
if (class_exists('WooCommerce')) {

    // Customize Checkout Layout for AJAX
    // Note: These actions modify visual layout hooks. 
    // If stricly visual, could stay in Theme. But it's rearranging core checkout flow. Let's keep in plugin for robust checkout logic.
    remove_action('woocommerce_checkout_order_review', 'woocommerce_order_review', 10);
    remove_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);
    add_action('medical_checkout_payment', 'woocommerce_checkout_payment', 20);
    add_action('medical_checkout_order_review', 'woocommerce_order_review', 10);

    // Filter to update BOTH payment and review areas via fragments
    add_filter('woocommerce_update_order_review_fragments', 'medical_checkout_fragments');
    function medical_checkout_fragments($fragments)
    {
        ob_start();
        woocommerce_order_review();
        $fragments['.woocommerce-checkout-review-order-table'] = ob_get_clean();

        ob_start();
        woocommerce_checkout_payment();
        $fragments['.woocommerce-checkout-payment'] = ob_get_clean();

        return $fragments;
    }

    // Add and structure checkout fields
    add_filter('woocommerce_checkout_fields', 'medical_custom_checkout_fields');
    function medical_custom_checkout_fields($fields)
    {
        // Billing Fields Refinement & Translation
        $fields['billing']['billing_first_name']['label'] = __('Nombre', 'medical-theme');
        $fields['billing']['billing_last_name']['label'] = __('Apellidos', 'medical-theme');
        $fields['billing']['billing_company']['label'] = __('Nombre de la empresa (opcional)', 'medical-theme');
        $fields['billing']['billing_country']['label'] = __('País / Región', 'medical-theme');
        $fields['billing']['billing_address_1']['label'] = __('Dirección de la calle', 'medical-theme');
        $fields['billing']['billing_address_2']['label'] = __('Apartamento, habitación, etc. (opcional)', 'medical-theme');
        $fields['billing']['billing_city']['label'] = __('Localidad / Ciudad', 'medical-theme');
        $fields['billing']['billing_state']['label'] = __('Provincia', 'medical-theme');
        $fields['billing']['billing_postcode']['label'] = __('Código postal', 'medical-theme');
        $fields['billing']['billing_phone']['label'] = __('Teléfono', 'medical-theme');
        $fields['billing']['billing_email']['label'] = __('Dirección de correo electrónico', 'medical-theme');

        // Placeholders
        $fields['billing']['billing_first_name']['placeholder'] = '';
        $fields['billing']['billing_last_name']['placeholder'] = '';
        $fields['billing']['billing_address_1']['placeholder'] = __('Número de la casa y nombre de la calle', 'medical-theme');
        $fields['billing']['billing_address_2']['placeholder'] = __('Apartamento, habitación, unidad, etc. (opcional)', 'medical-theme');

        // Priorities
        $fields['billing']['billing_first_name']['priority'] = 10;
        $fields['billing']['billing_last_name']['priority'] = 20;
        $fields['billing']['billing_address_1']['priority'] = 30;
        $fields['billing']['billing_city']['priority'] = 40;
        $fields['billing']['billing_postcode']['priority'] = 50;
        $fields['billing']['billing_phone']['priority'] = 60;
        $fields['billing']['billing_email']['priority'] = 70;

        // Custom Patient Information Fields
        $fields['billing']['billing_patient_name'] = array(
            'label' => __('Nombre Completo del Paciente', 'medical-theme'),
            'placeholder' => __('Ingrese el nombre completo del paciente', 'medical-theme'),
            'required' => true,
            'class' => array('form-row-wide'),
            'clear' => true,
            'priority' => 100
        );

        $fields['billing']['billing_patient_dob'] = array(
            'label' => __('Fecha de Nacimiento', 'medical-theme'),
            'placeholder' => __('AAAA-MM-DD', 'medical-theme'),
            'type' => 'date',
            'required' => true,
            'class' => array('form-row-first'),
            'clear' => false,
            'priority' => 110
        );

        $fields['billing']['billing_patient_gender'] = array(
            'label' => __('Género', 'medical-theme'),
            'type' => 'select',
            'required' => true,
            'class' => array('form-row-last'),
            'options' => array(
                '' => __('Seleccionar género', 'medical-theme'),
                'male' => __('Masculino', 'medical-theme'),
                'female' => __('Femenino', 'medical-theme'),
                'other' => __('Otro', 'medical-theme')
            ),
            'priority' => 120
        );

        $fields['billing']['billing_dni'] = array(
            'label' => __('DNI del Paciente', 'medical-theme'),
            'placeholder' => _x('Ingrese el DNI para la historia clínica', 'placeholder', 'medical-theme'),
            'required' => true,
            'class' => array('form-row-wide'),
            'clear' => true,
            'priority' => 130
        );

        return $fields;
    }

    // Save Patient Fields to Order Meta
    add_action('woocommerce_checkout_update_order_meta', 'medical_save_patient_fields');
    function medical_save_patient_fields($order_id)
    {
        if (!empty($_POST['billing_patient_name'])) {
            update_post_meta($order_id, '_billing_patient_name', sanitize_text_field($_POST['billing_patient_name']));
        }
        if (!empty($_POST['billing_patient_dob'])) {
            update_post_meta($order_id, '_billing_patient_dob', sanitize_text_field($_POST['billing_patient_dob']));
        }
        if (!empty($_POST['billing_patient_gender'])) {
            update_post_meta($order_id, '_billing_patient_gender', sanitize_text_field($_POST['billing_patient_gender']));
        }
        if (!empty($_POST['billing_dni'])) {
            update_post_meta($order_id, '_billing_dni', sanitize_text_field($_POST['billing_dni']));
        }
    }

    // Display Patient Info in Admin Order Page
    add_action('woocommerce_admin_order_data_after_billing_address', 'medical_display_patient_info_admin', 10, 1);
    function medical_display_patient_info_admin($order)
    {
        echo '<h4>' . __('Patient Information') . '</h4>';
        echo '<p><strong>' . __('Nombre') . ':</strong> ' . get_post_meta($order->get_id(), '_billing_patient_name', true) . '</p>';
        echo '<p><strong>' . __('DNI') . ':</strong> ' . get_post_meta($order->get_id(), '_billing_dni', true) . '</p>';
        echo '<p><strong>' . __('Birth') . ':</strong> ' . get_post_meta($order->get_id(), '_billing_patient_dob', true) . '</p>';
        echo '<p><strong>' . __('Gender') . ':</strong> ' . get_post_meta($order->get_id(), '_billing_patient_gender', true) . '</p>';
    }

    // Autofill Checkout Fields from Booking Data
    add_filter('woocommerce_checkout_get_value', 'medical_autofill_checkout_fields', 10, 2);
    function medical_autofill_checkout_fields($value, $input)
    {
        if (!function_exists('WC') || is_null(WC()->cart)) {
            return $value;
        }

        foreach (WC()->cart->get_cart() as $cart_item) {
            if (isset($cart_item['patient_name'])) {
                // Autofill Billing First/Last Name from Patient Name if not set differently
                if ($input === 'billing_first_name') {
                    $names = explode(' ', $cart_item['patient_name']);
                    return isset($names[0]) ? $names[0] : (WC()->customer ? WC()->customer->get_billing_first_name() : $value);
                }

                // Patient Specific Fields
                if ($input === 'billing_patient_name')
                    return $cart_item['patient_name'];
                if ($input === 'billing_dni' && isset($cart_item['patient_dni']))
                    return $cart_item['patient_dni'];
                if ($input === 'billing_email' && isset($cart_item['patient_email']))
                    return $cart_item['patient_email'];
                if ($input === 'billing_phone' && isset($cart_item['patient_phone']))
                    return $cart_item['patient_phone'];
            }
        }
        return $value;
    }

    // === BOOKING SYSTEM ===

    // AJAX: Add booking to cart
    add_action('wp_ajax_medical_add_booking_to_cart', 'medical_add_booking_to_cart');
    add_action('wp_ajax_nopriv_medical_add_booking_to_cart', 'medical_add_booking_to_cart');
    function medical_add_booking_to_cart()
    {
        check_ajax_referer('medical_booking', 'nonce');

        if (!isset($_POST['booking_data'])) {
            wp_send_json_error(array('message' => 'No booking data received'));
        }

        $booking_data = $_POST['booking_data'];
        $product_id = intval($booking_data['product_id']);

        if (!$product_id) {
            wp_send_json_error(array('message' => 'Invalid product ID'));
        }

        // Ensure WC is loaded and cart exists
        if (!function_exists('WC') || is_null(WC()->cart)) {
            if (function_exists('wc_load_cart')) {
                wc_load_cart();
            } else {
                wp_send_json_error(array('message' => 'WooCommerce not loaded'));
            }
        }

        // Optional: Clear cart before adding booking (common for medical appointments)
        WC()->cart->empty_cart();

        // Add product to cart with booking meta
        $cart_item_key = WC()->cart->add_to_cart($product_id, 1, 0, array(), array(
            'booking_date' => sanitize_text_field($booking_data['date']),
            'booking_time' => sanitize_text_field($booking_data['time']),
            'patient_name' => sanitize_text_field($booking_data['patient_name']),
            'patient_email' => sanitize_email($booking_data['patient_email']),
            'patient_phone' => sanitize_text_field($booking_data['patient_phone']),
            'patient_dni' => sanitize_text_field($booking_data['patient_dni']),
            'visit_reason' => sanitize_text_field($booking_data['visit_reason']),
            'additional_notes' => sanitize_textarea_field($booking_data['additional_notes'])
        ));

        if ($cart_item_key) {
            wp_send_json_success(array('cart_item_key' => $cart_item_key));
        } else {
            // Check why it failed
            $product = wc_get_product($product_id);
            $reason = 'Unknown reason';
            if (!$product) {
                $reason = 'Product not found';
            } elseif (!$product->is_purchasable()) {
                $reason = 'Product is not purchasable (check price)';
            } elseif (!$product->is_in_stock()) {
                $reason = 'Product is out of stock';
            }

            wp_send_json_error(array(
                'message' => 'Failed to add to cart: ' . $reason,
                'product_id' => $product_id
            ));
        }
    }

    // Display booking data in cart
    add_filter('woocommerce_get_item_data', 'medical_display_booking_in_cart', 10, 2);
    function medical_display_booking_in_cart($item_data, $cart_item)
    {
        if (isset($cart_item['booking_date'])) {
            $formatted_date = date('d-m-Y', strtotime($cart_item['booking_date']));
            $item_data[] = array(
                'name' => __('Fecha', 'medical-theme'),
                'value' => esc_html($formatted_date)
            );
        }

        if (isset($cart_item['booking_time'])) {
            $formatted_time = date('H:i', strtotime($cart_item['booking_time']));
            $item_data[] = array(
                'name' => __('Hora', 'medical-theme'),
                'value' => esc_html($formatted_time)
            );
        }

        if (isset($cart_item['patient_name'])) {
            $item_data[] = array(
                'name' => __('Paciente', 'medical-theme'),
                'value' => esc_html($cart_item['patient_name'])
            );
        }

        return $item_data;
    }

    // Save booking data to order meta
    add_action('woocommerce_checkout_create_order_line_item', 'medical_save_booking_to_order', 10, 4);
    function medical_save_booking_to_order($item, $cart_item_key, $values, $order)
    {
        if (isset($values['booking_date'])) {
            $item->add_meta_data('_booking_date', $values['booking_date']);
        }
        if (isset($values['booking_time'])) {
            $item->add_meta_data('Horario', $values['booking_time']);
        }
        if (isset($values['patient_name'])) {
            $item->add_meta_data('Paciente', $values['patient_name']);
        }
        if (isset($values['patient_email'])) {
            $item->add_meta_data('Email Paciente', $values['patient_email']);
        }
        if (isset($values['patient_phone'])) {
            $item->add_meta_data('_patient_phone', $values['patient_phone']);
        }
        if (isset($values['visit_reason'])) {
            $item->add_meta_data('_visit_reason', $values['visit_reason']);
        }
        if (isset($values['additional_notes'])) {
            $item->add_meta_data('_additional_notes', $values['additional_notes']);
        }
    }
}

/**
 * === CORE DATA STRUCTURES (CPTs & Taxonomies) ===
 * 
 */

/**
 * Register Custom Post Types
 */
function medical_register_cpts()
{
    // Medicos
    register_post_type('medico', array(
        'labels' => array(
            'name' => 'Médicos',
            'singular_name' => 'Médico',
            'add_new' => 'Añadir Nuevo',
            'add_new_item' => 'Añadir Nuevo Médico',
            'edit_item' => 'Editar Médico',
            'new_item' => 'Nuevo Médico',
            'view_item' => 'Ver Médico',
            'search_items' => 'Buscar Médicos',
            'not_found' => 'No se encontraron médicos',
            'not_found_in_trash' => 'No hay médicos en la papelera',
        ),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-id-alt',
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt'),
        'show_in_rest' => true, // Important for Gutenberg/ACF Blocks
        'rewrite' => array('slug' => 'medicos'),
    ));

    // Sedes
    register_post_type('sede', array(
        'labels' => array(
            'name' => 'Sedes',
            'singular_name' => 'Sede',
            'add_new' => 'Añadir Nueva',
            'add_new_item' => 'Añadir Nueva Sede',
            'edit_item' => 'Editar Sede',
            'new_item' => 'Nueva Sede',
            'view_item' => 'Ver Sede',
            'search_items' => 'Buscar Sedes',
            'not_found' => 'No se encontraron sedes',
            'not_found_in_trash' => 'No hay sedes en la papelera',
        ),
        'public' => true,
        'has_archive' => false,
        'menu_icon' => 'dashicons-location',
        'supports' => array('title', 'editor', 'thumbnail'),
        'show_in_rest' => true,
        'rewrite' => array('slug' => 'sedes'),
    ));
}
add_action('init', 'medical_register_cpts', 0);

/**
 * Register Taxonomies
 */
function medical_register_taxonomies()
{
    // Especialidades
    register_taxonomy('especialidad', array('medico'), array(
        'labels' => array(
            'name' => 'Especialidades',
            'singular_name' => 'Especialidad',
            'search_items' => 'Buscar Especialidades',
            'all_items' => 'Todas las Especialidades',
            'parent_item' => 'Especialidad Padre',
            'parent_item_colon' => 'Especialidad Padre:',
            'edit_item' => 'Editar Especialidad',
            'update_item' => 'Actualizar Especialidad',
            'add_new_item' => 'Añadir Nueva Especialidad',
            'new_item_name' => 'Nombre de Nueva Especialidad',
            'menu_name' => 'Especialidad',
        ),
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'show_in_rest' => true,
        'rewrite' => array('slug' => 'especialidad'),
    ));
}
add_action('init', 'medical_register_taxonomies');

/**
 * Register ACF Field Groups via PHP
 */
function medical_register_acf_fields()
{
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    // Campos para el CPT Médico
    acf_add_local_field_group(array(
        'key' => 'group_medico_details',
        'title' => 'Detalles del Médico',
        'fields' => array(
            array(
                'key' => 'field_medico_foto',
                'label' => 'Foto de Perfil',
                'name' => 'foto_perfil',
                'type' => 'image',
                'return_format' => 'array',
                'preview_size' => 'medium',
            ),
            array(
                'key' => 'field_medico_badge',
                'label' => 'Etiqueta de Certificación',
                'name' => 'etiqueta_certificacion',
                'type' => 'text',
                'placeholder' => 'Ej: Board Certified Specialist',
            ),
            array(
                'key' => 'field_medico_subtitulo',
                'label' => 'Subtítulo Profesional',
                'name' => 'subtitulo_profesional',
                'type' => 'text',
                'placeholder' => 'Ej: Senior Cardiologist & Internal Medicine',
            ),
            array(
                'key' => 'field_medico_rating',
                'label' => 'Calificación (Rating)',
                'name' => 'rating_value',
                'type' => 'number',
                'min' => 1,
                'max' => 5,
                'step' => '0.1',
            ),
            array(
                'key' => 'field_medico_rating_count',
                'label' => 'Cantidad de Reseñas',
                'name' => 'rating_count',
                'type' => 'number',
            ),
            array(
                'key' => 'field_medico_experience',
                'label' => 'Años de Experiencia',
                'name' => 'experiencia_texto',
                'type' => 'text',
                'placeholder' => 'Ej: 15+ Years',
            ),
            array(
                'key' => 'field_medico_bio',
                'label' => 'Biografía Corta',
                'name' => 'biografia_corta',
                'type' => 'textarea',
                'rows' => 4,
            ),
            array(
                'key' => 'field_medico_horarios',
                'label' => 'Horarios de Atención',
                'name' => 'horarios_atencion',
                'type' => 'repeater',
                'layout' => 'table',
                'button_label' => 'Añadir Horario',
                'sub_fields' => array(
                    array(
                        'key' => 'field_horario_dia',
                        'label' => 'Día',
                        'name' => 'dia',
                        'type' => 'select',
                        'choices' => array(
                            'lunes' => 'Lunes',
                            'martes' => 'Martes',
                            'miercoles' => 'Miércoles',
                            'jueves' => 'Jueves',
                            'viernes' => 'Viernes',
                        ),
                    ),
                    array(
                        'key' => 'field_horario_inicio',
                        'label' => 'Inicio',
                        'name' => 'hora_inicio',
                        'type' => 'time_picker',
                    ),
                    array(
                        'key' => 'field_horario_fin',
                        'label' => 'Fin',
                        'name' => 'hora_fin',
                        'type' => 'time_picker',
                    ),
                    array(
                        'key' => 'field_horario_sede',
                        'label' => 'Sede',
                        'name' => 'sede',
                        'type' => 'post_object',
                        'post_type' => array('sede'),
                        'return_format' => 'object',
                        'ui' => 1,
                    ),
                ),
            ),
            array(
                'key' => 'field_medico_sedes',
                'label' => 'Sedes Relacionadas (Filtro)',
                'name' => 'sedes_atencion',
                'type' => 'post_object',
                'post_type' => array('sede'),
                'multiple' => 1,
                'return_format' => 'object',
                'ui' => 1,
                'instructions' => 'Seleccione las sedes para que el médico aparezca en los filtros de búsqueda.',
            ),
            array(
                'key' => 'field_medico_contact_tab',
                'label' => 'Información de Contacto',
                'type' => 'tab',
            ),
            array(
                'key' => 'field_medico_phone',
                'label' => 'Teléfono',
                'name' => 'contacto_telefono',
                'type' => 'text',
            ),
            array(
                'key' => 'field_medico_email',
                'label' => 'Email',
                'name' => 'contacto_email',
                'type' => 'email',
            ),
            array(
                'key' => 'field_medico_address',
                'label' => 'Dirección de la Clínica',
                'name' => 'clinica_direccion',
                'type' => 'text',
            ),
            array(
                'key' => 'field_medico_map_link',
                'label' => 'Link de Mapa',
                'name' => 'mapa_link',
                'type' => 'url',
            ),
            array(
                'key' => 'field_medico_related_tab',
                'label' => 'Médicos Relacionados',
                'type' => 'tab',
            ),
            array(
                'key' => 'field_medico_related',
                'label' => 'Doctors Relacionados',
                'name' => 'medicos_relacionados',
                'type' => 'relationship',
                'post_type' => array('medico'),
                'filters' => array('search', 'taxonomy'),
                'elements' => array('featured_image'),
                'return_format' => 'object',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'medico',
                ),
            ),
        ),
    ));

    // Página de Opciones
    if (function_exists('acf_add_options_page')) {
        acf_add_options_page(array(
            'page_title' => 'Configuración del Tema',
            'menu_title' => 'Tema Opciones',
            'menu_slug' => 'theme-general-settings',
            'capability' => 'edit_posts',
            'redirect' => false
        ));
    }

    // Campos de Opciones
    acf_add_local_field_group(array(
        'key' => 'group_theme_options',
        'title' => 'Opciones del Tema',
        'fields' => array(
            array(
                'key' => 'field_video_promocional',
                'label' => 'Video Promocional (Home)',
                'name' => 'video_promocional',
                'type' => 'file',
                'instructions' => 'Sube el video promocional que se mostrará en el modal de la página de inicio.',
                'required' => 0,
                'return_format' => 'url', // Just URL is enough for video tag
                'library' => 'all',
                'min_size' => 0,
                'max_size' => 50, // MB
                'mime_types' => 'mp4,webm',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'options_page',
                    'operator' => '==',
                    'value' => 'theme-general-settings',
                ),
            ),
        ),
    ));
}
add_action('acf/init', 'medical_register_acf_fields');
/**
 * API Endpoint o Función Interna para buscar médicos disponibles
 *
 * @param array $params {
 * @type string $dia Día de la semana (lunes, martes, etc.)
 * @type string $hora Hora en formato 'HH:mm' (ej: '10:00')
 * }
 * @return array Lista de médicos disponibles
 */
function medical_buscar_disponibilidad($params)
{
    if (empty($params['dia']) || empty($params['hora'])) {
        return [];
    }

    // Normalizar día (eliminar acentos y minúsculas)
    $dia_buscado = strtolower($params['dia']);
    $acentos = array('á', 'é', 'í', 'ó', 'ú', 'ñ', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ');
    $sin_acentos = array('a', 'e', 'i', 'o', 'u', 'n', 'a', 'e', 'i', 'o', 'u', 'n');
    $dia_buscado = str_replace($acentos, $sin_acentos, $dia_buscado);

    $hora_buscada = $params['hora']; // ej: '09:00'

    $args = array(
        'post_type' => 'medico',
        'posts_per_page' => -1,
        'post_status' => 'publish',
    );

    $query = new WP_Query($args);
    $resultados = [];

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $medico_id = get_the_ID();

            // Obtener horarios del Repeater ACF
            $horarios = get_field('horarios_atencion', $medico_id);

            if ($horarios) {
                foreach ($horarios as $horario) {
                    // Verificar Día (también normalizar el de la base de datos por si acaso)
                    $dia_db = strtolower($horario['dia']);
                    $dia_db = str_replace($acentos, $sin_acentos, $dia_db);

                    if ($dia_db === $dia_buscado) {

                        // Verificar Rango Horario
                        $inicio = $horario['hora_inicio'];
                        $fin = $horario['hora_fin'];

                        // Simple string comparison for time (works for HH:MM format)
                        if ($hora_buscada >= $inicio && $hora_buscada <= $fin) {
                            $resultados[] = array(
                                'nombre' => get_the_title(),
                                'especialidad' => get_the_term_list($medico_id, 'especialidad', '', ', '),
                                // Strip tags incase it returns links, simple text is better for JSON
                                'especialidad_texto' => strip_tags(get_the_term_list($medico_id, 'especialidad', '', ', ')),
                                'sede' => isset($horario['sede']->ID) ? get_the_title($horario['sede']->ID) : 'Sede Principal',
                                'horario' => "$inicio - $fin",
                                'link' => get_permalink(),
                                'foto' => get_the_post_thumbnail_url($medico_id, 'medium_large') ?: 'https://via.placeholder.com/400x400?text=No+Image'
                            );
                            // No hacemos break aquí porque un médico puede tener múltiples turnos el mismo día (mañana/tarde)
                            // Aunque si solo queremos saber si está disponible, con uno basta.
                            // Pero para mostrar opciones, mejor no romper el loop interno de horarios si queremos ser exhaustivos.
                            // Para este caso simple de "está disponible", si encontramos uno, lo agregamos.
                            // Si queremos evitar duplicados (mismo médico dos veces en la lista por dos rangos), podemos chequear.
                            // Por ahora, asumimos rangos disjuntos o simplemente listamos.
                            // Para evitar duplicados en el array $resultados, podemos usar el ID del medico como clave, pero aqui es un array indexado.
                            // Simplemente haremos break del loop de horarios para este médico si encontramos coincidencia.
                            break;
                        }
                    }
                }
            }
        }
        wp_reset_postdata();
    }

    // Si no hay resultados y es "miercoles", verificar si se buscó "miércoles"
    if (empty($resultados) && $dia_buscado == 'miercoles') {
        // Debug logic if needed
    }

    return $resultados;
}

add_action('rest_api_init', function () {
    register_rest_route('medical/v1', '/buscar-medicos', array(
        'methods' => 'GET',
        'callback' => function ($request) {
            $params = $request->get_params(); // ?dia=lunes&hora=10:00
            if (!isset($params['dia']) || !isset($params['hora'])) {
                return new WP_Error('missing_params', 'Faltan parametros dia o hora', array('status' => 400));
            }
            return medical_buscar_disponibilidad($params);
        },
        'permission_callback' => '__return_true', // O validar API Key
    ));
});

// =============================================================================
// WEBMCP SETTINGS PAGE
// =============================================================================

add_action('admin_menu', 'webmcp_add_settings_page');
function webmcp_add_settings_page()
{
    add_options_page(
        'WebMCP AI Settings',
        'WebMCP AI',
        'manage_options',
        'webmcp-settings',
        'webmcp_render_settings_page'
    );
}

add_action('admin_init', 'webmcp_register_settings');
function webmcp_register_settings()
{
    register_setting('webmcp_settings_group', 'webmcp_gemini_api_key', array(
        'sanitize_callback' => 'sanitize_text_field',
    ));
    register_setting('webmcp_settings_group', 'webmcp_gemini_model', array(
        'sanitize_callback' => 'sanitize_text_field',
        'default' => 'gemini-2.0-flash',
    ));
    register_setting('webmcp_settings_group', 'webmcp_system_prompt', array(
        'sanitize_callback' => 'sanitize_textarea_field',
        'default' => 'Eres un asistente médico virtual de una clínica. Ayudas a los pacientes a encontrar médicos disponibles según el día y horario que necesitan. Cuando el usuario mencione un día y una hora, usa la herramienta buscar-medicos para encontrar disponibilidad.',
    ));
}

function webmcp_render_settings_page()
{
    if (!current_user_can('manage_options'))
        return;
    ?>
    <div class="wrap">
        <h1>⚕️ WebMCP AI — Configuración</h1>
        <p>Configura la integración con Gemini AI para el asistente de lenguaje natural.</p>

        <form method="post" action="options.php">
            <?php settings_fields('webmcp_settings_group'); ?>
            <?php do_settings_sections('webmcp-settings'); ?>

            <table class="form-table">
                <tr>
                    <th scope="row"><label for="webmcp_gemini_api_key">🔑 Gemini API Key</label></th>
                    <td>
                        <input type="password" id="webmcp_gemini_api_key" name="webmcp_gemini_api_key"
                            value="<?php echo esc_attr(get_option('webmcp_gemini_api_key')); ?>" class="regular-text"
                            placeholder="AIza..." />
                        <p class="description">Obtené tu API key en <a href="https://aistudio.google.com/app/apikey"
                                target="_blank">Google AI Studio</a>.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="webmcp_gemini_model">🤖 Modelo Gemini</label></th>
                    <td>
                        <select id="webmcp_gemini_model" name="webmcp_gemini_model">
                            <?php
                            $current_model = get_option('webmcp_gemini_model', 'gemini-2.0-flash');
                            $models = array(
                                'gemini-2.0-flash' => 'Gemini 2.0 Flash (Recomendado)',
                                'gemini-1.5-flash' => 'Gemini 1.5 Flash',
                                'gemini-1.5-pro' => 'Gemini 1.5 Pro',
                            );
                            foreach ($models as $value => $label) {
                                echo '<option value="' . esc_attr($value) . '"' . selected($current_model, $value, false) . '>' . esc_html($label) . '</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="webmcp_system_prompt">💬 System Prompt</label></th>
                    <td>
                        <textarea id="webmcp_system_prompt" name="webmcp_system_prompt" class="large-text"
                            rows="5"><?php echo esc_textarea(get_option('webmcp_system_prompt', '')); ?></textarea>
                        <p class="description">Instrucciones de comportamiento para el asistente IA.</p>
                    </td>
                </tr>
            </table>

            <?php submit_button('Guardar Configuración'); ?>
        </form>

        <?php
        $api_key = get_option('webmcp_gemini_api_key');
        if ($api_key): ?>
            <hr>
            <h2>✅ Estado</h2>
            <p style="color: green;">API Key configurada. El endpoint de chat está activo en:</p>
            <code><?php echo esc_url(rest_url('webmcp/v1/chat')); ?></code>
        <?php else: ?>
            <hr>
            <h2>⚠️ Estado</h2>
            <p style="color: orange;">Configurá la API Key para activar el chat con IA.</p>
        <?php endif; ?>
    </div>
    <?php
}

// =============================================================================
// WEBMCP CHAT ENDPOINT — Gemini Function Calling
// =============================================================================

add_action('rest_api_init', function () {
    register_rest_route('webmcp/v1', '/chat', array(
        'methods' => 'POST',
        'callback' => 'webmcp_chat_handler',
        'permission_callback' => '__return_true',
        'args' => array(
            'message' => array(
                'required' => true,
                'sanitize_callback' => 'sanitize_text_field',
            ),
        ),
    ));
});

function webmcp_chat_handler(WP_REST_Request $request)
{
    $api_key = get_option('webmcp_gemini_api_key');
    if (empty($api_key)) {
        return new WP_Error('no_api_key', 'API Key de Gemini no configurada. Ve a Ajustes → WebMCP AI.', array('status' => 503));
    }

    $model = get_option('webmcp_gemini_model', 'gemini-2.0-flash');
    $user_message = $request->get_param('message');

    // System prompt optimizado para acción directa
    $system_prompt = get_option(
        'webmcp_system_prompt',
        'Eres un asistente médico virtual de una clínica. Tu única función es buscar médicos disponibles usando la herramienta buscar_medicos. ' .
        'REGLAS ESTRICTAS: ' .
        '1. Cuando el usuario mencione cualquier día de la semana (lunes, martes, miércoles, jueves, viernes, sábado) Y una hora, INMEDIATAMENTE llama a buscar_medicos sin hacer preguntas. ' .
        '2. Si el usuario dice "lunes a las 10", "lunes 10am", "lunes 10:00", "el lunes a las 10", todos son válidos — interpretá la hora como HH:00 si no tiene minutos. ' .
        '3. NUNCA pidas confirmación. NUNCA preguntes el formato. Simplemente ejecutá la búsqueda. ' .
        '4. Si el usuario NO menciona día ni hora, pedí solo esa información de forma breve. ' .
        '5. Respondé siempre en español rioplatense.'
    );

    // --- Tool definitions ---
    $tools = array(
        array(
            'functionDeclarations' => array(
                array(
                    'description' => 'Busca médicos disponibles en la clínica según el día de la semana y la hora específica.',
                    'parameters' => array(
                        'type' => 'object',
                        'properties' => array(
                            'dia' => array(
                                'type' => 'string',
                                'description' => 'Día de la semana en español (lunes, martes, miercoles, jueves, viernes, sabado)',
                            ),
                            'hora' => array(
                                'type' => 'string',
                                'description' => 'Hora en formato HH:MM (ej: 09:00, 14:30)',
                            ),
                        ),
                        'required' => array('dia', 'hora'),
                    ),
                ),
            ),
        ),
    );

    // --- Primera llamada a Gemini ---
    $gemini_url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$api_key}";

    $body = array(
        'system_instruction' => array(
            'parts' => array(array('text' => $system_prompt)),
        ),
        'contents' => array(
            array(
                'role' => 'user',
                'parts' => array(array('text' => $user_message)),
            ),
        ),
        'tools' => $tools,
    );

    $response = wp_remote_post($gemini_url, array(
        'headers' => array('Content-Type' => 'application/json'),
        'body' => wp_json_encode($body),
        'timeout' => 30,
    ));

    if (is_wp_error($response)) {
        return new WP_Error('gemini_error', $response->get_error_message(), array('status' => 500));
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);

    // Verificar si Gemini quiere llamar a una tool
    $candidate = $data['candidates'][0] ?? null;
    if (!$candidate) {
        return new WP_Error('gemini_no_response', 'Gemini no devolvió respuesta.', array('status' => 500));
    }

    $parts = $candidate['content']['parts'] ?? array();

    // Buscar function call en las parts
    $function_call = null;
    foreach ($parts as $part) {
        if (isset($part['functionCall'])) {
            $function_call = $part['functionCall'];
            break;
        }
    }

    // --- Si Gemini quiere usar una tool ---
    if ($function_call) {
        $fn_name = $function_call['name'];
        $fn_args = $function_call['args'] ?? array();

        // Ejecutar la tool localmente
        $tool_result = webmcp_execute_tool($fn_name, $fn_args);

        // Segunda llamada a Gemini con el resultado de la tool
        $body['contents'][] = array(
            'role' => 'model',
            'parts' => array(array('functionCall' => $function_call)),
        );
        $body['contents'][] = array(
            'role' => 'user',
            'parts' => array(
                array(
                    'functionResponse' => array(
                        'name' => $fn_name,
                        'response' => array('result' => $tool_result),
                    ),
                ),
            ),
        );

        $response2 = wp_remote_post($gemini_url, array(
            'headers' => array('Content-Type' => 'application/json'),
            'body' => wp_json_encode($body),
            'timeout' => 30,
        ));

        $data2 = json_decode(wp_remote_retrieve_body($response2), true);
        $final_text = $data2['candidates'][0]['content']['parts'][0]['text'] ?? 'No pude procesar la respuesta.';

        return rest_ensure_response(array(
            'type' => 'tool_result',
            'tool' => $fn_name,
            'tool_args' => $fn_args,
            'tool_result' => $tool_result,
            'message' => $final_text,
        ));
    }

    // --- Respuesta de texto directo ---
    $text = $parts[0]['text'] ?? 'No entendí tu consulta. ¿Podés decirme qué día y horario necesitás?';

    return rest_ensure_response(array(
        'type' => 'text',
        'message' => $text,
    ));
}

/**
 * Ejecuta una tool MCP localmente en PHP
 */
function webmcp_execute_tool(string $tool_name, array $args): array
{
    switch ($tool_name) {
        case 'buscar_medicos':
            return medical_buscar_disponibilidad(array(
                'dia' => $args['dia'] ?? '',
                'hora' => $args['hora'] ?? '',
            ));

        default:
            return array('error' => "Tool '{$tool_name}' no encontrada.");
    }
}