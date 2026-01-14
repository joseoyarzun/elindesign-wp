<form id="woo_sdwpp" action="<?php echo add_query_arg(['page'=>'dfm-pgppfw', 'tab'=>'payment_per_categories'], $_SERVER['PHP_SELF']); ?>" method="post">
	<table class="form-table">
		<tbody>
			<tr valign="top" class="dfm-row">
				<th class="dfm-label"><?php echo __('Enable/Disable', 'softsdev'); ?></th>
				<td class="dfm-field">
					<label for="dfm_per_categories_enable">
						<?php $checked = dfm_per_categories_enabled(); ?>
						<input type="checkbox" name="dfm_per_categories_enable" id="dfm_per_categories_enable" value="1" <?php echo ($checked)?'checked="checked"':''; ?> />
						<strong><?php echo __('Enable section', 'softsdev'); ?></strong>
					</label>
				</td>
			</tr>
		</tbody>
	</table>
	<?php $available_gateways = WC()->payment_gateways->payment_gateways(); ?>
	<?php
		$args = array(
			'taxonomy'   => 'product_cat',
			'orderby'    => 'name',
			'hide_empty' => false,
		);
		$categories = get_terms( $args );
	?>
	<?php foreach ( $available_gateways as $gateway_id => $gateway ) : ?>
		<h2><?php echo $gateway->title; ?></h2>
		<table class="form-table">
			<tbody>
				<tr valign="top" class="dfm-row">
					<th class="dfm-label"><?php echo __('Include', 'softsdev'); ?></th>
					<td class="dfm-field">
						<?php $field_name = dfm_per_categories_include_field_name($gateway_id); ?>
						<?php $options = dfm_per_categories_include_get_option($gateway_id); ?>
						<select name="<?php echo $field_name; ?>[]" multiple="true" class="chosen_select">
							<?php foreach ($categories as $category): ?>
								<?php $selected = in_array($category->term_id, $options)?' selected="selected"':''; ?>
								<option value="<?php echo $category->term_id; ?>"<?php echo $selected; ?>><?php echo $category->name; ?></option>
							<?php endforeach ?>
						</select>
					</td>
				</tr>
				<tr valign="top" class="dfm-row">
					<th class="dfm-label"><?php echo __('Exclude', 'softsdev'); ?></th>
					<td class="dfm-field">
						<?php $field_name = dfm_per_categories_exclude_field_name($gateway_id); ?>
						<?php $options = dfm_per_categories_exclude_get_option($gateway_id); ?>
						<select name="<?php echo $field_name; ?>[]" multiple="true" class="chosen_select">
							<?php foreach ($categories as $category): ?>
								<?php $selected = in_array($category->term_id, $options)?' selected="selected"':''; ?>
								<option value="<?php echo $category->term_id; ?>"<?php echo $selected; ?>><?php echo $category->name; ?></option>
							<?php endforeach ?>
						</select>
					</td>
				</tr>
			</tbody>
		</table>
	<?php endforeach; ?>

	<input type="submit" value="Save Changes" class="button-large button-primary" />
	<input type="hidden" name="dfm_per_categories" value="1" class="button-large button-primary" />
</form>