<?php
/**
 * Plugin Name: WPForms Multilingual
 * Plugin URI: https://wpml.org/documentation/related-projects/creating-multilingual-forms-using-wpforms-and-wpml/?utm_source=plugin&utm_medium=gui&utm_campaign=wpforms
 * Description: Add multilingual support for WPForms | <a href="https://wpml.org/documentation/related-projects/creating-multilingual-forms-using-wpforms-and-wpml/?utm_source=plugin&utm_medium=gui&utm_campaign=wpforms">Documentation</a>
 * Author: OnTheGoSystems
 * Author URI: https://www.onthegosystems.com/
 * Version: 0.3.6
 * Plugin Slug: wpml-wpforms
 */

require_once __DIR__ . '/vendor/autoload.php';

if ( ! class_exists( 'WPML_Core_Version_Check' ) ) {
	require_once __DIR__ . '/vendor/wpml-shared/wpml-lib-dependencies/src/dependencies/class-wpml-core-version-check.php';
}

if ( ! WPML_Core_Version_Check::is_ok( __DIR__ . '/wpml-dependencies.json' ) ) {
	return;
}

require_once __DIR__ . '/vendor/wpml/forms/loader.php';

wpml_forms_initialize(
	__DIR__ . '/vendor/wpml/forms',
	untrailingslashit( plugin_dir_url( __FILE__ ) ) . '/vendor/wpml/forms'
);

function wpml_wpforms() {
	$forms = new \WPML\Forms(
		__FILE__,
		\WPML\Forms\Loader\WpForms::class,
		new \WPML\Forms\Loader\WpFormsStatus()
	);
	$forms->addHooks();
}

add_action( 'plugins_loaded', 'wpml_wpforms' );

function wpml_wpforms_activation_hook() {
	update_option( wpml_forms_bulk_registration_option_name( __FILE__ ), true );
}

register_activation_hook( __FILE__, 'wpml_wpforms_activation_hook' );
