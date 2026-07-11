******************************************
WP-Theme or WooCommerce UPDATE 
******************************************

"Things to Consider During WP Update"

Put html-order-item-meta.php in /wp-content/plugins/woocommerce/includes/admin/meta-boxes/views

File "auto_varient.php" is automatically removed by an OceanWP UPDATE. Add "auto_varient.php" in the following path:

Path: /wp-content/themes/oceanwp/woocommerce/single-product/add-to-cart/auto_varient.php


****************************************
Dependencies
****************************************
- Advanced Custom Fields
- Advanced Custom Fields: Repeater Field
- Custom Post Type UI
- WooCommerce (html-order-item-meta.php replaced)
- Theme OcenWp (auto_varient.php added)

****************************************
Documentation English
****************************************

**File: woocommerce-scancoordesign.php**
-----------------------------------
This code is a WordPress plugin intended for WooCommerce. Here's a summary of the main functions and features of the plugin:

1. **Registration of a New Product Type (`auto_varient`):**
   - The plugin registers a new product type called `auto_varient` that can be selected when creating a product in WooCommerce.

2. **Auto Varient Product Configuration Page:**
   - Adds a new tab in the product settings called "Auto Varient Product."
   - Allows configuration of various fields, including `laborcost`, `metal`, `stone`, `engravement`, `size`, `surface`, `thickness`, and `width`.

3. **Product Price Calculation:**
   - Includes a `calc_price` function that performs calculations based on various factors such as gold cost (`goldprice`), labor cost (`laborcost`), size (`size`), thickness (`thickness`), metal (`metal`), engraving (`engravement`), stone (`stone`), and density (`density`).

4. **Admin Product Interface:**
   - The product admin interface includes custom fields such as `laborcost`, `metal`, `stone`, `engravement`, `size`, `surface`, `thickness`, and `width`.

5. **Cart Session Handling:**
   - The plugin handles custom fields in the cart, such as `_custom_options`, `_my_custom_price`, and `_custom_calculation`, used to store custom product information in the cart.

6. **Cart Price Modification:**
   - Uses hooks to dynamically modify product prices in the cart based on selected options.

7. **Multiple Selection Compatibility (`select2`):**
   - Uses the `select2` script to provide a user-friendly interface for selecting multiple options in the metal, stone, and engraving fields.

8. **Order Management Functions:**
   - Displays additional information on the order details page, such as labor cost (`Labour Cost`).

9. **Settings Handling:**
   - Stores product-specific settings in the database.

10. **AJAX Handling:**
   - Includes an AJAX action (`auto_varient`) to dynamically calculate the product price based on selected options.

11. **Display Conditions in Admin Interface:**
    - Shows or hides certain fields depending on the selected product type.

This code appears to be a comprehensive implementation of a WooCommerce plugin that adds functionalities and custom fields for managing `auto_varient`-type products.

**File: html-order-item-meta.php**
------------------------------------

This code is part of a WooCommerce template for displaying and editing product metadata on the order details page. Here's a description of what the code does:

1. **Security Check:**
   ```php
   if (!defined('ABSPATH')) {
       exit;
   ```
   This block checks that WordPress is loaded and direct access to the script is blocked.

2. **Hidden Metadata Filtering:**
   ```php
   $hidden_order_itemmeta = apply_filters(
       'woocommerce_hidden_order_itemmeta', array(
           '_qty',
           '_tax_class',
           '_product_id',
           '_variation_id',
           '_line_subtotal',
           '_line_subtotal_tax',
           '_line_total',
           '_line_tax',
           'method_id',
           'cost',
           '_reduced_stock',
       )
   );
   ```
   An array of metadata to be hidden in order viewing and editing is created. This is later used to avoid displaying certain types of metadata.

3. **Metadata Display in View Mode:**
   ```php
   <div class="view">
       <?php if ($meta_data = $item->get_formatted_meta_data('')): ?>
           <table cellspacing="0" class="display_meta">
               <!-- ... Loop to display metadata ... -->
           </table>
       <?php endif; ?>
   </div>
   ```
   Displays item metadata in view mode if metadata is available.

