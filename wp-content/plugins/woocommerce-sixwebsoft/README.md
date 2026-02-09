# WooCommerce SixWebSoft - Plugin Independiente

## Versión 2.1 - Cambios Importantes

### ✅ Ya No Depende del Theme

Este plugin ahora es **completamente independiente** del tema OceanWP y de las actualizaciones de WooCommerce.

### 🎯 Problema Resuelto

**ANTES (v2.0):**
- El archivo `auto_varient.php` se guardaba en: `wp-content/themes/oceanwp/woocommerce/single-product/add-to-cart/`
- Las actualizaciones de WooCommerce/Theme **borraban** este archivo
- El plugin dejaba de funcionar después de actualizar

**AHORA (v2.1):**
- Todas las plantillas están dentro del plugin en: `wp-content/plugins/woocommerce-sixwebsoft/templates/`
- Las actualizaciones **NO afectan** las plantillas del plugin
- El plugin funciona de manera independiente

## 📁 Nueva Estructura de Archivos

```
woocommerce-sixwebsoft/
├── woocomerce-sixwebsoft.php          (Archivo principal)
├── readme.txt
├── README.md                          (Este archivo)
├── templates/                         (📌 NUEVO - Plantillas propias)
│   └── single-product/
│       └── add-to-cart/
│           └── auto_varient.php       (Template independiente)
├── template/
│   └── form.php                       (Formulario de cálculo)
└── replace_files/                     (Archivos de respaldo - ya no necesarios)
    ├── auto_varient.php
    └── html-order-item-meta.php
```

## 🔧 Cambios Técnicos (v2.1)

### 1. Sistema de Plantillas Independiente

```php
// ANTES - Buscaba en el theme
wc_get_template('single-product/add-to-cart/auto_varient.php');

// AHORA - Usa plantillas del plugin
$template_path = plugin_dir_path(__FILE__) . 'templates/single-product/add-to-cart/auto_varient.php';
include $template_path;
```

### 2. Filtro de WooCommerce

Se agregó el filtro `woocommerce_locate_template` que:
- Intercepta todas las búsquedas de plantillas de WooCommerce
- Verifica si existe la plantilla en el directorio del plugin
- Si existe, usa la del plugin (ignorando theme)
- Fallback a la plantilla estándar si no existe en el plugin

### 3. Constantes Definidas

```php
SIXWEBSOFT_VERSION          // Versión del plugin
SIXWEBSOFT_PLUGIN_DIR       // Ruta del directorio del plugin
SIXWEBSOFT_PLUGIN_URL       // URL del plugin
SIXWEBSOFT_TEMPLATES_DIR    // Directorio de plantillas
```

### 4. Verificación de WooCommerce

Ahora el plugin:
- Verifica si WooCommerce está activo
- Muestra un aviso en el admin si WooCommerce no está activo
- Solo carga el código si WooCommerce está disponible

## 📋 Dependencias

### Requerimientos Obligatorios
- **WordPress:** 5.0 o superior
- **PHP:** 7.2 o superior
- **WooCommerce:** 3.0 o superior (probado hasta 8.0)

### ✅ Ya NO Requiere ACF
- ~~Advanced Custom Fields~~ - **ELIMINADO en v2.1**
- El plugin ahora tiene su propio sistema de configuración
- Si migraste desde una versión anterior con ACF, puedes desactivarlo de forma segura

### Dependencias Opcionales Eliminadas
- ~~Ya no depende de archivos en el theme~~
- ~~Ya no depende de plugins adicionales (excepto WooCommerce)~~
- ~~Ya no depende de ACF (Advanced Custom Fields)~~ ← **NUEVO**

## 🚀 Instalación/Actualización

### Si estás actualizando desde v2.0:

1. **Desactiva el plugin** en WordPress
2. Asegúrate de tener backup de tu base de datos
3. Reemplaza los archivos del plugin con la nueva versión
4. **Activa el plugin** nuevamente
5. ✅ Verifica que los productos tipo "Auto Varient" funcionen correctamente

