<?php
/**
 * Plugin Name: Ninja Forms Multilingual
 * Plugin URI: https://wpml.org/documentation/related-projects/creating-multilingual-forms-using-ninja-forms-and-wpml/?utm_source=plugin&utm_medium=gui&utm_campaign=ninjaforms
 * Description: Add multilingual support for Ninja Forms | <a href="https://wpml.org/documentation/related-projects/creating-multilingual-forms-using-ninja-forms-and-wpml/?utm_source=plugin&utm_medium=gui&utm_campaign=ninjaforms">Documentation</a>
 * Author: OnTheGoSystems
 * Author URI: http://www.onthegosystems.com/
 * Version: 0.3.1
 * Plugin Slug: wpml-ninja-forms
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/vendor/wpml/forms/loader.php';

wpml_forms_initialize(
	__DIR__ . '/vendor/wpml/forms',
	untrailingslashit( plugin_dir_url( __FILE__ ) ) . '/vendor/wpml/forms'
);

function wpml_ninja_forms() {
	$forms = new \WPML\Forms(
		__FILE__,
		\WPML\Forms\Loader\NinjaForms::class,
		new \WPML\Forms\Loader\NinjaFormsStatus()
	);
	$forms->addHooks();
}

add_action( 'plugins_loaded', 'wpml_ninja_forms' );

function wpml_ninja_forms_activation_hook() {
	update_option( wpml_forms_bulk_registration_option_name( __FILE__ ), true );
}

register_activation_hook( __FILE__, 'wpml_ninja_forms_activation_hook' );
