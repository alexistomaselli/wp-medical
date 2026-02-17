/**
 * Booking System JavaScript
 */
(function ($) {
    'use strict';

    const BookingSystem = {
        selectedDate: null,
        selectedTime: null,
        flatpickrInstance: null,

        init() {
            this.bindEvents();
            this.initCalendar();
        },

        bindEvents() {
            // Tab navigation
            $('.booking-tab').on('click', (e) => this.handleTabClick(e));

            // Confirm booking button
            $('#confirm-booking-btn').on('click', () => this.confirmBooking());

            // Patient form validation
            $('#patient-form input, #patient-form select, #patient-form textarea').on('change blur keyup', () => {
                this.validateForm();
            });
        },

        handleTabClick(e) {
            const $tab = $(e.currentTarget);
            const tabName = $tab.data('tab');

            $('.booking-tab').removeClass('active');
            $tab.addClass('active');

            $('.booking-tab-content').removeClass('active');
            $('#tab-' + tabName).addClass('active');
        },

        initCalendar() {
            const self = this;

            // Initialize Flatpickr with Spanish locale
            this.flatpickrInstance = flatpickr('#booking-calendar', {
                inline: true,
                minDate: 'today',
                maxDate: new Date().fp_incr(90), // 90 days from today
                dateFormat: 'Y-m-d',
                locale: {
                    firstDayOfWeek: 1, // Lunes
                    weekdays: {
                        shorthand: ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
                        longhand: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado']
                    },
                    months: {
                        shorthand: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                        longhand: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']
                    }
                },
                disable: [
                    function (date) {
                        // Disable Sundays (0 = Sunday)
                        return date.getDay() === 0;
                    }
                ],
                onChange: function (selectedDates, dateStr) {
                    self.selectedDate = dateStr;
                    $('#selected_date').val(dateStr);
                    $('#summary-date').text(self.formatDate(dateStr));

                    // Generate time slots for selected date
                    self.generateTimeSlots(dateStr);
                }
            });

            // Show initial message
            $('#booking-slots').html('<p class="placeholder-text">Por favor selecciona una fecha primero</p>');
        },

        formatDate(dateStr) {
            const date = new Date(dateStr + 'T00:00:00');
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            return date.toLocaleDateString('es-ES', options);
        },

        generateTimeSlots(date) {
            const $slotsContainer = $('#booking-slots');

            // Get day of week (0 = Sunday, 1 = Monday, etc.)
            const dateObj = new Date(date + 'T00:00:00');
            const dayOfWeek = dateObj.getDay();

            // Parse schedule from doctor data
            const schedule = this.getScheduleForDay(dayOfWeek);

            if (!schedule || ((!schedule.morning || schedule.morning.length === 0) && (!schedule.afternoon || schedule.afternoon.length === 0))) {
                $slotsContainer.html('<p class="placeholder-text">No hay horarios disponibles para esta fecha</p>');
                return;
            }

            // Generate HTML for slots
            let slotsHTML = '';

            if (schedule.morning && schedule.morning.length > 0) {
                slotsHTML += '<div class="slot-section">';
                slotsHTML += '<h4 class="slot-period-title">Mañana</h4>';
                slotsHTML += '<div class="slot-grid">';
                schedule.morning.forEach(slot => {
                    slotsHTML += `<button type="button" class="time-slot" data-time="${slot}">${slot}</button>`;
                });
                slotsHTML += '</div></div>';
            }

            if (schedule.afternoon && schedule.afternoon.length > 0) {
                slotsHTML += '<div class="slot-section">';
                slotsHTML += '<h4 class="slot-period-title">Tarde</h4>';
                slotsHTML += '<div class="slot-grid">';
                schedule.afternoon.forEach(slot => {
                    slotsHTML += `<button type="button" class="time-slot" data-time="${slot}">${slot}</button>`;
                });
                slotsHTML += '</div></div>';
            }

            $slotsContainer.html(slotsHTML);

            // Bind slot click events
            $('.time-slot').on('click', (e) => this.selectTimeSlot(e));
        },

        getScheduleForDay(dayOfWeek) {
            // Check if we have doctor schedule data
            if (!medical_booking_params.doctor || !medical_booking_params.doctor.schedule) {
                return null;
            }

            const schedule = medical_booking_params.doctor.schedule;

            // Map day of week to day name (0 = Sunday, 1 = Monday, etc.)
            // Note: ACF stores day names WITHOUT accents (miercoles, not miércoles)
            const dayNames = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
            const currentDay = dayNames[dayOfWeek];

            // Find schedule for current day
            const daySchedule = schedule.find(item => item.dia === currentDay);

            if (!daySchedule) {
                return null;
            }

            // Parse time slots
            const slots = {
                morning: [],
                afternoon: []
            };

            // Generate 30-minute slots from start to end time
            if (daySchedule.hora_inicio && daySchedule.hora_fin) {
                const startTime = this.parseTime(daySchedule.hora_inicio);
                const endTime = this.parseTime(daySchedule.hora_fin);

                let currentTime = startTime;
                while (currentTime < endTime) {
                    const timeStr = this.formatTimeSlot(currentTime);

                    // Split into morning (before 12:00) and afternoon (after 12:00)
                    if (currentTime < 12 * 60) {
                        slots.morning.push(timeStr);
                    } else {
                        slots.afternoon.push(timeStr);
                    }

                    currentTime += 30; // 30 minutes
                }
            }

            return slots;
        },

        // Helper: Parse time string (HH:MM) to minutes
        parseTime(timeStr) {
            const [hours, minutes] = timeStr.split(':').map(Number);
            return hours * 60 + minutes;
        },

        // Helper: Format minutes to time slot string (e.g., "09:00 AM")
        formatTimeSlot(minutes) {
            const hours = Math.floor(minutes / 60);
            const mins = minutes % 60;
            const period = hours < 12 ? 'AM' : 'PM';
            const displayHours = hours > 12 ? hours - 12 : (hours === 0 ? 12 : hours);

            return `${String(displayHours).padStart(2, '0')}:${String(mins).padStart(2, '0')} ${period}`;
        },

        selectTimeSlot(e) {
            const $slot = $(e.currentTarget);
            const time = $slot.data('time');

            // Deselect all slots
            $('.time-slot').removeClass('selected');

            // Select this slot
            $slot.addClass('selected');
            this.selectedTime = time;

            // Update summary
            $('#summary-time').text(time);
            $('#selected_time').val(time);

            this.validateForm();
        },

        validateForm() {
            const name = $('#patient_name').val();
            const email = $('#patient_email').val();
            const phone = $('#patient_phone').val();
            const dni = $('#patient_dni').val();
            const hasDate = this.selectedDate !== null;
            const hasTime = this.selectedTime !== null;

            const isValid = name && email && phone && dni && hasDate && hasTime;

            $('#confirm-booking-btn').prop('disabled', !isValid);

            return isValid;
        },

        confirmBooking() {
            if (!this.validateForm()) {
                alert('Por favor completa todos los campos requeridos y selecciona fecha/hora.');
                return;
            }

            // Collect booking data
            const bookingData = {
                product_id: $('#product_id').val(),
                date: this.selectedDate,
                time: this.selectedTime,
                patient_name: $('#patient_name').val(),
                patient_email: $('#patient_email').val(),
                patient_phone: $('#patient_phone').val(),
                patient_dni: $('#patient_dni').val(),
                visit_reason: $('#visit_reason').val(),
                additional_notes: $('#additional_notes').val()
            };

            // Add to cart with booking data
            this.addToCart(bookingData);
        },

        addToCart(bookingData) {
            const $button = $('#confirm-booking-btn');
            $button.prop('disabled', true).text('Agregando al carrito...');

            $.ajax({
                url: medical_booking_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'medical_add_booking_to_cart',
                    booking_data: bookingData,
                    nonce: medical_booking_params.nonce
                },
                success: (response) => {
                    if (response.success) {
                        // Redirect to checkout
                        window.location.href = medical_booking_params.checkout_url;
                    } else {
                        const errorMsg = response.data && response.data.message ? response.data.message : 'Error al agregar al carrito. Por favor intenta de nuevo.';
                        alert(errorMsg);
                        $button.prop('disabled', false).html('<span class="dashicons dashicons-calendar-alt"></span> CONFIRMAR CITA');
                    }
                },
                error: () => {
                    alert('Error. Por favor intenta de nuevo.');
                    $button.prop('disabled', false).html('<span class="dashicons dashicons-calendar-alt"></span> CONFIRMAR CITA');
                }
            });
        }
    };

    // Initialize on document ready
    $(document).ready(() => {
        if ($('.booking-container').length) {
            // Check if Flatpickr is loaded
            if (typeof flatpickr === 'undefined') {
                console.error('Flatpickr library not loaded');
                return;
            }
            BookingSystem.init();
        }
    });

})(jQuery);