### Instalación Nueva:

1. Sube la carpeta `woocommerce-sixwebsoft` a `/wp-content/plugins/`
2. Activa el plugin desde el panel de WordPress
3. Asegúrate de que WooCommerce esté activo
4. Ve a **WooCommerce → Variantes SixWebSoft**
5. Configura las opciones de variantes (metal, piedras, tamaños, etc.)
6. Listo para usar

### Si Vienes desde Versión Anterior (con ACF):

1. **Mantén ACF activo temporalmente**
2. Actualiza el plugin a v2.1
3. Ve a **WooCommerce → Variantes SixWebSoft**
4. Verás un aviso para **"Migrar desde ACF"**
5. Haz clic en **"Migrar Ahora"**
6. Verifica que todo funcione correctamente
7. **Desactiva y elimina ACF** (ya no lo necesitas)

**📖 Guía detallada:** Ver [MIGRATION-GUIDE.md](MIGRATION-GUIDE.md)

## 🔍 Verificación Post-Actualización

1. Ve a un producto con tipo "Auto Varient"
2. Verifica que aparezca el formulario de cálculo
3. Prueba hacer un cálculo de precio
4. Agrega al carrito y verifica que se guarden los datos personalizados

## 📝 Notas Importantes

### Puedes Eliminar Archivos del Theme
Los siguientes archivos ya NO son necesarios y pueden eliminarse:
- `wp-content/themes/oceanwp/woocommerce/single-product/add-to-cart/auto_varient.php`

### Carpeta `replace_files/`
La carpeta `replace_files/` con sus archivos de respaldo puede mantenerse por seguridad, pero ya no se utiliza.

## 🛠️ Desarrollo y Personalización

### Modificar Plantillas

Si necesitas personalizar la plantilla `auto_varient.php`:
1. Edita: `wp-content/plugins/woocommerce-sixwebsoft/templates/single-product/add-to-cart/auto_varient.php`
2. Los cambios **NO** se perderán con actualizaciones de WooCommerce o Theme

### Agregar Nuevas Plantillas

1. Crea el archivo en `templates/` siguiendo la estructura de WooCommerce
2. El filtro `sixwebsoft_woocommerce_locate_template` las detectará automáticamente

## ⚠️ Troubleshooting

### El formulario no aparece
- Verifica que WooCommerce esté activo
- Verifica que el producto tenga tipo "Auto Varient"
- Revisa que exista el archivo en `templates/single-product/add-to-cart/auto_varient.php`

### Después de actualizar WooCommerce
- ✅ Tu plugin seguirá funcionando (las plantillas están en el plugin, no en WooCommerce)

### Mensaje "6-02-09)
- ✨ **INDEPENDENCIA DE ACF** - Sistema de configuración propio
- ✨ Nueva interfaz de administración en WooCommerce → Variantes SixWebSoft
- ✨ Migración automática desde ACF con un clic
- ✨ Sistema de plantillas independiente del theme
- ✨ Filtro `woocommerce_locate_template` implementado
- ✨ Constantes del plugin definidas
- ✨ Mejor verificación de dependencia WooCommerce
- ✨ Notificaciones en el admin si falta WooCommerce
- 📝 Documentación completa agregada
- 🐛 Solucionado: Plantillas se borran al actualizar WooCommerce
- 🐛 Solucionado: Precio no se actualiza en productos Auto Varient
- 🔥 REMOVIDO: Dependencia de Advanced Custom Fields (ACF)

### v2.0
- Versión anterior con dependencia de ACF

## Changelog Completo

### v2.1 (2025)
- ✨ Sistema de plantillas independiente del theme
- ✨ Filtro `woocommerce_locate_template` implementado
- ✨ Constantes del plugin definidas
- ✨ Mejor verificación de dependencia WooCommerce
- ✨ Notificaciones en el admin si falta WooCommerce
- 📝 Documentación completa agregada
- 🐛 Solucionado: Plantillas se borran al actualizar WooCommerce

### v2.0
- Versión anterior
