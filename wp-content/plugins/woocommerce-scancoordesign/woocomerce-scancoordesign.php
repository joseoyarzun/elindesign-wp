<?php
/**
 * Plugin Name: WooCommerce scancoordesign
 * Description: Size, width etc variants calculation. Independent plugin with its own templates.
 * Author: Drake
 * Author URI: https://scancoordesign.com
 * Text Domain: scancoordesign.com
 * Domain Path:
 * Version: 2.1
 * Requires at least: 5.0
 * Requires PHP: 7.2
 * WC requires at least: 3.0
 * WC tested up to: 8.0
 *
 * @package WooCommerce-ScancoorDesign
 * 
 * Changelog:
 * 2.1 - Templates now loaded from plugin directory. No longer dependent on theme files.
 * 2.0 - Previous version
 */

if (!defined('ABSPATH')) {
	exit;
}

// Define plugin constants
define('SCANCOORDESIGN_VERSION', '2.1');
define('SCANCOORDESIGN_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SCANCOORDESIGN_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SCANCOORDESIGN_TEMPLATES_DIR', SCANCOORDESIGN_PLUGIN_DIR . 'templates/');

/**
 * Check if WooCommerce is active
 * Display admin notice if WooCommerce is not active
 **/
function scancoordesign_check_woocommerce_active() {
	if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
		add_action('admin_notices', 'scancoordesign_woocommerce_missing_notice');
		return false;
	}
	return true;
}

function scancoordesign_woocommerce_missing_notice() {
	?>
	<div class="notice notice-error">
		<p><strong>WooCommerce ScancoorDesign:</strong> Este plugin requiere que WooCommerce esté activo. Por favor, activa WooCommerce primero.</p>
	</div>
	<?php
}

