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
}
add_action('acf/init', 'medical_register_acf_fields');
