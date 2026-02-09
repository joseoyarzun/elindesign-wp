<?php
/**
 * Admin Settings Page for Variants Configuration
 * 
 * Provides UI to manage all variant options without ACF
 *
 * @package WooCommerce-scancoordesign
 * @version 2.1
 */

if (!defined('ABSPATH')) {
	exit;
}

class ScancoorDesign_Admin_Settings {
	
	/**
	 * Initialize admin hooks
	 */
	public static function init() {
		add_action('admin_menu', array(__CLASS__, 'add_menu'));
		add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_scripts'));
		add_action('wp_ajax_scancoordesign_save_variant', array(__CLASS__, 'ajax_save_variant'));
		add_action('wp_ajax_scancoordesign_delete_variant', array(__CLASS__, 'ajax_delete_variant'));
		add_action('admin_notices', array(__CLASS__, 'admin_notices'));
	}
	
	/**
	 * Add menu to WordPress admin
	 */
	public static function add_menu() {
		add_submenu_page(
			'woocommerce',
			'Configuración de Variantes',
			'Variantes scancoordesign',
			'manage_woocommerce',
			'scancoordesign-variants',
			array(__CLASS__, 'render_page')
		);
	}
	
	/**
	 * Enqueue admin scripts and styles
	 */
	public static function enqueue_scripts($hook) {
		if ($hook !== 'woocommerce_page_scancoordesign-variants') {
			return;
		}
		
		wp_enqueue_style('scancoordesign-admin', SCANCOORDESIGN_PLUGIN_URL . 'includes/admin/admin.css', array(), SCANCOORDESIGN_VERSION);
		wp_enqueue_script('scancoordesign-admin', SCANCOORDESIGN_PLUGIN_URL . 'includes/admin/admin.js', array('jquery'), SCANCOORDESIGN_VERSION, true);
		
		wp_localize_script('scancoordesign-admin', 'scancoordesign_admin', array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('scancoordesign_admin_nonce'),
		));
	}
	
	/**
	 * Display admin notices
	 */
	public static function admin_notices() {
		// Check if migration is needed
		if (function_exists('get_fields')) {
			$acf_data = get_fields(389);
			$current_data = ScancoorDesign_Variants_Config::get_all();
			
			$is_empty = ScancoorDesign_Variants_Config::is_empty();
			
			if (!empty($acf_data) && $is_empty) {
				?>
				<div class="notice notice-warning is-dismissible">
					<p><strong>WooCommerce scancoordesign:</strong> Detectamos configuración en ACF. 
					<a href="<?php echo admin_url('admin.php?page=scancoordesign-variants&action=migrate'); ?>" class="button button-primary">Migrar Ahora</a></p>
				</div>
				<?php
			}
		}
	}
	
	/**
	 * AJAX: Save variant option
	 */
	public static function ajax_save_variant() {
		check_ajax_referer('scancoordesign_admin_nonce', 'nonce');
		
		if (!current_user_can('manage_woocommerce')) {
			wp_send_json_error(array('message' => 'Permisos insuficientes'));
		}
		
		$type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : '';
		$data = isset($_POST['data']) ? $_POST['data'] : array();
		
		if (empty($type)) {
			wp_send_json_error(array('message' => 'Tipo no especificado'));
		}
		
		// Add new option
		if (ScancoorDesign_Variants_Config::add_option($type, $data)) {
			wp_send_json_success(array('message' => 'Opción guardada correctamente'));
		} else {
			wp_send_json_error(array('message' => 'Error al guardar'));
		}
	}
	
	/**
	 * AJAX: Delete variant option
	 */
	public static function ajax_delete_variant() {
		check_ajax_referer('scancoordesign_admin_nonce', 'nonce');
		
		if (!current_user_can('manage_woocommerce')) {
			wp_send_json_error(array('message' => 'Permisos insuficientes'));
		}
		
		$type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : '';
		$index = isset($_POST['index']) ? intval($_POST['index']) : -1;
		
		if (empty($type) || $index < 0) {
			wp_send_json_error(array('message' => 'Datos inválidos'));
		}
		
		if (ScancoorDesign_Variants_Config::remove_option($type, $index)) {
			wp_send_json_success(array('message' => 'Opción eliminada'));
		} else {
			wp_send_json_error(array('message' => 'Error al eliminar'));
		}
	}
	
	/**
	 * Render settings page
	 */
	public static function render_page() {
		// Handle migration
		if (isset($_GET['action']) && $_GET['action'] === 'migrate') {
			self::handle_migration();
			return;
		}
		
		// Handle bulk save
		if (isset($_POST['scancoordesign_save_all']) && check_admin_referer('scancoordesign_save_all')) {
			self::handle_bulk_save();
		}
		
		$config = ScancoorDesign_Variants_Config::get_all();
		$summary = ScancoorDesign_Variants_Config::get_summary();
		
		?>
		<div class="wrap">
			<h1>⚙️ Configuración de Variantes - scancoordesign</h1>
			<p>Gestiona todas las opciones de variantes para tus productos personalizados.</p>
			
			<?php if (ScancoorDesign_Variants_Config::is_empty()): ?>
				<div class="notice notice-warning">
					<p><strong>⚠️ No hay configuración.</strong> Agrega opciones abajo o 
					<?php if (function_exists('get_fields')): ?>
						<a href="?page=scancoordesign-variants&action=migrate">migra desde ACF</a>.
					<?php else: ?>
						agrega opciones manualmente.
					<?php endif; ?>
					</p>
				</div>
			<?php endif; ?>
			
			<form method="post" action="" id="scancoordesign-config-form">
				<?php wp_nonce_field('scancoordesign_save_all'); ?>
				
				<div class="scancoordesign-tabs">
					<nav class="nav-tab-wrapper">
						<a href="#tab-metal" class="nav-tab nav-tab-active">Metal (<?php echo $summary['metal']; ?>)</a>
						<a href="#tab-stone" class="nav-tab">Piedras (<?php echo $summary['stone']; ?>)</a>
						<a href="#tab-engravement" class="nav-tab">Grabados (<?php echo $summary['engravement']; ?>)</a>
						<a href="#tab-size" class="nav-tab">Tamaños (<?php echo $summary['size']; ?>)</a>
						<a href="#tab-width" class="nav-tab">Anchos (<?php echo $summary['width']; ?>)</a>
						<a href="#tab-thickness" class="nav-tab">Grosores (<?php echo $summary['thickness']; ?>)</a>
						<a href="#tab-surface" class="nav-tab">Superficies (<?php echo $summary['surface']; ?>)</a>
					</nav>
					
					<!-- METAL TAB -->
					<div id="tab-metal" class="scancoordesign-tab-content active">
						<h2>⚙️ Configuración de Metales</h2>
						<p>Formato: Nombre, Precio por gramo (SEK), Densidad (g/cm³)</p>
						<?php self::render_variant_table('metal', $config['metal']); ?>
					</div>
					
					<!-- STONE TAB -->
					<div id="tab-stone" class="scancoordesign-tab-content">
						<h2>💎 Configuración de Piedras</h2>
						<p>Formato: Nombre, Precio adicional (SEK)</p>
						<?php self::render_variant_table('stone', $config['stone']); ?>
					</div>
					
					<!-- ENGRAVEMENT TAB -->
					<div id="tab-engravement" class="scancoordesign-tab-content">
						<h2>✍️ Configuración de Grabados</h2>
						<p>Formato: Nombre, Precio adicional (SEK)</p>
						<?php self::render_variant_table('engravement', $config['engravement']); ?>
					</div>
					
					<!-- SIZE TAB -->
					<div id="tab-size" class="scancoordesign-tab-content">
						<h2>📏 Configuración de Tamaños</h2>
						<p>Valores numéricos del tamaño del anillo</p>
						<?php self::render_variant_table('size', $config['size']); ?>
					</div>
					
					<!-- WIDTH TAB -->
					<div id="tab-width" class="scancoordesign-tab-content">
						<h2>↔️ Configuración de Anchos</h2>
						<p>Valores numéricos del ancho en mm</p>
						<?php self::render_variant_table('width', $config['width']); ?>
					</div>
					
					<!-- THICKNESS TAB -->
					<div id="tab-thickness" class="scancoordesign-tab-content">
						<h2>⬍ Configuración de Grosores</h2>
						<p>Valores numéricos del grosor en mm</p>
						<?php self::render_variant_table('thickness', $config['thickness']); ?>
					</div>
					
					<!-- SURFACE TAB -->
					<div id="tab-surface" class="scancoordesign-tab-content">
						<h2>✨ Configuración de Superficies</h2>
						<p>Tipos de acabado de superficie</p>
						<?php self::render_variant_table('surface', $config['surface']); ?>
					</div>
				</div>
				
				<p class="submit">
					<input type="submit" name="scancoordesign_save_all" class="button button-primary button-large" value="💾 Guardar Todos los Cambios">
				</p>
			</form>
		</div>
		<?php
	}
	
	/**
	 * Render variant table for a specific type
	 */
	private static function render_variant_table($type, $options) {
		$has_value = in_array($type, array('metal', 'stone', 'engravement'));
		$has_density = ($type === 'metal');
		$is_numeric = in_array($type, array('size', 'width', 'thickness'));
		
		?>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th width="5%">#</th>
					<th width="<?php echo $has_value ? '35%' : '70%'; ?>">Nombre/Texto</th>
					<?php if ($has_value): ?>
						<th width="25%">Precio/Valor (SEK)</th>
					<?php endif; ?>
					<?php if ($has_density): ?>
						<th width="20%">Densidad (g/cm³)</th>
					<?php endif; ?>
					<?php if ($is_numeric): ?>
						<th width="25%">Valor</th>
					<?php endif; ?>
					<th width="15%">Acciones</th>
				</tr>
			</thead>
			<tbody id="<?php echo esc_attr($type); ?>-list">
				<?php if (!empty($options)): ?>
					<?php foreach ($options as $index => $option): ?>
						<tr data-index="<?php echo $index; ?>">
							<td><?php echo $index + 1; ?></td>
							<td>
								<input type="text" name="config[<?php echo $type; ?>][<?php echo $index; ?>][text]" 
									value="<?php echo esc_attr($option['text']); ?>" class="regular-text" required>
							</td>
							<?php if ($has_value): ?>
								<td>
									<input type="number" step="0.01" name="config[<?php echo $type; ?>][<?php echo $index; ?>][value]" 
										value="<?php echo esc_attr($option['value']); ?>" class="small-text" required>
								</td>
							<?php endif; ?>
							<?php if ($has_density): ?>
								<td>
									<input type="number" step="0.01" name="config[<?php echo $type; ?>][<?php echo $index; ?>][density]" 
										value="<?php echo esc_attr($option['density']); ?>" class="small-text" required>
								</td>
							<?php endif; ?>
							<?php if ($is_numeric): ?>
								<td>
									<input type="number" step="0.01" name="config[<?php echo $type; ?>][<?php echo $index; ?>][value]" 
										value="<?php echo esc_attr($option['value']); ?>" class="small-text" required>
								</td>
							<?php endif; ?>
							<td>
								<button type="button" class="button button-small delete-option" data-type="<?php echo $type; ?>" data-index="<?php echo $index; ?>">
									🗑️ Eliminar
								</button>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php else: ?>
					<tr class="no-items">
						<td colspan="<?php echo $has_density ? 5 : ($has_value ? 4 : 4); ?>" style="text-align:center;">
							No hay opciones configuradas. Haz clic en "Agregar Nueva" abajo.
						</td>
					</tr>
				<?php endif; ?>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="<?php echo $has_density ? 5 : ($has_value ? 4 : 4); ?>">
						<button type="button" class="button add-option" data-type="<?php echo $type; ?>">
							➕ Agregar Nueva Opción
						</button>
					</td>
				</tr>
			</tfoot>
		</table>
		
		<!-- Template for new row -->
		<script type="text/template" id="<?php echo $type; ?>-template">
			<tr data-index="__INDEX__">
				<td>__NUM__</td>
				<td>
					<input type="text" name="config[<?php echo $type; ?>][__INDEX__][text]" value="" class="regular-text" required>
				</td>
				<?php if ($has_value): ?>
					<td>
						<input type="number" step="0.01" name="config[<?php echo $type; ?>][__INDEX__][value]" value="0" class="small-text" required>
					</td>
				<?php endif; ?>
				<?php if ($has_density): ?>
					<td>
						<input type="number" step="0.01" name="config[<?php echo $type; ?>][__INDEX__][density]" value="0" class="small-text" required>
					</td>
				<?php endif; ?>
				<?php if ($is_numeric): ?>
					<td>
						<input type="number" step="0.01" name="config[<?php echo $type; ?>][__INDEX__][value]" value="0" class="small-text" required>
					</td>
				<?php endif; ?>
				<td>
					<button type="button" class="button button-small delete-option" data-type="<?php echo $type; ?>" data-index="__INDEX__">
						🗑️ Eliminar
					</button>
				</td>
			</tr>
		</script>
		<?php
	}
	
	/**
	 * Handle bulk save from form
	 */
	private static function handle_bulk_save() {
		if (!current_user_can('manage_woocommerce')) {
			wp_die('Permisos insuficientes');
		}
		
		$config = isset($_POST['config']) ? $_POST['config'] : array();
		
		if (ScancoorDesign_Variants_Config::save($config)) {
			echo '<div class="notice notice-success"><p>✅ Configuración guardada correctamente.</p></div>';
		} else {
			echo '<div class="notice notice-error"><p>❌ Error al guardar la configuración.</p></div>';
		}
	}
	
	/**
	 * Handle migration from ACF
	 */
	private static function handle_migration() {
		if (!function_exists('get_fields')) {
			wp_die('ACF no está disponible para migración');
		}
		
		if (!current_user_can('manage_woocommerce')) {
			wp_die('Permisos insuficientes');
		}
		
		$acf_data = get_fields(389);
		
		if (empty($acf_data)) {
			echo '<div class="wrap"><h1>⚠️ No hay datos en ACF</h1><p>No se encontró configuración en el post 389.</p></div>';
			return;
		}
		
		if (ScancoorDesign_Variants_Config::import_from_acf($acf_data)) {
			$summary = ScancoorDesign_Variants_Config::get_summary();
			?>
			<div class="wrap">
				<h1>✅ Migración Completada</h1>
				<div class="notice notice-success">
					<p><strong>¡Éxito!</strong> Los datos de ACF han sido migrados al sistema interno.</p>
				</div>
				
				<h2>📊 Resumen de Migración:</h2>
				<table class="wp-list-table widefat">
					<thead>
						<tr>
							<th>Tipo</th>
							<th>Cantidad</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($summary as $type => $count): ?>
							<tr>
								<td><?php echo ucfirst($type); ?></td>
								<td><?php echo $count; ?> opciones</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				
				<p>
					<a href="<?php echo admin_url('admin.php?page=scancoordesign-variants'); ?>" class="button button-primary">
						Ver Configuración
					</a>
				</p>
				
				<div class="notice notice-info">
					<p><strong>ℹ️ Nota:</strong> Los datos originales de ACF (post 389) no han sido eliminados. 
					Puedes desactivar ACF de forma segura ahora.</p>
				</div>
			</div>
			<?php
		} else {
			echo '<div class="wrap"><h1>❌ Error en Migración</h1><p>Hubo un error al migrar los datos.</p></div>';
		}
	}
}

// Initialize
ScancoorDesign_Admin_Settings::init();