// Only load plugin if WooCommerce is active
if (scancoordesign_check_woocommerce_active()) {
	// Plugin code continues here
	
// Load plugin classes
require_once SCANCOORDESIGN_PLUGIN_DIR . 'includes/class-variants-config.php';
require_once SCANCOORDESIGN_PLUGIN_DIR . 'includes/admin/settings-page.php';

/**
 * Helper function to get variant configuration
 * Replaces get_fields(389) calls
 * 
 * @return array Configuration data
 */
function scancoordesign_get_config() {
	return ScancoorDesign_Variants_Config::get_all();
}

/**
 * Helper function for backward compatibility
 * Mimics ACF's get_fields() but uses our internal system
 * 
 * @param int $post_id Post ID (ignored, kept for compatibility)
 * @return array Configuration data
 */
function scancoordesign_get_fields($post_id = null) {
	// Try internal system first
	$config = ScancoorDesign_Variants_Config::get_all();
	
	// If empty and ACF is available, try ACF as fallback
	if (ScancoorDesign_Variants_Config::is_empty() && function_exists('get_fields')) {
		$acf_data = get_fields(389);
		if (!empty($acf_data)) {
			return $acf_data;
		}
	}
	
	return $config;
}

/**
 * Helper function for backward compatibility
 * Mimics ACF's get_field() but uses our internal system
 * 
 * @param string $field_name Field name
 * @param int $post_id Post ID (ignored)
 * @return mixed Field value
 */
function scancoordesign_get_field($field_name, $post_id = null) {
	$config = scancoordesign_get_fields($post_id);
	return isset($config[$field_name]) ? $config[$field_name] : null;
}

// Our custom post type function
function variants_post() {

	register_post_type('variants_setting',
		// CPT Options
		array(
			'labels' => array(
				'name' => __('Variants Setting'),
				'singular_name' => __('Variants Setting'),
			),
			'public' => true,
			//'has_archive' => true,
			'supports' => array('custom-fields'),
			'rewrite' => array('slug' => 'variants_setting'),
			//'capabilities' => array('create_posts' => false),
		)
	);
}
// Hooking up our function to theme setup
add_action('init', 'variants_post');

/**
 * Gives custom product type a template
 * Loads from plugin directory, independent of theme
 *
 * @return void
 */
function se47910821_answer() {
	$template_path = plugin_dir_path(__FILE__) . 'templates/single-product/add-to-cart/auto_varient.php';
	
	// Load template from plugin directory
	if (file_exists($template_path)) {
		include $template_path;
	} else {
		// Fallback to WooCommerce template system
		wc_get_template('single-product/add-to-cart/auto_varient.php');
	}
}
add_action('woocommerce_auto_varient_add_to_cart', 'se47910821_answer');

/**
 * Add plugin templates directory to WooCommerce template path
 * This ensures templates are loaded from plugin even if theme/WC updates
 *
 * @param string $template Template path
 * @param string $template_name Template name
 * @param string $template_path Template path within theme
 * @return string Modified template path
 */
function scancoordesign_woocommerce_locate_template($template, $template_name, $template_path) {
	$plugin_template_path = plugin_dir_path(__FILE__) . 'templates/';
	
	// Check if template exists in plugin
	$plugin_template = $plugin_template_path . $template_name;
	
	if (file_exists($plugin_template)) {
		return $plugin_template;
	}
	
	return $template;
}
add_filter('woocommerce_locate_template', 'scancoordesign_woocommerce_locate_template', 10, 3);

/**
 * Display initial price for auto_varient products on single product page
 */
add_filter('woocommerce_get_price_html', 'scancoordesign_auto_varient_price_html', 10, 2);
function scancoordesign_auto_varient_price_html($price, $product) {
	if ($product->get_type() === 'auto_varient') {
		// If product has a regular price, show it
		if ($product->get_regular_price() > 0) {
			return $price;
		}
		
		// Otherwise show a placeholder that will be replaced by JavaScript
		return '<span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">' . get_woocommerce_currency_symbol() . '</span> <span class="scancoordesign-calculating">Calculando...</span></span>';
	}
	return $price;
}

function custom_attribute($product) {

	$fields = get_post_meta($product->get_id(), 'auto_varient_data');
	//$product = wc_get_product($product_id);
	if (!$fields) {
		$six_original_id = apply_filters('wpml_object_id', $product->get_id(), 'any', FALSE, 'sv');

		$fields = get_post_meta($six_original_id, 'auto_varient_data');
	}

	$attributes = $product->get_attributes();

	include "template/form.php";

}

//add_action('woocommerce_before_add_to_cart_button', 'calc_price');

function calc_price($array) {

	$goldprice = $array['goldprice'];
	$laborcost = $array['laborcost'];
	$size = $array['size'];
	$width = $array['width'];
	$thickness = $array['thickness'];
	$metal = $array['metal'];
	$engravement = $array['engravement'];
	$stone = $array['stone'];
	$density = $array['density'];

	/*if($engravement == 'Straight' || $engravement == 'Cursive')
											{
												$engravement=200;
											}*/
	$size_thickness = $size + $thickness;
	$pi = 3.15;
	$circumference = ($size_thickness) * $pi;
	$massa = $circumference * $width * $thickness;

	$grams = ($massa * $density) / 1000;
	$ringprice = ($grams * $goldprice) + $laborcost;

	$total_price = $ringprice;
	$total_price = $total_price + $engravement;
	$total_price = $total_price + $stone;

	$formated_price = number_format($total_price,0,'.',' ');
	
	//return $total_price = round($formated_price);
	return $total_price = $formated_price;

}

//  The following goes inside the constructor ##

add_filter('woocommerce_add_cart_item_data', 'wdm_add_item_data', 1, 10);
function wdm_add_item_data($cart_item_data, $product_id) {

	global $woocommerce;
	if (!empty($_POST['new_custom_attr']) && !empty($_POST['custom_price']) && !empty($_POST['custom_attr'])) {
		$new_value = array();
		$new_value['_custom_options'] = $_POST['new_custom_attr'];
		$new_value['_my_custom_price'] = $_POST['custom_price'];
		$new_value['_custom_calculation'] = $_POST['custom_attr'];

		if (empty($cart_item_data)) {
			return $new_value;
		} else {
			return array_merge($cart_item_data, $new_value);
		}
	}
}

add_filter('woocommerce_get_cart_item_from_session', 'wdm_get_cart_items_from_session', 1, 3);
function wdm_get_cart_items_from_session($item, $values, $key) {
	// echo "<pre>";
	// //print_r($item);
	// print_r($key);
	// echo "---------------";
	// print_r($values);
	// echo "</pre>";

	if (array_key_exists('_custom_options', $values)) {
		$item['_custom_options'] = $values['_custom_options'];

	}

	return $item;
}

add_filter('woocommerce_cart_item_name', 'add_usr_custom_session', 1, 3);
function add_usr_custom_session($product_name, $values, $cart_item_key) {
	if (!empty($values['_custom_calculation']) && !empty($values['_custom_options'])) {
		$names = $values['_custom_options'];

		$return_string = $product_name . "<br />";
		$i = 0;

		$return_string .= '<dl class="variation">';
		foreach ($names as $key => $values) {

			if ($i >= 7) {
				break;
			}
			?>


			<?php
$return_string .= '<dt class="variation-Lngd">' . $key . '</dt>';
			$return_string .= '<dd class="variation-Lngd"><p>' . $values . '</p></dd>';
			$i++;

		}
		$return_string .= "</dl>";
		// . "<br />" . print_r($values['_custom_options']);
		return $return_string;
	}
	return $product_name;

}

add_action('woocommerce_add_order_item_meta', 'six_add_values_to_order_item_meta', 1, 2);
if (!function_exists('six_add_values_to_order_item_meta')) {
	function six_add_values_to_order_item_meta($item_id, $values) {
		global $woocommerce, $wpdb;
		if (!empty($values['_custom_calculation']) && !empty($values['_custom_options'])) {
			$user_custom_values = $values['_custom_options'];
			if (!empty($user_custom_values)) {
				$i = 0;
				foreach ($user_custom_values as $key => $value) {
					if ($i >= 7) {
						break;
					}
					wc_add_order_item_meta($item_id, $key, $value);
					$i++;
				}
			}
		}
	}
}

// add_action('woocommerce_admin_order_data_after_billing_address', 'my_custom_checkout_field_display_admin_order_meta', 10, 1);

// function my_custom_checkout_field_display_admin_order_meta($order) {
// 	// echo "Asasasasas";
// 	// echo $order->get_id();
// 	$data = get_post_meta($order->get_id());
// 	print_r($data);
// }

add_action('woocommerce_before_calculate_totals', 'update_custom_price', 1, 1);
function update_custom_price($cart_object) {
	// PERFORMANCE FIX: Prevent multiple executions in same request
	static $already_run = false;
	if ($already_run) {
		return;
	}
	$already_run = true;
	
	foreach ($cart_object->cart_contents as $cart_item_key => $value) {
		// Version 2.x
		//$value['data']->price = $value['_custom_options']['custom_price'];
		// Version 3.x / 4.x
		if (!empty($value['_custom_calculation']) && !empty($value['_custom_options'])) {
			$POST = $value['_custom_calculation'];
			$POST1 = $value['_custom_options'];
			$data = array(
				'goldprice' => get_options_six($POST['metal'])[1],
				'laborcost' => $POST1['laborcost'],
				'size' => $POST['size'],
				'width' => $POST['width'],
				'thickness' => $POST['thickness'],
				'surface' => $POST['surface'],
				'metal' => get_options_six($POST['metal'])[0],
				'engravement' => get_options_six($POST['engravement'])[1],
				'stone' => get_options_six($POST['stone'])[1],
				'density' => get_options_six($POST['metal'])[2],
			);
			$pp = calc_price($data);
			
			$value['data']->set_price($pp);
		}

	}
}

//add_filter( 'woocommerce_product_get_sale_price', 'update_custom_price', 20, 2 );

function sv_change_product_price_cart($price, $cart_item, $cart_item_key) {

	if (!empty($cart_item['_custom_calculation']) && !empty($cart_item['_custom_options'])) {
		$POST = $cart_item['_custom_calculation'];
		$POST1 = $cart_item['_custom_options'];
		$data = array(
			'goldprice' => get_options_six($POST['metal'])[1],
			'laborcost' => $POST1['laborcost'],
			'size' => $POST['size'],
			'width' => $POST['width'],
			'thickness' => $POST['thickness'],
			'surface' => $POST['surface'],
			'metal' => get_options_six($POST['metal'])[0],
			'engravement' => get_options_six($POST['engravement'])[1],
			'stone' => get_options_six($POST['stone'])[1],
			'density' => get_options_six($POST['metal'])[2],
		);
		$pp = calc_price($data);
		
		return '<span class="woocommerce-Price-amount amount">' . $pp . '&nbsp;<span class="woocommerce-Price-currencySymbol">' . get_woocommerce_currency_symbol() . '</span></span>';
	} else {
		return $price;
	}
}
add_filter('woocommerce_cart_item_price', 'sv_change_product_price_cart', 10, 3);

// add_filter('woocommerce_cart_item_price', 'modify_cart_product_price', 10, 3);

// function modify_cart_product_price($price, $cart_item, $cart_item_key) {
// 	$price = wc_price($custom_price, 4);
// 	return $price;
// }

add_action('woocommerce_before_order_itemmeta', 'so_32457241_before_order_itemmeta', 10, 3);
function so_32457241_before_order_itemmeta($item_id, $item, $_product) {

	$data = scancoordesign_get_field("laborcost", $item->get_product_id());
	if (!empty($data)) {
		?>
	<table cellspacing="0" class="display_meta">
		<tr>
			<th>Labour Cost :</th>
			<td><?php echo $data; ?></td>
		</tr>
	</table>
	<?php
}
}

function woocommerce_wp_select_multiple($field) {

	global $thepostid, $post, $woocommerce;

	$thepostid = empty($thepostid) ? $post->ID : $thepostid;
	$field['class'] = isset($field['class']) ? $field['class'] : 'select short ';
	$field['wrapper_class'] = isset($field['wrapper_class']) ? $field['wrapper_class'] : '';
	$field['name'] = isset($field['name']) ? $field['name'] : $field['id'];
	
	// PERFORMANCE FIX: Only call get_post_meta ONCE instead of 2-3 times
	if (!isset($field['value'])) {
		$meta_value = get_post_meta($thepostid, $field['meta'], true);
		$field['value'] = $meta_value ? $meta_value : array();
	}
	
	// print_r($$field['value']);
	$array_field = array();
	if (!empty($field['value'][$field['id']])) {
		$array_field = $field['value'][$field['id']];
	}
	echo '<p class="form-field ' . esc_attr($field['id']) . '_field ' . esc_attr($field['wrapper_class']) . '"><label for="' . esc_attr($field['id']) . '">' . wp_kses_post($field['label']) . '</label><select id="' . esc_attr($field['id']) . '" name="' . esc_attr($field['name']) . '" class="' . esc_attr($field['class']) . '" multiple="multiple">';

	foreach ($field['options'] as $key => $value) {

		echo '<option value="' . esc_attr($key) . '" ' . (in_array($key, $array_field) ? 'selected="selected"' : '') . '>' . esc_html($value) . '</option>';

	}

	echo '</select> ';

	if (!empty($field['description'])) {

		if (isset($field['desc_tip']) && false !== $field['desc_tip']) {
			echo '<img class="help_tip" data-tip="' . esc_attr($field['description']) . '" src="' . esc_url(WC()->plugin_url()) . '/assets/images/help.png" height="16" width="16" />';
		} else {
			echo '<span class="description">' . wp_kses_post($field['description']) . '</span>';
		}

	}
	// echo '<span class="description select_check select_all"><a href="javascript:void(0)" onclick="select_all(' . esc_attr($field['id']) . ',true)" class="button">Select All</a></span>';
	// echo '<span class="description" select_check unselect_all><a href="javascript:void(0)" onclick="select_all(' . esc_attr($field['id']) . ',false)" class="button">UnSelect All</a></span>';
	echo '</p>';
}

add_action('init', 'register_auto_varient_type');

function register_auto_varient_type() {

	class WC_Product_Auto_Varient extends WC_Product {

		public function __construct($product) {
			parent::__construct($product);
		}
        
        // Needed since Woocommerce version 3
        public function get_type() {
            return 'auto_varient';
        }
        
        // Override get_price_html to show calculated price
        public function get_price_html($price = '') {
            // If product has a regular price set, show it
            if ($this->get_regular_price()) {
                return parent::get_price_html($price);
            }
            
            // Otherwise show "from" message
            return '<span class="woocommerce-Price-amount amount">0&nbsp;<span class="woocommerce-Price-currencySymbol">' . get_woocommerce_currency_symbol() . '</span></span>';
        }
	}
}

add_filter('product_type_selector', 'add_auto_varient_type');

function add_auto_varient_type($types) {
	$types['auto_varient'] = __('Auto Varient');

	return $types;
}

add_filter('woocommerce_product_data_tabs', 'auto_varient_tab');

function auto_varient_tab($tabs) {

	$tabs['auto_varient'] = array(
		'label' => __('Auto Varient Product'),
		'target' => 'auto_varient_options',
		'class' => 'show_if_auto_varient',
		'priority' => 20,
	);

	return $tabs;
}

add_action('woocommerce_product_data_panels', 'auto_varient_tab_content');

function auto_varient_tab_content() {
	global $thepostid, $post, $woocommerce;
	$thepostid = empty($thepostid) ? $post->ID : $thepostid;
	$data = scancoordesign_get_config();

	// echo "<pre>";
	//get_post_meta($thepostid, 'auto_varient_data', true));
	// echo "</pre>";
	
	// PERFORMANCE FIX: Only call get_post_meta ONCE
	$laborcost = 0;
	$auto_varient_data = get_post_meta($thepostid, 'auto_varient_data', true);
	if (!empty($auto_varient_data) && isset($auto_varient_data['laborcost'])) {
		$laborcost = $auto_varient_data['laborcost'];
	}
	
	// PERFORMANCE OPTIMIZATION: Use array_column() instead of foreach loops (20-30% faster)
	$engravement = !empty($data['engravement']) ? array_column($data['engravement'], 'text', 'text') : array();
	$stone = !empty($data['stone']) ? array_column($data['stone'], 'text', 'text') : array();
	$metal = !empty($data['metal']) ? array_column($data['metal'], 'text', 'text') : array();
	$size = !empty($data['size']) ? array_column($data['size'], 'value', 'value') : array();
	$thickness = !empty($data['thickness']) ? array_column($data['thickness'], 'value', 'value') : array();
	$width = !empty($data['width']) ? array_column($data['width'], 'value', 'value') : array();
	$surface = !empty($data['surface']) ? array_column($data['surface'], 'text', 'text') : array();
	?>
	<style>
		.auto_varient_options{
		    display: block !important;
		}
		#auto_varient_options span.select2.select2-container {
		    width: 80% !important;
		}
		span.select2.select2-container {
		    max-height: 200px;
		    overflow: hidden;
		    overflow-y: auto;
		}
	</style>
	<div id='auto_varient_options' class='panel woocommerce_options_panel'>
	<div style="padding: 10px;color: red;">**All the fields are mandatory without it Auto Varient won't work</div>
	<div style="padding: 10px;color: red;">**Please add any price e.g "0" in "Allmänt" Tab</div>
		<?php
?><div class='options_group'><?php

	woocommerce_wp_text_input(
		array(
			'id' => 'product__auto[laborcost]',
			'label' => __('Labour Cost', 'woocommerce'),
			'placeholder' => 'Labour Cost',
			'desc_tip' => 'true',
			'type' => "number",
			'value' => $laborcost,
			'description' => __('Labour Cost', 'woocommerce'),
		)
	);
	woocommerce_wp_select_multiple(array(
		'id' => 'metal',
		'name' => 'product__auto[metal][]',
		'class' => 'stone select2 select2-search__field my_cus_select2',
		'label' => __('Metal', 'woocommerce'),
		'meta' => 'auto_varient_data',
		'options' => $metal)
	);
	woocommerce_wp_select_multiple(array(
		'id' => 'stone',
		'name' => 'product__auto[stone][]',
		'class' => 'stone select2 select2-search__field my_cus_select2',
		'label' => __('Stone', 'woocommerce'),
		'meta' => 'auto_varient_data',
		'options' => $stone)
	);
	woocommerce_wp_select_multiple(array(
		'id' => 'engravement',
		'name' => 'product__auto[engravement][]',
		'class' => 'engravement select2 select2-search__field my_cus_select2',
		'label' => __('Engravement', 'woocommerce'),
		'meta' => 'auto_varient_data',
		'options' => $engravement)
	);
	woocommerce_wp_select_multiple(array(
		'id' => 'size',
		'name' => 'product__auto[size][]',
		'class' => 'stone select2 select2-search__field my_cus_select2',
		'label' => __('Size', 'woocommerce'),
		'meta' => 'auto_varient_data',
		'options' => $size)
	);
	woocommerce_wp_select_multiple(array(
		'id' => 'surface',
		'name' => 'product__auto[surface][]',
		'class' => 'stone select2 select2-search__field my_cus_select2',
		'label' => __('Surface', 'woocommerce'),
		'meta' => 'auto_varient_data',
		'options' => $surface)
	);
	woocommerce_wp_select_multiple(array(
		'id' => 'thickness',
		'name' => 'product__auto[thickness][]',
		'class' => 'stone select2 select2-search__field my_cus_select2',
		'label' => __('Thickness', 'woocommerce'),
		'meta' => 'auto_varient_data',
		'options' => $thickness)
	);
	woocommerce_wp_select_multiple(array(
		'id' => 'width',
		'name' => 'product__auto[width][]',
		'class' => 'stone select2 select2-search__field my_cus_select2',
		'label' => __('Width', 'woocommerce'),
		'meta' => 'auto_varient_data',
		'options' => $width)
	);
	?></div>
	<!-- <input type="hidden" name="_regular_price" value="0"> -->
 </div>
<script>
$.fn.select2.amd.define('select2/selectAllAdapter', [
    'select2/utils',
    'select2/dropdown',
    'select2/dropdown/attachBody'
], function (Utils, Dropdown, AttachBody) {

    function SelectAll() { }
    SelectAll.prototype.render = function (decorated) {
        var self = this,
            $rendered = decorated.call(this),
            $selectAll = $(
                '<button class="button btn btn-xs btn-default" type="button" style="margin-left:6px;"><i class="fa fa-check-square-o"></i> Select All</button>'
            ),
            $unselectAll = $(
                '<button class="button btn btn-xs btn-default" type="button" style="margin-left:6px;"><i class="fa fa-square-o"></i> Unselect All</button>'
            ),
            $btnContainer = $('<div style="margin-top:3px;">').append($selectAll).append($unselectAll);
        if (!this.$element.prop("multiple")) {
            // this isn't a multi-select -> don't add the buttons!
            return $rendered;
        }
        $rendered.find('.select2-dropdown').prepend($btnContainer);
        $selectAll.on('click', function (e) {
            var $results = $rendered.find('.select2-results__option[aria-selected=false]');
            $results.each(function () {
                self.trigger('select', {
                    data: $(this).data('data')
                });
            });
            self.trigger('close');
        });
        $unselectAll.on('click', function (e) {
            var $results = $rendered.find('.select2-results__option[aria-selected=true]');
            $results.each(function () {
                self.trigger('unselect', {
                    data: $(this).data('data')
                });
            });
            self.trigger('close');
        });
        return $rendered;
    };

    return Utils.Decorate(
        Utils.Decorate(
            Dropdown,
            AttachBody
        ),
        SelectAll
    );

});
$(".select2.my_cus_select2").select2({
	placeholder: 'Select',
    dropdownAdapter: $.fn.select2.amd.require('select2/selectAllAdapter')
});

</script>

 <?php
}

