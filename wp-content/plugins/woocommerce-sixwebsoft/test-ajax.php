<!DOCTYPE html>
<html>
<head>
    <title>Test AJAX - SixWebSoft</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        select, input { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #0073aa; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background: #005177; }
        button:disabled { background: #ccc; cursor: not-allowed; }
        .result { margin-top: 20px; padding: 15px; border-radius: 4px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .loading { background: #fff3cd; border: 1px solid #ffeeba; color: #856404; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
        .price-display { font-size: 24px; font-weight: bold; color: #0073aa; margin: 10px 0; }
    </style>
</head>
<body>
    <?php
    // Load WordPress
    require_once('../../../wp-load.php');
    
    if (!is_user_logged_in()) {
        echo '<div class="container"><h1>⚠️ Debes estar logueado</h1><p>Por favor <a href="' . wp_login_url($_SERVER['REQUEST_URI']) . '">inicia sesión</a> primero.</p></div>';
        exit;
    }
    
    // Get configuration from internal system
    $config = sixwebsoft_get_config();
    
    if (SixWebSoft_Variants_Config::is_empty()) {
        echo '<div class="container">';
        echo '<h1>⚠️ No hay configuración</h1>';
        echo '<p>El sistema interno no tiene configuración. Ve a: <a href="' . admin_url('admin.php?page=sixwebsoft-variants') . '">WooCommerce → Variantes SixWebSoft</a></p>';
        if (function_exists('get_field')) {
            echo '<p>O <a href="' . admin_url('admin.php?page=sixwebsoft-variants&action=migrate') . '">migra desde ACF</a>.</p>';
        }
        echo '</div>';
        exit;
    }
    ?>
    
    <div class="container">
        <h1>🧪 Test AJAX - Calculadora de Precios</h1>
        <p>Este formulario prueba directamente el sistema de cálculo de precios sin necesidad de un producto.</p>
        <hr>
        
        <form id="testForm">
            <div class="form-group">
                <label>Metal:</label>
                <select name="custom_attr[metal]" id="metal" required>
                    <?php
                    if (isset($config['metal'])) {
                        foreach ($config['metal'] as $metal) {
                            $value = $metal['text'] . '|' . $metal['value'] . '|' . $metal['density'];
                            echo '<option value="' . esc_attr($value) . '">' . esc_html($metal['text']) . ' (Precio: ' . $metal['value'] . ' SEK, Densidad: ' . $metal['density'] . ')</option>';
                        }
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Tamaño (Size):</label>
                <select name="custom_attr[size]" id="size" required>
                    <?php
                    if (isset($config['size'])) {
                        foreach ($config['size'] as $size) {
                            echo '<option value="' . esc_attr($size['value']) . '">' . esc_html($size['value']) . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Ancho (Width):</label>
                <select name="custom_attr[width]" id="width" required>
                    <?php
                    if (isset($config['width'])) {
                        foreach ($config['width'] as $width) {
                            echo '<option value="' . esc_attr($width['value']) . '">' . esc_html($width['value']) . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Grosor (Thickness):</label>
                <select name="custom_attr[thickness]" id="thickness" required>
                    <?php
                    if (isset($config['thickness'])) {
                        foreach ($config['thickness'] as $thickness) {
                            echo '<option value="' . esc_attr($thickness['value']) . '">' . esc_html($thickness['value']) . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Superficie (Surface):</label>
                <select name="custom_attr[surface]" id="surface" required>
                    <?php
                    if (isset($config['surface'])) {
                        foreach ($config['surface'] as $surface) {
                            echo '<option value="' . esc_attr($surface['text']) . '">' . esc_html($surface['text']) . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Grabado (Engravement):</label>
                <select name="custom_attr[engravement]" id="engravement" required>
                    <?php
                    if (isset($config['engravement'])) {
                        foreach ($config['engravement'] as $eng) {
                            $value = $eng['text'] . '|' . $eng['value'];
                            echo '<option value="' . esc_attr($value) . '">' . esc_html($eng['text']) . ' (+' . $eng['value'] . ' SEK)</option>';
                        }
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Piedra (Stone):</label>
                <select name="custom_attr[stone]" id="stone" required>
                    <?php
                    if (isset($config['stone'])) {
                        foreach ($config['stone'] as $stone) {
                            $value = $stone['text'] . '|' . $stone['value'];
                            echo '<option value="' . esc_attr($value) . '">' . esc_html($stone['text']) . ' (+' . $stone['value'] . ' SEK)</option>';
                        }
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Costo de Mano de Obra (Labour Cost):</label>
                <input type="number" name="new_custom_attr[laborcost]" id="laborcost" value="300" required>
            </div>
            
            <button type="submit" id="calculateBtn">Calcular Precio</button>
        </form>
        
        <div id="result" style="display:none;"></div>
        
        <hr style="margin-top: 30px;">
        <h3>📝 Información de Debug</h3>
        <div style="background:#f8f9fa; padding:10px; border-radius:4px;">
            <p><strong>AJAX URL:</strong> <?php echo admin_url('admin-ajax.php'); ?></p>
            <p><strong>Action:</strong> auto_varient_calculate</p>
            <p><strong>Sistema de Configuración:</strong> Interno (SixWebSoft)</p>
            <p><strong>Configuración ACF:</strong> <?php echo function_exists('get_field') ? 'Disponible (no requerido)' : 'No disponible'; ?></p>
        </div>
    </div>
    
    <script src="<?php echo includes_url('js/jquery/jquery.min.js'); ?>"></script>
    <script>
    jQuery(document).ready(function($) {
        console.log('Test AJAX cargado');
        
        // Auto-calculate on change
        $('select, input').on('change', function() {
            $('#calculateBtn').click();
        });
        
        $('#testForm').on('submit', function(e) {
            e.preventDefault();
            
            var $btn = $('#calculateBtn');
            var $result = $('#result');
            
            $btn.prop('disabled', true).text('Calculando...');
            $result.html('<div class="result loading">⏳ Calculando precio...</div>').show();
            
            var formData = $(this).serialize();
            formData += '&action=auto_varient_calculate';
            
            console.log('Enviando datos:', formData);
            
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                method: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    console.log('Respuesta:', response);
                    
                    $btn.prop('disabled', false).text('Calcular Precio');
                    
                    if (response.success && response.data) {
                        var data = response.data;
                        var html = '<div class="result success">';
                        html += '<h3>✅ Precio Calculado Exitosamente</h3>';
                        html += '<div class="price-display">' + data.price + ' SEK</div>';
                        html += '<h4>Detalles:</h4>';
                        html += '<pre>' + JSON.stringify(data.data, null, 2) + '</pre>';
                        html += '</div>';
                        $result.html(html);
                    } else {
                        var html = '<div class="result error">';
                        html += '<h3>❌ Error en el Cálculo</h3>';
                        html += '<pre>' + JSON.stringify(response, null, 2) + '</pre>';
                        html += '</div>';
                        $result.html(html);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error AJAX:', xhr, status, error);
                    
                    $btn.prop('disabled', false).text('Calcular Precio');
                    
                    var html = '<div class="result error">';
                    html += '<h3>❌ Error de Conexión</h3>';
                    html += '<p><strong>Status HTTP:</strong> ' + xhr.status + '</p>';
                    html += '<p><strong>Error:</strong> ' + error + '</p>';
                    html += '<p><strong>Respuesta del servidor:</strong></p>';
                    html += '<pre>' + xhr.responseText + '</pre>';
                    html += '</div>';
                    $result.html(html);
                }
            });
        });
        
        // Trigger automatic calculation on load
        setTimeout(function() {
            $('#calculateBtn').click();
        }, 500);
    });
    </script>
</body>
</html>
