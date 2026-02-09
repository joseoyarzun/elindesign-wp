<?php

$global = sixwebsoft_get_config();
$array = array("stone", "engravement", "metal");

// DEBUG: Verificar datos
echo '<!-- DEBUG: form.php cargado - ' . date('Y-m-d H:i:s') . ' -->';
echo '<!-- DEBUG: $fields existe: ' . (isset($fields) ? 'SI' : 'NO') . ' -->';
echo '<!-- DEBUG: $global existe: ' . (isset($global) && is_array($global) ? 'SI (' . count($global) . ' items)' : 'NO') . ' -->';

if(!isset($fields[0]) || !is_array($fields[0])) {
    echo '<div style="border:3px solid red;padding:15px;margin:10px 0;background:#fff;">';
    echo '<strong style="color:red;">⚠️ ERROR: No hay configuración de producto</strong><br>';
    echo 'Este producto no tiene campos configurados. Ve a Productos → Editar → Auto Varient Product';
    echo '</div>';
    return;
}

$laborcost = isset($fields[0]['laborcost']) ? $fields[0]['laborcost'] : 0;
unset($fields[0]['laborcost']);

//echo "<pre>";
//print_r($fields);
//echo "</pre>";
foreach ($fields[0] as $key => $value) {

	if (in_array($key, $array)) {
		?>
	<label><?=ucwords($key);?></label>
	<select id="<?=$key;?>" onChange="calculate_price(this,'<?=$key;?>_name');" name="custom_attr[<?=$key;?>]">
	<?php

		foreach ($global[$key] as $row) {
			if (in_array($row['text'], $value)) {
				$val = $row['text'] . "|" . $row['value'];
				if ($key == "metal") {
					$val = $row['text'] . "|" . $row['value'] . "|" . $row['density'];
				}

				//$name = get_options($row);
				// if (!in_array($row['text'], get_options($value))) {
				// 	continue;
				// }
				?>
		<option value="<?=$val;?>"><?=$row['text'];?></option>
	<?php
}
		}
		?>

	</select>
<?php
} else {
		?>
	<label><?=ucwords($key);?></label>
	<select id="<?=$key;?>" onChange="calculate_price(this,'<?=$key;?>_name');" name="custom_attr[<?=$key;?>]">
	<?php

		foreach ($value as $row) {
			$name = get_options_six($row);
			// if (!in_array($row['text'], get_options($value))) {
			// 	continue;
			// }
			?>
		<option value="<?=$row;?>"><?=$name[0];?></option>
	<?php
}
		?>

	</select>
<?php
}
}
?>



<input type="hidden" name="new_custom_attr[metal]" id="cus__metal">
<input type="hidden" name="new_custom_attr[stone]" id="cus__stone">
<input type="hidden" name="new_custom_attr[engravement]" id="cus__engravement">
<input type="hidden" name="new_custom_attr[surface]" id="cus__surface">
<input type="hidden" name="new_custom_attr[size]" id="cus__size">
<input type="hidden" name="new_custom_attr[thickness]" id="cus__thickness">
<input type="hidden" name="new_custom_attr[width]" id="cus__width">
<input type="hidden" name="custom_price" id="cus__price">
<input type="hidden" name="new_custom_attr[laborcost]" id="cus__laborcost" value="<?php echo $laborcost; ?>">

