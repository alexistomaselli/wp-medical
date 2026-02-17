<?php
/**
 * Thankyou page - Custom Overridden for Medical Theme
 *
 * @var WC_Order $order
 */

defined('ABSPATH') || exit;

if ($order):

    // Get Order Items and Doctor Details
    $items = $order->get_items();
    $first_item = reset($items); // Assuming single booking 
    $product_id = $first_item ? $first_item->get_product_id() : 0;

    // Fetch Doctor linked to this product
    $doctor_post = null;
    if ($product_id) {
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
        $doctor_query = new WP_Query($args);
        if ($doctor_query->have_posts()) {
            $doctor_post = $doctor_query->posts[0];
        }
    }

    // Extract Data
    $doctor_name = $doctor_post ? $doctor_post->post_title : ($first_item ? $first_item->get_name() : 'Especialista');
    $doctor_image = $doctor_post ? get_the_post_thumbnail_url($doctor_post->ID, 'thumbnail') : '';

    // Specialty
    $specialty = '';
    if ($doctor_post) {
        $terms = get_the_terms($doctor_post->ID, 'especialidad');
        if (!is_wp_error($terms) && !empty($terms)) {
            $specialty = $terms[0]->name;
        }
    }

    // Order Meta
    $booking_date = $first_item ? $first_item->get_meta('_booking_date') : '';
    $booking_time = $first_item ? $first_item->get_meta('Horario') : ''; // Visible key
    if (!$booking_time)
        $booking_time = $first_item ? $first_item->get_meta('_booking_time') : ''; // Fallback

    // Format Date / Time
    setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'esp'); // Attempt to set Spanish locale
    $formatted_date = $booking_date ? date_i18n('d M, Y', strtotime($booking_date)) : '';
    $formatted_time = $booking_time ? date('H:i', strtotime($booking_time)) : '';

    // Clinic (Placeholder or fetch from doctor)
    $clinic_name = "Clínica Medical Central";
    if ($doctor_post) {
        $sedes = get_field('sedes_atencion', $doctor_post->ID);
        if ($sedes && !empty($sedes)) {
            $clinic_name = get_the_title($sedes[0]);
        }
    }
    ?>

    <div class="thankyou-page-container">

        <div class="thankyou-header">
            <div class="success-icon">
                <span class="material-icons">check</span>
            </div>
            <h1>¡Gracias por tu confianza, <?php echo esc_html($order->get_billing_first_name()); ?>!</h1>
            <p class="subtitle">Tu cita ha sido confirmada.</p>
        </div>

        <div class="thankyou-card">

            <!-- Appointment Summary -->
            <div class="section-title">
                <span class="material-icons">calendar_today</span>
                <h3>Resumen de la Cita</h3>
            </div>

            <div class="appointment-summary-card">
                <div class="doctor-image">
                    <?php if ($doctor_image): ?>
                        <img src="<?php echo esc_url($doctor_image); ?>" alt="<?php echo esc_attr($doctor_name); ?>">
                    <?php else: ?>
                        <div class="placeholder-image"></div>
                    <?php endif; ?>
                </div>
                <div class="doctor-details">
                    <h4>Dr. <?php echo esc_html(str_replace('Dr. ', '', $doctor_name)); ?></h4>
                    <?php if ($specialty): ?>
                        <p class="specialty">ESPECIALISTA EN <?php echo esc_html(strtoupper($specialty)); ?></p>
                    <?php endif; ?>
                    <div class="meta-row">
                        <?php if ($formatted_date): ?>
                            <span class="meta-item"><span class="material-icons">event</span>
                                <?php echo esc_html($formatted_date); ?></span>
                        <?php endif; ?>
                        <?php if ($formatted_time): ?>
                            <span class="meta-item"><span class="material-icons">schedule</span>
                                <?php echo esc_html($formatted_time); ?></span>
                        <?php endif; ?>
                        <span class="meta-item"><span class="material-icons">location_on</span>
                            <?php echo esc_html($clinic_name); ?></span>
                    </div>
                </div>
            </div>

            <hr class="divider">

            <!-- Order Details -->
            <div class="section-title">
                <span class="material-icons">receipt_long</span>
                <h3>Detalles del Pedido</h3>
            </div>

            <div class="order-details-grid">
                <div class="detail-col">
                    <span class="label">NÚMERO DE PEDIDO</span>
                    <span class="value">#<?php echo $order->get_order_number(); ?></span>
                </div>
                <div class="detail-col">
                    <span class="label">FECHA</span>
                    <span class="value"><?php echo date_i18n('d F, Y', strtotime($order->get_date_created())); ?></span>
                </div>
                <div class="detail-col">
                    <span class="label">MÉTODO DE PAGO</span>
                    <span class="value">
                        <span class="material-icons"
                            style="font-size: 14px; vertical-align: middle; color: #6366f1;">credit_card</span>
                        <?php echo wp_kses_post($order->get_payment_method_title()); ?>
                    </span>
                </div>
            </div>

            <hr class="divider">

            <!-- Next Steps -->
            <div class="section-title">
                <span class="material-icons">auto_awesome</span>
                <h3>Próximos Pasos</h3>
            </div>

            <div class="next-steps-grid">
                <a href="#" class="step-card">
                    <div class="icon-circle"><span class="material-icons">event_available</span></div>
                    <span>Añadir a Google Calendar</span>
                </a>
                <a href="#" class="step-card">
                    <div class="icon-circle"><span class="material-icons">download</span></div>
                    <span>Descargar Recibo</span>
                </a>
                <a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>" class="step-card">
                    <div class="icon-circle"><span class="material-icons">account_box</span></div>
                    <span>Portal del Paciente</span>
                </a>
            </div>

        </div>

        <div class="thankyou-footer-actions">
            <a href="<?php echo home_url(); ?>" class="btn-primary">
                VOLVER AL INICIO <span class="material-icons">home</span>
            </a>
            <a href="<?php echo wc_get_account_endpoint_url('orders'); ?>" class="btn-secondary">
                VER MIS CITAS <span class="material-icons">list_alt</span>
            </a>
        </div>

    </div>

<?php else: ?>

    <p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed">
        <?php esc_html_e('Desafortunadamente tu pedido no puede ser procesado.', 'woocommerce'); ?>
    </p>

<?php endif; ?>