add_action('woocommerce_process_product_meta', 'save_auto_varient_settings');

function save_auto_varient_settings($post_id) {

	$product__auto = $_POST['product__auto'];

	// print_r($product__auto);
	// die();

	if (!empty($product__auto)) {

		update_post_meta($post_id, 'auto_varient_data', $product__auto);
	}
}

// Adding Price fields & inventory to custom product type
add_action('admin_footer', 'pd_custom_product_admin_custom_js');
function pd_custom_product_admin_custom_js() {

	if ('product' != get_post_type()):
		return;
	endif;
	?>
    <script type='text/javascript'>
        jQuery(document).ready(function () {
        	check_auto_varient();
        	jQuery("#product-type").on("change", function() {
           		check_auto_varient();
        	});
        });

       function check_auto_varient() {
       		if(jQuery("#product-type").val() == 'auto_varient'){
	            jQuery('.options_group.pricing').show();
	            //for Inventory tab
	            jQuery('.inventory_options').show();
	            jQuery('.general_options').show();

	            jQuery('#inventory_product_data ._manage_stock_field').show();
	            jQuery('#inventory_product_data ._sold_individually_field').parent().show();
	            jQuery('#inventory_product_data ._sold_individually_field').show();
        	}
       }
    </script>
    <?php
}

