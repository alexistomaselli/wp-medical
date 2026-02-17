<?php
/**
 * Product quantity inputs
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 10.1.0
 */

defined('ABSPATH') || exit;

$label = !empty($args['product_name']) ? sprintf(esc_html__('%s quantity', 'woocommerce'), wp_strip_all_tags($args['product_name'])) : esc_html__('Quantity', 'woocommerce');
?>
<div class="quantity">
    <button type="button" class="minus">-</button>
    <label class="screen-reader-text" for="<?php echo esc_attr($input_id); ?>">
        <?php echo esc_attr($label); ?>
    </label>
    <input type="<?php echo esc_attr($type); ?>" <?php echo $readonly ? 'readonly="readonly"' : ''; ?>
        id="<?php echo esc_attr($input_id); ?>" class="<?php echo esc_attr(join(' ', (array) $classes)); ?>"
        name="<?php echo esc_attr($input_name); ?>" value="<?php echo esc_attr($input_value); ?>"
        aria-label="<?php esc_attr_e('Product quantity', 'woocommerce'); ?>" min="<?php echo esc_attr($min_value); ?>"
        <?php if (0 < $max_value): ?>max="<?php echo esc_attr($max_value); ?>" <?php endif; ?>
        step="<?php echo esc_attr($step); ?>" />
    <button type="button" class="plus">+</button>
</div>