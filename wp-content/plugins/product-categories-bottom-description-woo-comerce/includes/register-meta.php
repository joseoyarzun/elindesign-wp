<?php

add_action( 'init', 'pcbdw_product_cat_register_meta' );
function pcbdw_product_cat_register_meta() {
    register_meta( 'term', 'details', array(
        'type'              => 'string',
        'description'       => esc_html__( 'Sanitizes and saves the details custom meta field.', 'pcbdw' ),
        'single'            => true,
        'sanitize_callback' => 'pcbdw_sanitize_details',
        'show_in_rest'      => true,
    ) );
}

function pcbdw_sanitize_details( $details ) {
    return wp_kses_post( $details );
}
