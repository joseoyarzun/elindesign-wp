# Análisis de Dependencias - WooCommerce SixWebSoft

## 🔍 Plugin Dependencies Analysis

### 1️⃣ **WooCommerce** (OBLIGATORIO - No reemplazable)
- **Estado:** 🔴 DEPENDENCIA CRÍTICA
- **Función:** Base del sistema de comercio electrónico
- **Usado para:**
  - Tipos de productos personalizados
  - Sistema de carrito
  - Procesamiento de órdenes
  - Cálculo de precios
- **Recomendación:** ❌ NO ELIMINAR - Es la base del sistema

---

### 2️⃣ **Advanced Custom Fields (ACF)** (REEMPLAZABLE)
- **Estado:** 🟡 DEPENDENCIA ACTUAL - PUEDE REEMPLAZARSE
- **Archivos que lo usan:**
  - `woocomerce-sixwebsoft.php` (líneas 336, 432, 686)
  - `template/form.php` (línea 3)

#### Funcionalidad Actual con ACF:
```php
// Obtiene toda la configuración desde el post ID 389
$data = get_fields(389);

// Estructura esperada:
$data = array(
    'metal' => array(
        ['text' => 'Gold', 'value' => 500, 'density' => 19.3],
        ['text' => 'Silver', 'value' => 200, 'density' => 10.5],
        ...
    ),
    'stone' => array(
        ['text' => 'Diamond', 'value' => 1000],
        ['text' => 'Ruby', 'value' => 800],
        ...
    ),
    'engravement' => array(
        ['text' => 'Straight', 'value' => 200],
        ['text' => 'Cursive', 'value' => 300],
        ...
    ),
    'size' => array( ... ),
    'width' => array( ... ),
    'thickness' => array( ... ),
    'surface' => array( ... )
)
```

#### ✅ Solución: Reemplazar ACF con Sistema Propio

**Opción A: Usar Options API de WordPress**
```php
// Guardar configuración
update_option('sixwebsoft_variants_config', $config_array);

// Obtener configuración
$data = get_option('sixwebsoft_variants_config', array());
```

**Opción B: Crear Tabla de Base de Datos Propia**
```php
// Más control, mejor rendimiento para mucha data
global $wpdb;
$table_name = $wpdb->prefix . 'sixwebsoft_variants';
```

**Opción C: Usar Post Meta sin ACF**
```php
// Mantener el post 389 pero usar meta keys nativos
update_post_meta(389, 'sixwebsoft_metal_options', $metal_array);
$metal = get_post_meta(389, 'sixwebsoft_metal_options', true);
```

---

### 3️⃣ **WPML (Multilingual)** (OPCIONAL)
- **Estado:** 🟢 OPCIONAL - Ya tiene manejo
- **Código relacionado:**
```php
// Línea 124 - Traducción de productos
$six_original_id = apply_filters('wpml_object_id', $product->get_id(), 'any', FALSE, 'sv');
```
- **Recomendación:** ✅ MANTENER - Funciona como fallback, no afecta si WPML no está activo

---

## 📊 Plan de Eliminación de Dependencias ACF

### Fase 1: Crear Sistema de Configuración Propio

```php
// Nuevo archivo: includes/class-variants-config.php

class SixWebSoft_Variants_Config {
    
    private static $option_name = 'sixwebsoft_variants_settings';
    
    /**
     * Get all variant options
     */
    public static function get_all() {
        return get_option(self::$option_name, self::get_defaults());
    }
    
    /**
     * Get default configuration
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
     * Save variant options
     */
    public static function save($data) {
        return update_option(self::$option_name, $data);
    }
    
    /**
     * Get specific variant type
     */
    public static function get($type) {
        $all = self::get_all();
        return isset($all[$type]) ? $all[$type] : array();
    }
}
```

### Fase 2: Crear Página de Configuración en Admin

```php
// Nuevo archivo: includes/admin/settings-page.php

function sixwebsoft_add_admin_menu() {
    add_submenu_page(
        'woocommerce',
        'Configuración de Variantes',
        'Variantes Config',
        'manage_woocommerce',
        'sixwebsoft-variants',
        'sixwebsoft_render_settings_page'
    );
}
add_action('admin_menu', 'sixwebsoft_add_admin_menu');

function sixwebsoft_render_settings_page() {
    // Interfaz para gestionar:
    // - Metales (nombre, precio, densidad)
    // - Piedras (nombre, precio)
    // - Grabados (nombre, precio)
    // - Tamaños, anchos, grosores, superficies
}
```

### Fase 3: Reemplazar Llamadas a ACF

**ANTES (usando ACF):**
```php
$data = get_fields(389);
```

**DESPUÉS (sistema propio):**
```php
$data = SixWebSoft_Variants_Config::get_all();
```

---

## 🎯 Beneficios de Eliminar ACF

### ✅ Ventajas
1. **Independencia total** - No depender de plugin de terceros
2. **Menor costo** - ACF Pro es de pago
3. **Mejor rendimiento** - Código optimizado para tu caso específico
4. **Control total** - Interfaz diseñada específicamente para variantes
5. **Más ligero** - No cargar ACF completo solo para configuración

### ⚠️ Consideraciones
1. **Migración de datos** - Necesitas migrar la configuración existente del post 389
2. **Interfaz de admin** - Debes crear tu propia interfaz de configuración
3. **Testing** - Probar exhaustivamente después del cambio

---

## 📋 Checklist de Implementación

### Preparación
- [ ] Hacer backup completo de la base de datos
- [ ] Documentar la configuración actual en el post 389
- [ ] Exportar datos de ACF a JSON para respaldo

### Desarrollo
- [ ] Crear clase `SixWebSoft_Variants_Config`
- [ ] Crear página de settings en admin
- [ ] Crear función de migración de datos ACF → nuevo sistema
- [ ] Actualizar todas las referencias a `get_fields()` y `get_field()`
- [ ] Testing en entorno de desarrollo

### Deployment
- [ ] Ejecutar script de migración en producción
- [ ] Verificar que todos los productos funcionen
- [ ] Verificar configuración de variantes
- [ ] Desactivar ACF (si no se usa en otro lado)
- [ ] Monitorear por 1 semana

---

## 💡 Alternativa: Mantener ACF

Si prefieres **NO** eliminar ACF, puedes:

1. **Documentar la dependencia** en el README
2. **Agregar verificación** al inicio del plugin:
```php
if (!function_exists('get_field')) {
    add_action('admin_notices', function() {
        echo '<div class="notice notice-error">';
        echo '<p><strong>WooCommerce SixWebSoft:</strong> Requiere Advanced Custom Fields.</p>';
        echo '</div>';
    });
    return;
}
```

3. **Incluir ACF en el plugin** (si tienes licencia):
   - Copiar ACF dentro de `/includes/acf/`
   - Cargarlo desde tu plugin

---

## 🔄 Next Steps

¿Qué prefieres hacer?

### Opción 1: Eliminar dependencia de ACF
→ Necesitas que implemente el sistema de configuración propio

### Opción 2: Mantener ACF pero mejorar validación
→ Agregar checks y mejor documentación

### Opción 3: Incluir ACF dentro del plugin
→ Requiere licencia de ACF Pro

**Déjame saber qué opción prefieres y continúo con la implementación.**
