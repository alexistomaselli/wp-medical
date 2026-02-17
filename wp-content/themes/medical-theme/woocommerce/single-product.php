<?php
/**
 * The Template for displaying all single products
 *
 * @package Medical
 */

get_header();

// Get the product
global $product;
?>

<main id="primary" class="site-main">
    <div class="booking-container">
        <?php while (have_posts()):
            the_post(); ?>

            <div class="booking-layout">
                <!-- Left Side: Booking Interface -->
                <div class="booking-main">
                    <h1 class="booking-title"><?php the_title(); ?></h1>
                    <p class="booking-description"><?php echo $product->get_short_description(); ?></p>

                    <!-- Tabs -->
                    <div class="booking-tabs">
                        <button class="booking-tab active" data-tab="schedule">
                            <span class="dashicons dashicons-calendar-alt"></span>
                            Agendar
                        </button>
                        <button class="booking-tab" data-tab="confirmation">
                            <span class="dashicons dashicons-yes-alt"></span>
                            Confirmación
                        </button>
                    </div>

                    <!-- Tab Content: Schedule -->
                    <div class="booking-tab-content active" id="tab-schedule">
                        <!-- Date Selector -->
                        <div class="booking-section">
                            <h3 class="section-title">
                                <span class="dashicons dashicons-calendar"></span>
                                Seleccionar Fecha
                            </h3>
                            <div id="booking-calendar"></div>
                        </div>

                        <!-- Time Slots -->
                        <div class="booking-section">
                            <h3 class="section-title">
                                <span class="dashicons dashicons-clock"></span>
                                Horarios Disponibles
                            </h3>
                            <div id="booking-slots">
                                <p class="placeholder-text">Por favor selecciona una fecha primero</p>
                            </div>
                        </div>

                        <!-- Patient Information -->
                        <div class="booking-section">
                            <h3 class="section-title">
                                <span class="dashicons dashicons-admin-users"></span>
                                Información del Paciente
                            </h3>
                            <form id="patient-form" class="patient-form">
                                <div class="form-row">
                                    <div class="form-field">
                                        <label for="patient_name">Nombre Completo *</label>
                                        <input type="text" id="patient_name" name="patient_name" required>
                                    </div>
                                    <div class="form-field">
                                        <label for="patient_email">Correo Electrónico *</label>
                                        <input type="email" id="patient_email" name="patient_email" required>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-field">
                                        <label for="patient_phone">Teléfono *</label>
                                        <input type="tel" id="patient_phone" name="patient_phone" required>
                                    </div>
                                    <div class="form-field">
                                        <label for="patient_dni">DNI del Paciente *</label>
                                        <input type="text" id="patient_dni" name="patient_dni" required>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-field">
                                        <label for="visit_reason">Motivo de Consulta</label>
                                        <select id="visit_reason" name="visit_reason">
                                            <option value="">Seleccionar motivo</option>
                                            <option value="routine">Consulta de Rutina</option>
                                            <option value="followup">Consulta de Seguimiento</option>
                                            <option value="new">Nueva Consulta</option>
                                            <option value="other">Otro</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-row full-width">
                                    <div class="form-field">
                                        <label for="additional_notes">Notas Adicionales (Opcional)</label>
                                        <textarea id="additional_notes" name="additional_notes" rows="4"
                                            placeholder="Describe cualquier síntoma o inquietud específica..."></textarea>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Hidden fields for booking data -->
                        <input type="hidden" id="selected_date" name="selected_date">
                        <input type="hidden" id="selected_time" name="selected_time">
                        <input type="hidden" id="product_id" value="<?php echo $product->get_id(); ?>">

                        <!-- Confirm Button -->
                        <button type="button" id="confirm-booking-btn" class="btn-confirm-booking" disabled>
                            <span class="dashicons dashicons-calendar-alt"></span>
                            CONFIRMAR CITA
                        </button>
                    </div>

                    <!-- Tab Content: Confirmation -->
                    <div class="booking-tab-content" id="tab-confirmation">
                        <div class="confirmation-message">
                            <p>Revisa los detalles de tu reserva y procede al checkout.</p>
                        </div>
                    </div>
                </div>

                <!-- Right Side: Booking Summary -->
                <aside class="booking-sidebar">
                    <div class="booking-summary-card">
                        <h3 class="summary-title">Resumen de Reserva</h3>

                        <?php
                        // Get doctor info from linked medico
                        $medico_id = null;
                        $args = array(
                            'post_type' => 'medico',
                            'meta_query' => array(
                                array(
                                    'key' => 'producto_consulta',
                                    'value' => $product->get_id(),
                                    'compare' => '='
                                )
                            ),
                            'posts_per_page' => 1
                        );
                        $medico_query = new WP_Query($args);

                        if ($medico_query->have_posts()):
                            $medico_query->the_post();
                            $medico_id = get_the_ID();
                            $foto = get_field('foto_perfil');
                            $subtitulo = get_field('subtitulo_profesional');
                            ?>
                            <div class="doctor-info">
                                <?php if ($foto):
                                    if (is_array($foto)) {
                                        $foto_url = $foto['url'];
                                    } elseif (is_numeric($foto)) {
                                        $foto_url = wp_get_attachment_url($foto);
                                    } elseif (is_object($foto) && isset($foto->ID)) {
                                        $foto_url = wp_get_attachment_url($foto->ID);
                                    }
                                    ?>
                                    <img src="<?php echo esc_url($foto_url); ?>" alt="<?php the_title(); ?>" class="doctor-avatar">
                                <?php endif; ?>
                                <div class="doctor-details">
                                    <h4 class="doctor-name"><?php the_title(); ?></h4>
                                    <?php if ($subtitulo): ?>
                                        <p class="doctor-specialty"><?php echo esc_html($subtitulo); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php
                            wp_reset_postdata();
                        endif;
                        ?>

                        <div class="summary-item">
                            <span class="dashicons dashicons-calendar"></span>
                            <div class="summary-details">
                                <span class="summary-label">Fecha</span>
                                <span class="summary-value" id="summary-date">No seleccionada</span>
                            </div>
                        </div>

                        <div class="summary-item">
                            <span class="dashicons dashicons-clock"></span>
                            <div class="summary-details">
                                <span class="summary-label">Hora</span>
                                <span class="summary-value" id="summary-time">No seleccionada</span>
                            </div>
                        </div>

                        <div class="summary-item">
                            <span class="dashicons dashicons-location"></span>
                            <div class="summary-details">
                                <span class="summary-label">Ubicación de la Clínica</span>
                                <span class="summary-value">
                                    <?php
                                    if ($medico_id) {
                                        $sedes = get_field('sedes_atencion', $medico_id);
                                        if ($sedes && is_array($sedes)) {
                                            $sede = $sedes[0];
                                            echo get_the_title($sede);
                                        }
                                    }
                                    ?>
                                </span>
                            </div>
                        </div>

                        <div class="summary-price">
                            <span class="price-label">Total</span>
                            <span class="price-value"><?php echo $product->get_price_html(); ?></span>
                        </div>

                        <div class="summary-help">
                            <p><strong>¿Necesitas ayuda?</strong></p>
                            <p>Llámanos al +54 (11) 4567-8900 para asistencia con tu reserva</p>
                        </div>
                    </div>
                </aside>
            </div>

        <?php endwhile; ?>
    </div>
</main>

<?php get_footer(); ?>