<!--<span id="total_price" > </span>-->

 						 <script>
							console.log('╔═══════════════════════════════════════╗');
							console.log('║  CALCULADORA DE ANILLOS - DEBUG      ║');
							console.log('╚═══════════════════════════════════════╝');
							console.log('Timestamp:', new Date().toISOString());
							console.log('jQuery disponible:', typeof jQuery !== 'undefined');
							
							if(typeof jQuery === 'undefined') {
								alert('ERROR CRÍTICO: jQuery no está cargado. El cálculo no funcionará.');
							}
							
 						 	jQuery(document).ready(function () {
								console.log('✓ Document Ready');
								console.log('Formularios .cart encontrados:', jQuery('.cart').length);
								
								if(jQuery('.cart').length === 0) {
									console.error('❌ ERROR: No se encuentra el formulario .cart');
								} else {
									console.log('✓ Formulario encontrado, ejecutando cálculo inicial...');
									// Ejecutar cálculo inicial automáticamente
									setTimeout(function() {
										calculate_price();
									}, 500);
								}
							});
							
 						 	function calculate_price(ele='',id='')
 						 	{
								console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
								console.log('calculate_price() llamada');
								if(ele) console.log('Elemento cambiado:', ele, 'ID:', id);
								
								var formData = jQuery(".cart").serialize();
								console.log('Datos serializados:', formData);
								
								// Use WordPress AJAX endpoint
								var ajaxUrl = "<?php echo admin_url('admin-ajax.php'); ?>";
								formData += '&action=auto_varient_calculate';
								
								console.log('URL destino:', ajaxUrl);

								jQuery.ajax({
									url: ajaxUrl,
									method: "POST",
									dataType: "json",
									data: formData,
									beforeSend: function() {
										console.log('⏳ Enviando petición AJAX...');
									},
									success:function(response){
										console.log('✓ Respuesta recibida:', response);
										
										// WordPress AJAX returns response in 'data' property
										var r = response.data || response;
										
										if(r.status === 'true' || r.status === true || response.success){
											console.log('💰 Precio calculado:', r.price);
											
											var priceHTML = r.price + '&nbsp;<span class="woocommerce-Price-currencySymbol"><?=get_woocommerce_currency_symbol();?></span>';
											
											// Intentar múltiples selectores
											var selectores = [
												'.summary.entry-summary .woocommerce-Price-amount.amount',
												'.summary .price .woocommerce-Price-amount',
												'p.price .woocommerce-Price-amount.amount',
												'div.product p.price .amount',
												'.price .amount',
												'p.price bdi',
												'.woocommerce-Price-amount'
											];
											
											var actualizado = false;
											console.log('🔍 Buscando elementos de precio...');
											
											for(var i = 0; i < selectores.length; i++) {
												var $elementos = jQuery(selectores[i]);
												console.log('  Selector ' + (i+1) + ' [' + selectores[i] + ']: ' + $elementos.length + ' elementos');
												
												if($elementos.length > 0 && !actualizado) {
													$elementos.first().html(priceHTML);
													console.log('  ✓ Precio actualizado con selector ' + (i+1));
													actualizado = true;
												}
											}
											
											if(!actualizado) {
												console.error('❌ NO se pudo actualizar el precio - ningún selector funcionó');
												console.log('Estructura HTML actual de .summary:');
												console.log(jQuery('.summary').html());
											}
											
											// Ocultar precio tachado
											jQuery(".summary.entry-summary del, p.price del").hide();
											
											// Actualizar campos ocultos
											jQuery("#cus__metal").val(r.data.metal);
											jQuery("#cus__stone").val(r.data.stone);
											jQuery("#cus__engravement").val(r.data.engravement);
											jQuery("#cus__surface").val(r.data.surface);
											jQuery("#cus__size").val(r.data.size);
											jQuery("#cus__thickness").val(r.data.thickness);
											jQuery("#cus__width").val(r.data.width);
											jQuery("#cus__price").val(r.price);
											jQuery("#cus__laborcost").val(r.data.laborcost);
											
											console.log('✓ Campos actualizados correctamente');
										} else {
											console.error('❌ Error: Status no es válido');
											console.log('Respuesta completa:', response);
										}
										console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
									},
									error: function(xhr, status, error) {
										console.error('╔═══════════════════════════════════════╗');
										console.error('║       ERROR AJAX CRÍTICO              ║');
										console.error('╚═══════════════════════════════════════╝');
										console.error('Status:', status);
										console.error('Error:', error);
										console.error('HTTP Status:', xhr.status);
										console.error('Response:', xhr.responseText);
										
										if(xhr.status === 0) {
											console.error('⚠️ Posible problema: Red, CORS o URL incorrecta');
										} else if(xhr.status === 404) {
											console.error('⚠️ Error 404: La URL no existe');
										} else if(xhr.status === 500) {
											console.error('⚠️ Error 500: Error del servidor PHP');
											console.error('Verifica los logs de PHP para más detalles');
										}
									}
								});
 						 	}

 						  </script>