4. **Metadata Editing in View Mode:**
   ```php
   <div class="edit" style="display: none;">
       <table class="meta" cellspacing="0">
           <tbody class="meta_items">
               <!-- ... Loop to display metadata in edit mode ... -->
           </tbody>
           <tfoot>
               <!-- ... Button to add new metadata ... -->
           </tfoot>
       </table>
   </div>
   ```
   Displays item metadata in edit mode if metadata is available. Also provides a button to add new metadata.

5. **Metadata Editing Fields:**
   ```php
   <tr data-meta_id="<?php echo esc_attr($meta_id); ?>">
       <td>
           <input type="text" maxlength="255" placeholder="<?php esc_attr_e('Name (required)', 'woocommerce');?>" name="meta_key[<?php echo esc_attr($item_id); ?>][<?php echo esc_attr($meta_id); ?>]" value="<?php echo esc_attr($meta->key); ?>" />
           <textarea placeholder="<?php esc_attr_e('Value (required)', 'woocommerce');?>" name="meta_value[<?php echo esc_attr($item_id); ?>][<?php echo esc_attr($meta_id); ?>]"><?php echo esc_textarea(rawurldecode($meta->value)); ?></textarea>
       </td>
       <td width="1%"><button class="remove_order_item_meta button">&times;</button></td>
   </tr>
   ```
   Displays text input and textarea fields for editing the name and value of metadata. Also provides a button to remove the metadata.

In general, this code creates an interface for displaying and editing product metadata in the context of an order in WooCommerce.

**File: template/form.php**
---------------------------

This PHP code is part of a form on a WordPress page, likely related to WooCommerce functionality. Let's break it down step by step:

1. **Getting Global Data:**
   ```php
   $global = get_fields(389);
   ```
   Custom fields from the entry with ID 389 are obtained. This information seems to contain global settings for options such as stones (`stone`), engravings (`engravement`), metals (`metal`), and others.

2. **Variable Setup:**
   ```php
   $array = array("stone", "engravement", "metal");
   $laborcost = $fields[0]['laborcost'];
   unset($fields[0]['laborcost']);
   ```
   An array `$array` is defined, containing the keys that will be used to create dropdown lists in the form. Also, the labor cost (`laborcost`) is extracted, and this field is removed from the main array (`$fields`).

3. **Form Generation:**
   ```php
   foreach ($fields[0] as $key => $value) {
       // ... Code to generate labels and dropdowns
   }
   ```
   The `$fields[0]` array is looped through to generate labels and dropdown lists based on the keys and values obtained.

4. **Hidden Inputs:**
   ```php
   <input type="hidden" name="new_custom_attr[metal]" id="cus__metal">
   <!-- Other hidden inputs with default values -->
   ```
   Hidden inputs are created to store certain selected values in the dropdown lists. These values will be used later in the JavaScript function `calculate_price`.

5. **JavaScript Script:**
   ```javascript
  

 jQuery(document).ready(function () {
       calculate_price();
   });

   function calculate_price(ele='',id='')
   {
       // ... Code to make an AJAX request and update the price on the page
   }
   ```
   jQuery is used to run the `calculate_price` function when the page loads and also every time a change occurs in the dropdown lists. The function makes an AJAX request to `admin-ajax.php?action=auto_varient` with the form data and updates the displayed price on the page.

In summary, this code is part of a form that allows users to select options related to jewelry (stones, engravings, metals, etc.) and dynamically updates the price based on the selections made. The core functionality lies in the JavaScript script `calculate_price`, which uses AJAX to fetch and display the updated price without reloading the page.

**File: auto_varient.php**
------------------------

This is a PHP code snippet that is part of the WooCommerce shopping cart system in WordPress. This specific snippet is a template file used to display the add-to-cart form on a simple product's page.

Let's break down the code:

1. **Check for Allowed Purchase:**
   ```php
   if (!$product->is_purchasable()) {
       return;
   }
   ```
   It checks if the product is purchasable. If it's not, the execution of the rest of the code is stopped.

2. **Display Stock Status:**
   ```php
   echo wc_get_stock_html($product);
   ```
   The stock status of the product is displayed. This snippet likely shows whether the product is in stock and how many units are available.

3. **Start of Add-to-Cart Form:**
   ```php
   if ($product->is_in_stock()):
   ```
   It checks if the product is in stock before showing the add-to-cart form. If the product is in stock, the form is displayed.

