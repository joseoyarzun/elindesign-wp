<?php
/*
Plugin Name: Product Categories Bottom Description for WooCommerce
Plugin URI: https://wordpress.org/plugins/product-categories-bottom-description-woo-comerce
Description: Add a new content field to the bottom of your WooCommerce product categories, right after the products list. Improve your SEO and UX.
Author: Diego de Guindos
Author URI: https://diegoguindos.com
Version: 3.5.0
License: GPL2
*/

defined('ABSPATH') or die('Hey, what are you doing? STOP!');

define( 'PCBDW_META_PREFIX', 'pcbdw_' );

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    return;
}

/**
 * Add a direct link to the settings page from the Plugins screen.
 */
add_filter('plugin_action_links_' . plugin_basename(__FILE__), function ($links) {
    $url = admin_url('admin.php?page=pcbdw-settings');
    $settings_link = '<a href="' . esc_url($url) . '">' . esc_html__('Settings', 'pcbdw') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
});



require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/register-meta.php';
require_once __DIR__ . '/includes/admin-fields.php';
require_once __DIR__ . '/includes/save-hooks.php';
require_once __DIR__ . '/includes/frontend-display.php';
require_once __DIR__ . '/includes/shortcodes.php';
require_once __DIR__ . '/includes/settings-page.php';
