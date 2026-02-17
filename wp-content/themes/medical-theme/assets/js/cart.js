jQuery(function ($) {
    // Quantity buttons
    $(document).on('click', '.quantity button', function () {
        var $button = $(this);
        var $input = $button.parent().find('input');
        var val = parseFloat($input.val());
        var step = parseFloat($input.attr('step'));
        var min = parseFloat($input.attr('min'));
        var max = parseFloat($input.attr('max'));

        if ($button.hasClass('plus')) {
            if (max && val >= max) return;
            $input.val(val + step).change();
        } else {
            if (val <= min) return;
            $input.val(val - step).change();
        }

        // Enable update cart button
        $('[name="update_cart"]').prop('disabled', false).css('opacity', '1').css('cursor', 'pointer');
    });

    // Auto-enable update cart button on input change
    $(document).on('change', '.quantity input', function () {
        $('[name="update_cart"]').prop('disabled', false).css('opacity', '1').css('cursor', 'pointer');
    });
});
