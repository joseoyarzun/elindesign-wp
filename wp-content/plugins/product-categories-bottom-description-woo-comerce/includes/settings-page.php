<?php

/**
 * Register plugin settings page under WooCommerce menu.
 */
add_action('admin_menu', function () {
    add_submenu_page(
        'woocommerce',
        __( 'Bottom Description Settings', 'pcbdw' ),
        __( 'Bottom Description', 'pcbdw' ),
        'manage_options',
        'pcbdw-settings',
        'pcbdw_render_settings_page'
    );
});



/**
 * Register settings to store CSS options.
 */
add_action('admin_init', 'pcbdw_register_settings');
function pcbdw_register_settings() {
    $sides = ['top', 'right', 'bottom', 'left'];

    foreach (['margin', 'padding'] as $type) {
        foreach ($sides as $side) {
            register_setting('pcbdw_settings_group', "pcbdw_{$type}_{$side}_value");
            register_setting('pcbdw_settings_group', "pcbdw_{$type}_{$side}_unit");
        }
    }

    register_setting('pcbdw_settings_group', 'pcbdw_max_width_value');
    register_setting('pcbdw_settings_group', 'pcbdw_max_width_unit');
    register_setting('pcbdw_settings_group', 'pcbdw_background_color');

    register_setting('pcbdw_settings_group', 'pcbdw_border_width');
    register_setting('pcbdw_settings_group', 'pcbdw_border_color');

    register_setting('pcbdw_settings_group', 'pcbdw_border_radius_value');
    register_setting('pcbdw_settings_group', 'pcbdw_border_radius_unit');

    register_setting('pcbdw_settings_group', 'pcbdw_hidden_taxonomies');
}



/**
 * Render plugin settings page with individual margin/padding controls and unit selectors,
 * plus max-width, background color, border and border-radius settings.
 */
