<!DOCTYPE html>
<html>
<head>
    <title>Test SixWebSoft - Diagnóstico</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #2d2d2d; color: #fff; }
        .test { margin: 10px 0; padding: 10px; border-left: 4px solid #666; background: #3d3d3d; }
        .success { border-color: #4CAF50; }
        .error { border-color: #f44336; }
        .warning { border-color: #ff9800; }
        pre { background: #1d1d1d; padding: 10px; overflow-x: auto; }
        h1 { color: #4CAF50; }
        h2 { color: #2196F3; }
    </style>
</head>
<body>
    <h1>🔧 SixWebSoft Plugin - Diagnóstico</h1>
    <p>Fecha: <?php echo date('Y-m-d H:i:s'); ?></p>
    <hr>

    <?php
    // Load WordPress
    require_once('../../../wp-load.php');

    echo "<h2>1. WordPress Cargado</h2>";
    echo '<div class="test success">✓ WordPress detectado: Versión ' . get_bloginfo('version') . '</div>';

    // Check WooCommerce
    echo "<h2>2. WooCommerce</h2>";
    if (class_exists('WooCommerce')) {
        global $woocommerce;
        echo '<div class="test success">✓ WooCommerce activo: Versión ' . $woocommerce->version . '</div>';
    } else {
        echo '<div class="test error">✗ WooCommerce NO está activo</div>';
    }

    // Check ACF
    echo "<h2>3. Advanced Custom Fields (ACF)</h2>";
    if (function_exists('get_field')) {
        echo '<div class="test success">✓ ACF disponible (OPCIONAL - ya no es necesario)</div>';
        
        $test_data = get_fields(389);
        if ($test_data) {
            echo '<div class="test warning">⚠️ Configuración encontrada en ACF (post 389)</div>';
            echo '<div class="test">Puedes migrar estos datos al sistema interno desde: WooCommerce → Variantes SixWebSoft</div>';
            echo '<pre>';
            echo "Campos ACF disponibles:\n";
            if (isset($test_data['metal'])) echo "  - Metal: " . count($test_data['metal']) . " opciones\n";
            if (isset($test_data['stone'])) echo "  - Stone: " . count($test_data['stone']) . " opciones\n";
            if (isset($test_data['engravement'])) echo "  - Engravement: " . count($test_data['engravement']) . " opciones\n";
            if (isset($test_data['size'])) echo "  - Size: " . count($test_data['size']) . " opciones\n";
            echo '</pre>';
        } else {
            echo '<div class="test">ACF activo pero sin configuración en post 389</div>';
        }
    } else {
        echo '<div class="test success">✓ ACF NO está activo (correcto - ya no es necesario)</div>';
    }
    
    // Check Internal Config System
    echo "<h2>3b. Sistema de Configuración Interno (NUEVO)</h2>";
    $config = SixWebSoft_Variants_Config::get_all();
    $summary = SixWebSoft_Variants_Config::get_summary();
    
    if (!SixWebSoft_Variants_Config::is_empty()) {
        echo '<div class="test success">✓ Sistema interno configurado correctamente</div>';
        echo '<pre>';
        echo "Configuración interna:\n";
        foreach ($summary as $type => $count) {
            echo "  - " . ucfirst($type) . ": $count opciones\n";
        }
        echo '</pre>';
    } else {
        echo '<div class="test warning">⚠️ Sistema interno SIN configuración</div>';
        if (function_exists('get_field')) {
            echo '<div class="test">Ve a: WooCommerce → Variantes SixWebSoft → Migrar desde ACF</div>';
        } else {
            echo '<div class="test">Ve a: WooCommerce → Variantes SixWebSoft y agrega las opciones manualmente</div>';
        }
    }

    // Check plugin functions
    echo "<h2>4. Funciones del Plugin</h2>";
    
    $functions = [
        'calc_price' => 'Cálculo de precio',
        'custom_attribute' => 'Atributos personalizados',
        'get_options_six' => 'Parser de opciones',
    ];
    
    foreach ($functions as $func => $desc) {
        if (function_exists($func)) {
            echo "<div class='test success'>✓ $desc ($func)</div>";
        } else {
            echo "<div class='test error'>✗ $desc ($func) NO encontrada</div>";
        }
    }

    // Test get_options_six
    if (function_exists('get_options_six')) {
        $test_value = "Gold|500|19.3";
        $result = get_options_six($test_value);
        echo "<div class='test success'>✓ Test get_options_six('$test_value'):</div>";
        echo "<pre>" . print_r($result, true) . "</pre>";
    }

    // Check template files
    echo "<h2>5. Archivos de Plantilla</h2>";
    
    $template_file = __DIR__ . '/templates/single-product/add-to-cart/auto_varient.php';
    if (file_exists($template_file)) {
        echo "<div class='test success'>✓ Plantilla auto_varient.php existe</div>";
        echo "<div class='test'>Ubicación: " . $template_file . "</div>";
    } else {
        echo "<div class='test error'>✗ Plantilla auto_varient.php NO existe</div>";
    }

    $form_file = __DIR__ . '/template/form.php';
    if (file_exists($form_file)) {
        echo "<div class='test success'>✓ Formulario form.php existe</div>";
    } else {
        echo "<div class='test error'>✗ Formulario form.php NO existe</div>";
    }

    // Check AJAX endpoint
    echo "<h2>6. Endpoint AJAX</h2>";
    $ajax_url = admin_url('admin-ajax.php');
    echo "<div class='test success'>✓ WordPress AJAX URL: <a href='$ajax_url' target='_blank' style='color:#4CAF50'>$ajax_url</a></div>";
    echo "<div class='test'>Acción: auto_varient_calculate</div>";
    
    // Test AJAX manually
    echo "<div class='test warning'>";
    echo "<p><strong>Test Manual AJAX:</strong></p>";
    echo "<p>Abre la consola del navegador y ejecuta:</p>";
    echo "<pre style='background:#000;color:#0f0;padding:10px;'>";
    echo "jQuery.post('" . $ajax_url . "', {\n";
    echo "  action: 'auto_varient_calculate',\n";
    echo "  'custom_attr[metal]': 'Gold|500|19.3',\n";
    echo "  'custom_attr[size]': '60',\n";
    echo "  'custom_attr[width]': '6',\n";
    echo "  'custom_attr[thickness]': '2',\n";
    echo "  'custom_attr[engravement]': 'None|0',\n";
    echo "  'custom_attr[stone]': 'None|0',\n";
    echo "  'custom_attr[surface]': 'Polished',\n";
    echo "  'new_custom_attr[laborcost]': '300'\n";
    echo "}, function(r) { console.log(r); });\n";
    echo "</pre>";
    echo "</div>";

    // Test calc_price function
    echo "<h2>7. Test Cálculo de Precio</h2>";
    if (function_exists('calc_price')) {
        $test_data = array(
            'goldprice' => 500,
            'laborcost' => 300,
            'size' => 60,
            'width' => 6,
            'thickness' => 2,
            'metal' => 'Gold',
            'engravement' => 0,
            'stone' => 0,
            'density' => 19.3,
        );
        
        echo "<div class='test'>Datos de prueba:</div>";
        echo "<pre>" . print_r($test_data, true) . "</pre>";
        
        $price = calc_price($test_data);
        echo "<div class='test success'>✓ Precio calculado: $price SEK</div>";
    } else {
        echo "<div class='test error'>✗ No se puede probar - función calc_price no existe</div>";
    }

    // Check for Auto Varient products
    echo "<h2>8. Productos Auto Varient</h2>";
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => 'auto_varient_data',
                'compare' => 'EXISTS'
            )
        )
    );
    
    $products = new WP_Query($args);
    if ($products->have_posts()) {
        echo "<div class='test success'>✓ " . $products->post_count . " productos con configuración Auto Varient</div>";
        echo "<ul>";
        while ($products->have_posts()) {
            $products->the_post();
            $product = wc_get_product(get_the_ID());
            echo "<li><a href='" . get_permalink() . "' target='_blank' style='color:#4CAF50'>" . get_the_title() . "</a> (ID: " . get_the_ID() . ", Tipo: " . $product->get_type() . ")</li>";
        }
        echo "</ul>";
        wp_reset_postdata();
    } else {
        echo "<div class='test warning'>⚠ No hay productos con configuración Auto Varient</div>";
    }

    ?>

    <hr>
    <h2>✅ Resumen</h2>
    <div class="test">
        <p><strong>Para que el cálculo funcione, verifica:</strong></p>
        <ol>
            <li>✓ WooCommerce está activo</li>
            <li>✓ ACF está activo y tiene datos en post 389</li>
            <li>✓ Existen productos con tipo "auto_varient"</li>
            <li>✓ El producto tiene configuración en "Auto Varient Product" tab</li>
            <li>✓ La consola del navegador no muestra errores AJAX</li>
        </ol>
        
        <p><strong>Pasos para probar:</strong></p>
        <ol>
            <li>Ve a un producto tipo "Auto Varient"</li>
            <li>Abre la consola del navegador (F12 → Console)</li>
            <li>Cambia valores del formulario (metal, tamaño, etc.)</li>
            <li>Verifica que aparezcan logs en la consola</li>
            <li>El precio debería actualizarse automáticamente</li>
        </ol>
    </div>

    <div class="test warning">
        <strong>⚠️ Importante:</strong> Elimina este archivo después de usarlo por seguridad.
    </div>
</body>
</html>
