<?php
function dfm_get_field_name($gateway_id, $type, $section) {
	return sprintf('dfm_%s_%s_%s', $section, $type, $gateway_id);
}
function dfm_per_categories_include_field_name($gateway_id) {
	return dfm_get_field_name($gateway_id, 'include', 'per_categories');
}
function dfm_per_categories_exclude_field_name($gateway_id) {
	return dfm_get_field_name($gateway_id, 'exclude', 'per_categories');
}
function dfm_per_tags_include_field_name($gateway_id) {
	return dfm_get_field_name($gateway_id, 'include', 'per_tags');
}
function dfm_per_tags_exclude_field_name($gateway_id) {
	return dfm_get_field_name($gateway_id, 'exclude', 'per_tags');
}
function dfm_get_option($gateway_id, $type, $section, $default = false) {
	$field_name = dfm_get_field_name($gateway_id, $type, $section);
	return dfm_convert_to_array_int(get_option($field_name, $default));
}
function dfm_per_tags_include_get_option($gateway_id, $default = false) {
	return dfm_get_option($gateway_id, 'include', 'per_tags', $default);
}
function dfm_per_tags_exclude_get_option($gateway_id, $default = false) {
	return dfm_get_option($gateway_id, 'exclude', 'per_tags', $default);
}
function dfm_per_categories_include_get_option($gateway_id, $default = false) {
	return dfm_get_option($gateway_id, 'include', 'per_categories', $default);
}
function dfm_per_categories_exclude_get_option($gateway_id, $default = false) {
	return dfm_get_option($gateway_id, 'exclude', 'per_categories', $default);
}
function dfm_per_categories_enabled() {
	return get_option('dfm_per_categories_enable', false);
}
function dfm_per_tags_enabled() {
	return get_option('dfm_per_tags_enable', false);
}
function dfm_convert_to_array_int($input) {
		if (is_array($input)) {
			return array_map('intval', $input);
		} else {
			return [];
		}
}

function dfm_per_categories_do_disable( $terms, $is_include = false ) {
	if (empty($terms)) {
		return false;
	}
	foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item_values ) {
		$product_terms = get_the_terms( $cart_item_values['product_id'], 'product_cat' );
		if ( $product_terms && ! is_wp_error( $product_terms ) ) {
			foreach( $product_terms as $product_term ) {
				if ( in_array( $product_term->term_id, $terms ) ) {
					return ( ! $is_include );
				}
			}
		}
	}
	return $is_include;
}

function dfm_per_tags_do_disable( $terms, $is_include = false ) {
	if (empty($terms)) {
		return false;
	}
	foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item_values ) {
		$product_terms = get_the_terms( $cart_item_values['product_id'], 'product_tag' );
		if ( $product_terms && ! is_wp_error( $product_terms ) ) {
			foreach( $product_terms as $product_term ) {
				if ( in_array( $product_term->term_id, $terms ) ) {
					return ( ! $is_include );
				}
			}
		}
	}
	return $is_include;
}
?>