# 🎉 Migración de ACF a Sistema Interno

## ✅ ¡Ya No Dependes de ACF!

Tu plugin WooCommerce SixWebSoft ahora tiene su propio sistema de configuración. Ya no necesitas Advanced Custom Fields.

---

## 📋 Pasos para Migrar

### 1. Accede a la Nueva Página de Configuración

Ve a WordPress Admin:
```
WooCommerce → Variantes SixWebSoft
```

### 2. Ejecuta la Migración Automática

Si tienes ACF activo con configuración en el post 389:

1. Verás un aviso amarillo que dice: **"Detectamos configuración en ACF"**
2. Haz clic en el botón: **"Migrar Ahora"**
3. El sistema copiará automáticamente todos los datos de ACF

**Datos que se migrarán:**
- ✅ Metales (nombre, precio, densidad)
- ✅ Piedras (nombre, precio)  
- ✅ Grabados (nombre, precio)
- ✅ Tamaños
- ✅ Anchos
- ✅ Grosores
- ✅ Superficies

### 3. Verifica la Migración

Después de migrar, verás un resumen mostrando cuántas opciones se importaron de cada tipo.

**Ir a verificar:**
```
WooCommerce → Variantes SixWebSoft
```

Deberías ver todas tus opciones organizadas en pestañas.

---

## 🎯 Si NO Tienes ACF

No hay problema. Puedes agregar la configuración manualmente:

1. Ve a: **WooCommerce → Variantes SixWebSoft**
2. Selecciona cada pestaña (Metal, Piedras, etc.)
3. Haz clic en **"➕ Agregar Nueva Opción"**
4. Completa los campos
5. Haz clic en **"💾 Guardar Todos los Cambios"**

---

## 📊 Nueva Interfaz de Administración

### Pestañas Disponibles:

#### 1. **⚙️ Metal**
Campos:
- Nombre (ej: "Gold", "Silver")
- Precio por gramo (SEK)
- Densidad (g/cm³)

#### 2. **💎 Piedras**
Campos:
- Nombre (ej: "Diamond", "Ruby")
- Precio adicional (SEK)

#### 3. **✍️ Grabados**
Campos:
- Nombre (ej: "Straight", "Cursive")
- Precio adicional (SEK)

#### 4. **📏 Tamaños**
Campos:
- Valor numérico (ej: 50, 52, 54, etc.)

#### 5. **↔️ Anchos**
Campos:
- Valor en mm (ej: 4, 5, 6, etc.)

#### 6. **⬍ Grosores**
Campos:
- Valor en mm (ej: 1.5, 2, 2.5, etc.)

#### 7. **✨ Superficies**
Campos:
- Nombre del tipo de acabado (ej: "Polished", "Matte")

---

## 🔄 Funciones de la Interfaz

### Agregar Opción
1. Haz clic en **"➕ Agregar Nueva Opción"**
2. Se agregará una fila nueva al final
3. Completa los campos
4. Haz clic en **"💾 Guardar Todos los Cambios"**

### Editar Opción
1. Simplemente modifica los valores en los campos
2. Haz clic en **"💾 Guardar Todos los Cambios"**

### Eliminar Opción
1. Haz clic en el botón **"🗑️ Eliminar"** de la fila
2. Confirma la eliminación
3. Haz clic en **"💾 Guardar Todos los Cambios"**

---

## ⚠️ Después de Migrar

### ¿Puedo Desactivar ACF?

**¡SÍ!** Una vez que hayas migrado los datos:

1. ✅ Verifica que todo funcione en un producto de prueba
2. ✅ Ve a: Plugins → Advanced Custom Fields
3. ✅ Haz clic en "Desactivar"
4. ✅ (Opcional) Elimina el plugin ACF

### ¿Qué pasa con el Post 389?

- Los datos originales en ACF (post 389) **NO se eliminan** automáticamente
- Puedes mantenerlos como respaldo
- O eliminar el post manualmente si ya no lo necesitas

### ¿Qué pasa con mis Productos?

- **Nada cambia** en tus productos existentes
- Los productos tipo "Auto Varient" seguirán funcionando
- Los pedidos existentes mantienen su configuración
- El cálculo de precios funciona igual

