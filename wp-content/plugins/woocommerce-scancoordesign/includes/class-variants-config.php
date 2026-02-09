<?php
/**
 * Variants Configuration Manager
 * 
 * Manages all variant options (metal, stone, engravement, etc.)
 * Replaces ACF dependency with native WordPress options
 *
 * @package WooCommerce-ScancoorDesign
 * @version 2.1
 */

if (!defined('ABSPATH')) {
	exit;
}

class ScancoorDesign_Variants_Config {
	
	/**
	 * Option name in wp_options table
	 */
	private static $option_name = 'scancoordesign_variants_settings';
	
	/**
	 * Get all variant options
	 * 
	 * @return array Array with all variant configurations
	 */
	public static function get_all() {
		$options = get_option(self::$option_name, self::get_defaults());
		
		// Ensure all keys exist
		return wp_parse_args($options, self::get_defaults());
	}
	
	/**
	 * Get default configuration structure
	 * 
	 * @return array Default configuration
	 */
	private static function get_defaults() {
		return array(
			'metal' => array(),
			'stone' => array(),
			'engravement' => array(),
			'size' => array(),
			'width' => array(),
			'thickness' => array(),
			'surface' => array(),
		);
	}
	
	/**
	 * Save all variant options
	 * 
	 * @param array $data Variant configuration data
	 * @return bool True on success
	 */
	public static function save($data) {
		// Sanitize data before saving
		$sanitized = self::sanitize_config($data);
		return update_option(self::$option_name, $sanitized);
	}
	
	/**
	 * Get specific variant type
	 * 
	 * @param string $type Type of variant (metal, stone, etc.)
	 * @return array Array of options for that type
	 */
	public static function get($type) {
		$all = self::get_all();
		return isset($all[$type]) ? $all[$type] : array();
	}
	
	/**
	 * Add option to a specific variant type
	 * 
	 * @param string $type Variant type
	 * @param array $option Option data
	 * @return bool True on success
	 */
	public static function add_option($type, $option) {
		$all = self::get_all();
		
		if (!isset($all[$type])) {
			$all[$type] = array();
		}
		
		$all[$type][] = $option;
		
		return self::save($all);
	}
	
	/**
	 * Remove option from a specific variant type
	 * 
	 * @param string $type Variant type
	 * @param int $index Index of option to remove
	 * @return bool True on success
	 */
	public static function remove_option($type, $index) {
		$all = self::get_all();
		
		if (isset($all[$type][$index])) {
			unset($all[$type][$index]);
			// Re-index array
			$all[$type] = array_values($all[$type]);
		}
		
		return self::save($all);
	}
	
	/**
	 * Update specific option
	 * 
	 * @param string $type Variant type
	 * @param int $index Index of option
	 * @param array $data New data
	 * @return bool True on success
	 */
	public static function update_option($type, $index, $data) {
		$all = self::get_all();
		
		if (isset($all[$type][$index])) {
			$all[$type][$index] = $data;
		}
		
		return self::save($all);
	}
	
	/**
	 * Sanitize configuration data
	 * 
	 * @param array $data Raw data
	 * @return array Sanitized data
	 */
	private static function sanitize_config($data) {
		$sanitized = array();
		
		$defaults = self::get_defaults();
		
		foreach ($defaults as $type => $value) {
			if (isset($data[$type]) && is_array($data[$type])) {
				$sanitized[$type] = array();
				
				foreach ($data[$type] as $option) {
					$sanitized[$type][] = self::sanitize_option($option, $type);
				}
			} else {
				$sanitized[$type] = array();
			}
		}
		
		return $sanitized;
	}
	
	/**
	 * Sanitize individual option
	 * 
	 * @param array $option Option data
	 * @param string $type Variant type
	 * @return array Sanitized option
	 */
	private static function sanitize_option($option, $type) {
		$sanitized = array();
		
		// All options need a 'text' field
		$sanitized['text'] = isset($option['text']) ? sanitize_text_field($option['text']) : '';
		
		// Options with value (metal, stone, engravement)
		if (in_array($type, array('metal', 'stone', 'engravement'))) {
			$sanitized['value'] = isset($option['value']) ? floatval($option['value']) : 0;
		}
		
		// Metal also has density
		if ($type === 'metal') {
			$sanitized['density'] = isset($option['density']) ? floatval($option['density']) : 0;
		}
		
		// Size, width, thickness just need value (numeric)
		if (in_array($type, array('size', 'width', 'thickness'))) {
			$sanitized['value'] = isset($option['value']) ? floatval($option['value']) : 0;
		}
		
		return $sanitized;
	}
	
	/**
	 * Import data from ACF (migration helper)
	 * 
	 * @param array $acf_data Data from get_fields(389)
	 * @return bool True on success
	 */
	public static function import_from_acf($acf_data) {
		if (!is_array($acf_data)) {
			return false;
		}
		
		return self::save($acf_data);
	}
	
	/**
	 * Export data in ACF-compatible format
	 * Useful for compatibility during migration
	 * 
	 * @return array ACF-compatible data structure
	 */
	public static function export_acf_format() {
		return self::get_all();
	}
	
	/**
	 * Check if configuration is empty
	 * 
	 * @return bool True if no configuration exists
	 */
	public static function is_empty() {
		$all = self::get_all();
		
		foreach ($all as $type => $options) {
			if (!empty($options)) {
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Get configuration summary for admin display
	 * 
	 * @return array Summary statistics
	 */
	public static function get_summary() {
		$all = self::get_all();
		$summary = array();
		
		foreach ($all as $type => $options) {
			$summary[$type] = count($options);
		}
		
		return $summary;
	}
	
	/**
	 * Reset to defaults (caution!)
	 * 
	 * @return bool True on success
	 */
	public static function reset() {
		return delete_option(self::$option_name);
	}
}
