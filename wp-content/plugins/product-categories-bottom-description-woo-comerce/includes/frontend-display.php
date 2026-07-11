<?php

/**
 * Hook into WooCommerce and decide where to render the custom description.
 * If the category has products, use the user-selected hook.
 * If not, fall back to a hook that executes after the "no products found" message.
 */
add_action('woocommerce_before_main_content', 'pcbdw_maybe_hook_bottom_description', 5);
function pcbdw_maybe_hook_bottom_description()
{
    if (!is_tax() || is_paged()) {
        return;
    }

    $term = get_queried_object();

    if (!$term || empty($term->taxonomy) || !pcbdw_is_supported_taxonomy($term->taxonomy)) {
        return;
    }

    if (pcbdw_is_taxonomy_globally_hidden($term->taxonomy)) {
        return;
    }

    $display_position = get_term_meta($term->term_id, 'woo_bottom_description_display_position', true);

    if (!$display_position) {
        $display_position = 'woocommerce_after_shop_loop';
    }

    if (have_posts()) {
        add_action($display_position, 'pcbdw_product_cat_display_details_meta');
    } else {
        add_action('woocommerce_no_products_found', 'pcbdw_product_cat_display_details_meta');
    }
}


/**
 * Render the custom bottom description for product categories.
 * Only displays if the "display description" option is enabled and content exists.
 */
function pcbdw_product_cat_display_details_meta()
{
    $term = get_queried_object();

    if (!$term || empty($term->taxonomy) || pcbdw_is_taxonomy_globally_hidden($term->taxonomy)) {
        return;
    }

    $display_option = get_term_meta($term->term_id, 'woo_bottom_description_display_option', true);

    if ($display_option === '1') {
        return;
    }

    $details = get_term_meta($term->term_id, 'details', true);

    if ('' === $details) {
        return;
    }

    echo '<div class="pcbdw-bottom-description-content">';
    echo apply_filters('the_content', wp_kses_post(wpautop($details)));
    echo '</div>';
}


/**
 * Output custom CSS styles in the frontend based on saved plugin options.
 */
add_action( 'wp_enqueue_scripts', 'pcbdw_enqueue_frontend_styles' );
function pcbdw_enqueue_frontend_styles()
{
    if (!is_tax()) {
        return;
    }

    $term = get_queried_object();

    if (!$term || empty($term->taxonomy) || !pcbdw_is_supported_taxonomy($term->taxonomy)) {
        return;
    }

    if (pcbdw_is_taxonomy_globally_hidden($term->taxonomy)) {
        return;
    }

    wp_register_style('pcbdw-custom-style', false, [], false);
    wp_enqueue_style('pcbdw-custom-style');

    $sides = ['top', 'right', 'bottom', 'left'];
    $css = '';

    foreach (['margin', 'padding'] as $type) {
        $values = [];

        foreach ($sides as $side) {
            $val = get_option("pcbdw_{$type}_{$side}_value", '');
            $unit = get_option("pcbdw_{$type}_{$side}_unit", 'px');
            $values[] = $val !== '' ? "{$val}{$unit}" : '0';
        }

        $css .= "{$type}: " . implode(' ', $values) . "; ";
    }

    $max_width_val = get_option('pcbdw_max_width_value', '');
    $max_width_unit = get_option('pcbdw_max_width_unit', 'px');
    if ($max_width_val !== '') {
        $css .= "max-width: {$max_width_val}{$max_width_unit}; ";
    }

    $bg_color = get_option('pcbdw_background_color', '#ffffff');
    $css .= "background-color: {$bg_color}; ";

    $border_width = get_option('pcbdw_border_width', '');
    $border_color = get_option('pcbdw_border_color', '#000000');
    if ($border_width !== '') {
        $css .= "border: {$border_width}px solid {$border_color}; ";
    }

    $radius_val = get_option('pcbdw_border_radius_value', '');
    $radius_unit = get_option('pcbdw_border_radius_unit', 'px');
    if ($radius_val !== '') {
        $css .= "border-radius: {$radius_val}{$radius_unit}; ";
    }

    $final_css = ".pcbdw-bottom-description-content { {$css} }";

    wp_add_inline_style('pcbdw-custom-style', $final_css);
}


// Check if a taxonomy is globally hidden based on plugin settings
function pcbdw_is_taxonomy_globally_hidden($taxonomy) {
    $hidden_taxonomies = get_option('pcbdw_hidden_taxonomies', []);

    if (!is_array($hidden_taxonomies) || empty($taxonomy)) {
        return false;
    }

    if (in_array($taxonomy, $hidden_taxonomies, true)) {
        return true;
    }

    if (strpos($taxonomy, 'pa_') === 0 && in_array('pa_all', $hidden_taxonomies, true)) {
        return true;
    }

    return false;
}