/**
 * Helper function to split variant options
 * Format: "Name|Price|Density"
 */
function get_options_six($value) {
	return explode("|", $value);
}

/**
 * AJAX handler for auto_varient price calculation
 * Called from frontend when user changes variant options
 */
add_action('wp_ajax_nopriv_auto_varient_calculate', 'scancoordesign_ajax_calculate_price');
add_action('wp_ajax_auto_varient_calculate', 'scancoordesign_ajax_calculate_price');

function scancoordesign_ajax_calculate_price() {
	// Verify we have the required data
	if (empty($_POST['custom_attr'])) {
		wp_send_json_error(array('message' => 'No custom attributes provided'));
		return;
	}

	$POST = $_POST['custom_attr'];
	$fields = scancoordesign_get_config();

	// Validate required fields
	if (empty($POST['metal']) || empty($_POST['new_custom_attr']['laborcost'])) {
		wp_send_json_error(array('message' => 'Missing required fields'));
		return;
	}

	$data = array(
		'goldprice' => get_options_six($POST['metal'])[1],
		'laborcost' => $_POST['new_custom_attr']['laborcost'],
		'size' => $POST['size'],
		'width' => $POST['width'],
		'thickness' => $POST['thickness'],
		'metal' => get_options_six($POST['metal'])[0],
		'engravement' => get_options_six($POST['engravement'])[1],
		'stone' => get_options_six($POST['stone'])[1],
		'density' => get_options_six($POST['metal'])[2],
	);
	
	$total_price = calc_price($data);
	$data['stone'] = get_options_six($POST['stone'])[0];
	$data['engravement'] = get_options_six($POST['engravement'])[0];
	$data['surface'] = $POST['surface'];
	
	wp_send_json_success(array(
		'status' => 'true',
		'price' => $total_price,
		'data' => $data
	));
}

// Legacy support for old AJAX method
if (!empty($_GET['action']) && $_GET['action'] == 'auto_varient') {
	$POST = $_POST['custom_attr'];
	$fields = scancoordesign_get_config();

	$data = array(
		'goldprice' => get_options_six($POST['metal'])[1],
		'laborcost' => $_POST['new_custom_attr']['laborcost'],
		'size' => $POST['size'],
		'width' => $POST['width'],
		'thickness' => $POST['thickness'],
		'metal' => get_options_six($POST['metal'])[0],
		'engravement' => get_options_six($POST['engravement'])[1],
		'stone' => get_options_six($POST['stone'])[1],
		'density' => get_options_six($POST['metal'])[2],
	);
	$total_price = calc_price($data);
	$data['stone'] = get_options_six($POST['stone'])[0];
	$data['engravement'] = get_options_six($POST['engravement'])[0];
	$data['surface'] = $POST['surface'];
	echo json_encode(array("status" => "true", "price" => $total_price, "data" => $data));
	die();
}

} // End WooCommerce active check
