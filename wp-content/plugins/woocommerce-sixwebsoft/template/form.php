<?php

$global = get_fields(389);
$array = array("stone", "engravement", "metal");
$laborcost = $fields[0]['laborcost'];
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
 						 	        jQuery(document).ready(function () {
            calculate_price();
        });
 						 	function calculate_price(ele='',id='')
 						 	{

								jQuery.ajax({
									url: "<?=home_url();?>?action=auto_varient",
									method: "POST",
									dataType:"json",
									data: jQuery(".cart").serialize(),
									success:function(r){
										console.log(r);
										if(r.status){
											jQuery(".summary.entry-summary .woocommerce-Price-amount.amount").html(r.price+'&nbsp;<span class="woocommerce-Price-currencySymbol"><?=get_woocommerce_currency_symbol();?></span>');
											jQuery(".summary.entry-summary del").html();
											jQuery("#cus__metal").val(r.data.metal);
											jQuery("#cus__stone").val(r.data.stone);
											jQuery("#cus__engravement").val(r.data.engravement);
											jQuery("#cus__surface").val(r.data.surface);
											jQuery("#cus__size").val(r.data.size);
											jQuery("#cus__thickness").val(r.data.thickness);
											jQuery("#cus__width").val(r.data.width);
											jQuery("#cus__price").val(r.price);
											jQuery("#cus__laborcost").val(r.data.laborcost);
										}

									}
								});


 						 	}

 						  </script>