---

## 🧪 Verificar que Todo Funciona

### Test 1: Página de Configuración
```
http://localhost/elindesign/wp-admin/admin.php?page=sixwebsoft-variants
```
✅ Deberías ver todas tus opciones organizadas en pestañas

### Test 2: Diagnóstico
```
http://localhost/elindesign/wp-content/plugins/woocommerce-sixwebsoft/test-diagnostico.php
```
✅ Debe decir "Sistema interno configurado correctamente"

### Test 3: Producto Real
1. Ve a un producto tipo "Auto Varient"
2. Verifica que aparezcan todos los campos (metal, tamaño, etc.)
3. Cambia valores y verifica que el precio se actualice

### Test 4: Test AJAX
```
http://localhost/elindesign/wp-content/plugins/woocommerce-sixwebsoft/test-ajax.php
```
✅ El formulario debe calcular el precio correctamente

---

## 🐛 Solución de Problemas

### No veo mis opciones después de migrar

**Solución:**
1. Ve a: **WooCommerce → Variantes SixWebSoft**
2. Verifica que las pestañas tengan números (ej: "Metal (5)")
3. Si dice "(0)", la migración no funcionó
4. Verifica que ACF esté activo y tenga datos en post 389
5. Vuelve a intentar la migración

### El cálculo de precio no funciona

**Solución:**
1. Ejecuta el test de diagnóstico
2. Verifica que diga "Sistema interno configurado"
3. Si dice "SIN configuración", agrega las opciones manualmente
4. Verifica los logs en la consola del navegador (F12)

### "Call to undefined function get_fields()"

**¡Esto es normal!** Significa que ACF no está activo.

**Solución:**
- Si ya migraste los datos, todo está bien
- El plugin usa ahora `sixwebsoft_get_config()` internamente
- No necesitas hacer nada

---

## 📚 Ventajas del Nuevo Sistema

### ✅ Independencia Total
- No dependes de ACF (ni gratis ni Pro)
- Un plugin menos en tu sitio
- Mejor rendimiento

### ✅ Interfaz Especializada
- Diseñada específicamente para variantes de productos
- Más fácil de usar que ACF
- Pestañas organizadas por tipo

### ✅ Cero Conflictos
- No más problemas con actualizaciones de ACF
- No más "llamadas a funciones no definidas"
- Código más limpio y mantenible

### ✅ Portabilidad
- Fácil de exportar/importar
- Los datos están en `wp_options`
- Fácil de respaldar

---

## 🔧 Para Desarrolladores

### Acceder a la Configuración desde Código

```php
// Obtener toda la configuración
$config = SixWebSoft_Variants_Config::get_all();

// Obtener un tipo específico
$metales = SixWebSoft_Variants_Config::get('metal');

// Agregar una opción
SixWebSoft_Variants_Config::add_option('metal', array(
    'text' => 'Platinum',
    'value' => 800,
    'density' => 21.45
));

// Guardar todo
SixWebSoft_Variants_Config::save($config);

// Obtener resumen
$summary = SixWebSoft_Variants_Config::get_summary();
// ['metal' => 5, 'stone' => 3, ...]

// Verificar si está vacío
if (SixWebSoft_Variants_Config::is_empty()) {
    // No hay configuración
}
```

### Funciones de Compatibilidad

Para facilitar la migración, existen funciones de compatibilidad:

```php
// Estas funcionan igual, pero usan el sistema interno
$config = sixwebsoft_get_config();
$fields = sixwebsoft_get_fields(); // Reemplaza get_fields(389)
$field = sixwebsoft_get_field('metal'); // Reemplaza get_field()
```

---

## ⚡ Migración Express (Resumen)

1. **WooCommerce → Variantes SixWebSoft**
2. **Clic en "Migrar Ahora"**
3. **Verificar que todo esté OK**
4. **Desactivar ACF**
5. **¡Listo!**

---

**¿Necesitas ayuda?** Revisa el archivo `TROUBLESHOOTING.md` o contacta al soporte.

**Versión:** 2.1  
**Actualizado:** <?php echo date('Y-m-d'); ?>