4. **Actions Before the Form:**
   ```php
   <?php do_action('woocommerce_before_add_to_cart_form');?>
   ```
   WooCommerce actions are executed before displaying the add-to-cart form.

5. **Add-to-Cart Form:**
   ```php
   <form class="cart" action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>" method="post" enctype='multipart/form-data'>
   ```
   The add-to-cart form is initiated. The action (`action`) to which the form will be submitted (usually the product's URL) is specified.

6. **Custom Attributes:**
   ```php
   <?php custom_attribute($product); ?>
   ```
   A function `custom_attribute` is called, which probably displays custom fields or specific attributes of the product.

7. **Quantity Input:**
   ```php
   <?php woocommerce_quantity_input(...); ?>
   ```
   An input field is displayed for the quantity of the product the user wants to add to the cart.

8. **Add-to-Cart Button:**
   ```php
   <button type="submit" name="add-to-cart" value="<?php echo esc_attr($product->get_id()); ?>" class="single_add_to_cart_button button alt"><?php echo esc_html($product->single_add_to_cart_text()); ?></button>
   ```
   The button that the user can click to add the product to the cart is displayed.

9. **Actions After the Form:**
   ```php
   <?php do_action('woocommerce_after_add_to_cart_form');?>
   ```
   WooCommerce actions are executed after displaying the add-to-cart form.

In summary, this code generates the add-to-cart form for simple products in WooCommerce, displaying information about the stock and allowing users to specify the desired quantity before adding the product to the cart.


****************************************
Documentation spanish
****************************************

ARCHIVO woocommerce-scancoordesign.php
-----------------------------------
Este código es un plugin de WordPress destinado a WooCommerce. Aquí hay un resumen de las principales funciones y características del plugin:

1. **Registro de un nuevo tipo de producto (`auto_varient`):**
   - El plugin registra un nuevo tipo de producto llamado `auto_varient` que se puede seleccionar al crear un producto en WooCommerce.

2. **Página de configuración del producto `auto_varient`:**
   - Agrega una nueva pestaña en la configuración del producto llamada "Auto Varient Product".
   - Permite la configuración de varios campos, incluidos `laborcost`, `metal`, `stone`, `engravement`, `size`, `surface`, `thickness` y `width`.

3. **Cálculo del precio del producto:**
   - Se incluye una función `calc_price` que realiza cálculos basados en varios factores, como el costo del oro (`goldprice`), el costo de la mano de obra (`laborcost`), el tamaño (`size`), el grosor (`thickness`), el metal (`metal`), grabado (`engravement`), piedra (`stone`), y densidad (`density`).

4. **Interfaz de usuario en la administración del producto:**
   - La interfaz de administración del producto incluye campos personalizados como `laborcost`, `metal`, `stone`, `engravement`, `size`, `surface`, `thickness` y `width`.

5. **Manejo de sesiones del carrito:**
   - El plugin maneja campos personalizados en el carrito, como `_custom_options`, `_my_custom_price` y `_custom_calculation`, que se utilizan para almacenar información personalizada sobre el producto en el carrito.

6. **Modificación de precios en el carrito:**
   - Utiliza ganchos para modificar dinámicamente el precio de los productos en el carrito según las opciones seleccionadas.

7. **Compatibilidad con múltiples selecciones (`select2`):**
   - Utiliza el script `select2` para proporcionar una interfaz de usuario amigable para la selección de múltiples opciones en los campos de metal, piedra y grabado.

8. **Funciones de administración de pedidos:**
   - Muestra información adicional en la página de detalles del pedido, como el costo de la mano de obra (`Labour Cost`).

9. **Manejo de configuraciones:**
   - Almacena configuraciones específicas del producto en la base de datos.

10. **Manejo de AJAX:**
   - Incluye una acción de AJAX (`auto_varient`) para calcular dinámicamente el precio del producto en función de las opciones seleccionadas.

11. **Condiciones de visualización en la interfaz de administración:**
    - Muestra u oculta ciertos campos dependiendo del tipo de producto seleccionado.

Este código parece ser una implementación completa de un plugin para WooCommerce que agrega funcionalidades y campos personalizados para gestionar productos específicos del tipo `auto_varient`.

--------------
ARCHIVO html-order-item-meta.php
--------------------------------

Este código es parte de una plantilla de WooCommerce para mostrar y editar metadatos de productos en la página de detalles del pedido. Aquí hay una descripción de lo que hace el código:

1. **Verificación de Seguridad:**
   ```php
   if (!defined('ABSPATH')) {
       exit;
   }
   ```
   Este bloque verifica que WordPress esté cargado y que el acceso directo al script esté bloqueado.

2. **Filtrado de Metadatos Ocultos:**
   ```php
   $hidden_order_itemmeta = apply_filters(
       'woocommerce_hidden_order_itemmeta', array(
           '_qty',
           '_tax_class',
           '_product_id',
           '_variation_id',
           '_line_subtotal',
           '_line_subtotal_tax',
           '_line_total',
           '_line_tax',
           'method_id',
           'cost',
           '_reduced_stock',
       )
   );
   ```
   Se crea un array de metadatos que se deben ocultar en la visualización y edición de la orden. Esto se utiliza posteriormente para evitar mostrar ciertos tipos de metadatos.

3. **Visualización de Metadatos en la Vista:**
   ```php
   <div class="view">
       <?php if ($meta_data = $item->get_formatted_meta_data('')): ?>
           <table cellspacing="0" class="display_meta">
               <!-- ... Loop para mostrar metadatos ... -->
           </table>
       <?php endif; ?>
   </div>
   ```
   Muestra los metadatos del artículo en modo de vista si hay metadatos disponibles.

4. **Edición de Metadatos en la Vista:**
   ```php
   <div class="edit" style="display: none;">
       <table class="meta" cellspacing="0">
           <tbody class="meta_items">
               <!-- ... Loop para mostrar metadatos en modo de edición ... -->
           </tbody>
           <tfoot>
               <!-- ... Botón para agregar nuevos metadatos ... -->
           </tfoot>
       </table>
   </div>
   ```
   Muestra los metadatos del artículo en modo de edición si hay metadatos disponibles. También proporciona un botón para agregar nuevos metadatos.

5. **Campos de Edición de Metadatos:**
   ```php
   <tr data-meta_id="<?php echo esc_attr($meta_id); ?>">
       <td>
           <input type="text" maxlength="255" placeholder="<?php esc_attr_e('Name (required)', 'woocommerce');?>" name="meta_key[<?php echo esc_attr($item_id); ?>][<?php echo esc_attr($meta_id); ?>]" value="<?php echo esc_attr($meta->key); ?>" />
           <textarea placeholder="<?php esc_attr_e('Value (required)', 'woocommerce');?>" name="meta_value[<?php echo esc_attr($item_id); ?>][<?php echo esc_attr($meta_id); ?>]"><?php echo esc_textarea(rawurldecode($meta->value)); ?></textarea>
       </td>
       <td width="1%"><button class="remove_order_item_meta button">&times;</button></td>
   </tr>
   ```
   Muestra campos de entrada de texto y área de texto para editar el nombre y el valor de los metadatos. También proporciona un botón para eliminar el metadato.

En general, este código crea una interfaz para mostrar y editar metadatos de productos en el contexto de un pedido en WooCommerce.

--------------
ARCHIVO template/form.php
-------------------------

Este código PHP es una parte de un formulario en una página de WordPress, probablemente relacionada con la funcionalidad de WooCommerce. Vamos a analizarlo paso a paso.

1. **Obtención de datos globales:**
   ```php
   $global = get_fields(389);
   ```
   Se obtienen los campos personalizados de la entrada con ID 389. Esta información parece contener configuraciones globales para opciones como piedras (`stone`), grabados (`engravement`), metales (`metal`), y otros.

2. **Configuración de variables:**
   ```php
   $array = array("stone", "engravement", "metal");
   $laborcost = $fields[0]['laborcost'];
   unset($fields[0]['laborcost']);
   ```
   Se define un array `$array` que contiene las claves que se usarán para crear listas desplegables en el formulario. También, se extrae el costo de mano de obra (`laborcost`) y se elimina este campo del array principal (`$fields`).

3. **Generación del formulario:**
   ```php
   foreach ($fields[0] as $key => $value) {
       // ... Código para generar etiquetas y listas desplegables
   }
   ```
   Se recorre el array `$fields[0]` para generar etiquetas y listas desplegables en función de las claves y valores obtenidos.

4. **Inputs ocultos:**
   ```php
   <input type="hidden" name="new_custom_attr[metal]" id="cus__metal">
   <!-- Otros inputs ocultos con valores predeterminados -->
   ```
   Se crean inputs ocultos que almacenarán ciertos valores seleccionados en las listas desplegables. Estos valores se utilizarán más adelante en la función JavaScript `calculate_price`.

5. **Script JavaScript:**
   ```javascript
   jQuery(document).ready(function () {
       calculate_price();
   });

   function calculate_price(ele='',id='')
   {
       // ... Código para hacer una solicitud AJAX y actualizar el precio en la página
   }
   ```
   Se utiliza jQuery para ejecutar la función `calculate_price` al cargar la página y también cada vez que se produce un cambio en las listas desplegables. La función realiza una solicitud AJAX a través de `admin-ajax.php?action=auto_varient` con los datos del formulario y actualiza el precio mostrado en la página.

En resumen, este código forma parte de un formulario que permite a los usuarios seleccionar opciones relacionadas con la joyería (piedras, grabados, metales, etc.) y actualiza dinámicamente el precio según las selecciones realizadas. La funcionalidad principal está en el script JavaScript `calculate_price`, que utiliza AJAX para obtener y mostrar el precio actualizado sin recargar la página.

-------------------
ARCHIVO auto_varient.php
------------------------

Este es un fragmento de código en PHP que forma parte del sistema de carrito de compras de WooCommerce en WordPress. Este fragmento específico es un archivo de plantilla utilizado para mostrar el formulario de añadir al carrito en la página de un producto simple.

Vamos a desglosar el código:

1. **Chequeo de Compra Permitida:**
   ```php
   if (!$product->is_purchasable()) {
       return;
   }
   ```
   Se verifica si el producto es comprable. Si no lo es, se detiene la ejecución del resto del código.

2. **Mostrar Estado de Stock:**
   ```php
   echo wc_get_stock_html($product);
   ```
   Se muestra el estado del stock del producto. Este fragmento probablemente muestra si el producto está en stock y cuántas unidades están disponibles.

3. **Inicio del Formulario de Añadir al Carrito:**
   ```php
   if ($product->is_in_stock()):
   ```
   Se verifica si el producto está en stock antes de mostrar el formulario de añadir al carrito. Si el producto está en stock, se procede a mostrar el formulario.

4. **Acciones Antes del Formulario:**
   ```php
   <?php do_action('woocommerce_before_add_to_cart_form');?>
   ```
   Se ejecutan acciones de WooCommerce antes de mostrar el formulario de añadir al carrito.

5. **Formulario de Añadir al Carrito:**
   ```php
   <form class="cart" action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>" method="post" enctype='multipart/form-data'>
   ```
   Se inicia el formulario de añadir al carrito. Se especifica la acción (`action`) a la que se enviará el formulario (generalmente la URL del producto).

6. **Atributos Personalizados:**
   ```php
   <?php custom_attribute($product); ?>
   ```
   Se llama a una función `custom_attribute` que probablemente muestra campos personalizados o atributos específicos del producto.

7. **Entrada de Cantidad:**
   ```php
   <?php woocommerce_quantity_input(...); ?>
   ```
   Se muestra un campo de entrada para la cantidad del producto que el usuario desea añadir al carrito.

8. **Botón de Añadir al Carrito:**
   ```php
   <button type="submit" name="add-to-cart" value="<?php echo esc_attr($product->get_id()); ?>" class="single_add_to_cart_button button alt"><?php echo esc_html($product->single_add_to_cart_text()); ?></button>
   ```
   Se muestra el botón que el usuario puede hacer clic para añadir el producto al carrito.

9. **Acciones Después del Formulario:**
   ```php
   <?php do_action('woocommerce_after_add_to_cart_form');?>
   ```
   Se ejecutan acciones de WooCommerce después de mostrar el formulario de añadir al carrito.

En resumen, este código genera el formulario de añadir al carrito para productos simples en WooCommerce, mostrando información sobre el stock y permitiendo a los usuarios especificar la cantidad deseada antes de añadir el producto al carrito.