function pcbdw_render_settings_page() {
    $fields = [
        'margin'  => ['Top', 'Right', 'Bottom', 'Left'],
        'padding' => ['Top', 'Right', 'Bottom', 'Left'],
    ];

    $units = ['px', 'em', 'rem', '%'];
    $hidden_taxonomies = get_option('pcbdw_hidden_taxonomies', []);

    if (!is_array($hidden_taxonomies)) {
        $hidden_taxonomies = [];
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Bottom Description Settings', 'pcbdw' ); ?></h1>

        <form method="post" action="options.php">
            <?php settings_fields('pcbdw_settings_group'); ?>

            <table class="form-table">

                <tr>
                    <th colspan="2">
                        <h2 style="margin:0;"><?php esc_html_e( 'Visibility by taxonomy', 'pcbdw' ); ?></h2>
                        <p class="description" style="margin-top:10px;">
                            <?php esc_html_e( 'These options prevent the bottom description from being loaded in the frontend for the selected WooCommerce taxonomies.', 'pcbdw' ); ?>
                        </p>
                    </th>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Hide bottom description in:', 'pcbdw' ); ?></th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="checkbox" name="pcbdw_hidden_taxonomies[]" value="product_cat" <?php checked(in_array('product_cat', $hidden_taxonomies, true)); ?> />
                                <?php esc_html_e( 'Product categories', 'pcbdw' ); ?>
                            </label>
                            <br>

                            <label>
                                <input type="checkbox" name="pcbdw_hidden_taxonomies[]" value="product_tag" <?php checked(in_array('product_tag', $hidden_taxonomies, true)); ?> />
                                <?php esc_html_e( 'Product tags', 'pcbdw' ); ?>
                            </label>
                            <br>

                            <label>
                                <input type="checkbox" name="pcbdw_hidden_taxonomies[]" value="product_brand" <?php checked(in_array('product_brand', $hidden_taxonomies, true)); ?> />
                                <?php esc_html_e( 'Brands', 'pcbdw' ); ?>
                            </label>
                            <br>

                            <label>
                                <input type="checkbox" name="pcbdw_hidden_taxonomies[]" value="pa_all" <?php checked(in_array('pa_all', $hidden_taxonomies, true)); ?> />
                                <?php esc_html_e( 'All attributes', 'pcbdw' ); ?>
                            </label>
                        </fieldset>
                    </td>
                </tr>

                <tr><td colspan="2"><hr style="margin:20px 0;"></td></tr>

                <tr>
                    <th colspan="2">
                        <h2 style="margin:0;"><?php esc_html_e( 'Styles', 'pcbdw' ); ?></h2>
                    </th>
                </tr>

                <?php foreach ($fields as $type => $sides): ?>
                    <tr>
                        <th colspan="2">
                            <h2 style="margin:0;"><?php echo esc_html( ucfirst( translate( $type, 'pcbdw' ) ) ); ?></h2>
                        </th>
                    </tr>

                    <?php foreach ($sides as $side):
                        $key = "pcbdw_{$type}_" . strtolower($side);
                        $val = get_option($key . '_value', '');
                        $unit = get_option($key . '_unit', 'px');
                    ?>
                        <tr>
                            <th scope="row"><?php echo esc_html( ucfirst( translate( $type, 'pcbdw' ) . ' ' . translate( $side, 'pcbdw' ) ) ); ?></th>
                            <td>
                                <input type="number" step="any" name="<?php echo esc_attr($key . '_value'); ?>" value="<?php echo esc_attr($val); ?>" style="width:80px;" />
                                <select name="<?php echo esc_attr($key . '_unit'); ?>">
                                    <?php foreach ($units as $u): ?>
                                        <option value="<?php echo esc_attr($u); ?>" <?php selected($unit, $u); ?>><?php echo esc_html($u); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>

                <tr>
                    <th colspan="2">
                        <h2 style="margin:0;"><?php esc_html_e( 'Other styles', 'pcbdw' ); ?></h2>
                    </th>
                </tr>

                <tr>
                    <th scope="row"><?php esc_html_e( 'Max width', 'pcbdw' ); ?></th>
                    <td>
                        <?php
                        $max_width_value = get_option('pcbdw_max_width_value', '');
                        $max_width_unit = get_option('pcbdw_max_width_unit', 'px');
                        ?>
                        <input type="number" step="any" name="pcbdw_max_width_value" value="<?php echo esc_attr($max_width_value); ?>" style="width:80px;" />
                        <select name="pcbdw_max_width_unit">
                            <?php foreach (['px', '%', 'em', 'rem'] as $unit): ?>
                                <option value="<?php echo esc_attr($unit); ?>" <?php selected($max_width_unit, $unit); ?>><?php echo esc_html($unit); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php esc_html_e( 'Background color', 'pcbdw' ); ?></th>
                    <td>
                        <?php $bg_color = get_option('pcbdw_background_color', '#ffffff'); ?>
                        <input type="color" name="pcbdw_background_color" value="<?php echo esc_attr($bg_color); ?>" />
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php esc_html_e( 'Border', 'pcbdw' ); ?></th>
                    <td>
                        <?php
                        $border_width = get_option('pcbdw_border_width', '');
                        $border_color = get_option('pcbdw_border_color', '#000000');
                        ?>
                        <input type="number" name="pcbdw_border_width" value="<?php echo esc_attr($border_width); ?>" style="width:80px;" /> px
                        &nbsp;&nbsp;&nbsp;
                        <input type="color" name="pcbdw_border_color" value="<?php echo esc_attr($border_color); ?>" />
                        <span style="margin-left:10px;"><?php esc_html_e( '(solid)', 'pcbdw' ); ?></span>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php esc_html_e( 'Border radius', 'pcbdw' ); ?></th>
                    <td>
                        <?php
                        $radius_val = get_option('pcbdw_border_radius_value', '');
                        $radius_unit = get_option('pcbdw_border_radius_unit', 'px');
                        ?>
                        <input type="number" step="any" name="pcbdw_border_radius_value" value="<?php echo esc_attr($radius_val); ?>" style="width:80px;" />
                        <select name="pcbdw_border_radius_unit">
                            <?php foreach (['px', '%', 'em', 'rem'] as $unit): ?>
                                <option value="<?php echo esc_attr($unit); ?>" <?php selected($radius_unit, $unit); ?>><?php echo esc_html($unit); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>

            </table>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
