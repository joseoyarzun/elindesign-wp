<?php

// Shortcode to display the details field content
add_shortcode('woo-bottom-description', 'pcbdw_product_category_bottom_description_shortcode');
function pcbdw_product_category_bottom_description_shortcode($atts) {
    $atts = shortcode_atts(array(
        'category_slug' => ''
    ), $atts);

    if (!empty($atts['category_slug'])) {
        $category = get_term_by('slug', $atts['category_slug'], 'product_cat');
        if ($category) {
            $details = get_term_meta($category->term_id, 'details', true);
            return wpautop($details);
        }
    }

    $current_category = get_queried_object();
    if ($current_category && !empty($current_category->term_id)) {
        $details = get_term_meta($current_category->term_id, 'details', true);
        return wpautop($details);
    }

    return '';
}
