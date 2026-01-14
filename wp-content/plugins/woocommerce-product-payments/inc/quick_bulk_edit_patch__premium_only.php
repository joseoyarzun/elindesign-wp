<?php
/**
 * QUICK Edit
 */
add_action('woocommerce_product_quick_edit_end', 'dreamfox_sd_quick_edit_payment');

function dreamfox_sd_quick_edit_payment() {
    /*
      Notes:
      Take a look at the name of the text field, '_custom_field_demo', that is the name of the custom field, basically its just a post meta
      The value of the text field is blank, it is intentional
     */
    ?>
    <br class="clear">
    <div class="shipping_section">
        <label class="">
            <span class="title"><?php _e('Payment', 'woocommerce'); ?></span>
            <span class="input-text-wrap">
                <ul class="quick_payment">

                    <?php
                    global $woo;
                    $woo = new WC_Payment_Gateways();
                    $payments = $woo->payment_gateways;
                    foreach ($payments as $pay) {
                        if (apply_filters('softsdev_show_disabled_gateways', false) || $pay->enabled == 'no') {
                            continue;
                        }
                        $checked = '';
                        ?>  
                        <li>
                            <label for="payment_<?php echo $pay->id; ?>"><input type="checkbox" <?php echo $checked; ?> value="<?php echo $pay->id; ?>" name="pays[]" id="payment_<?php echo $pay->id; ?>" /><?php echo $pay->title; ?></label>
                        </li>
                        <?php
                    }
                    ?>
                </ul>
            </span>
        </label>
    </div>
    <?php
}

/**
 * BULK Edit
 */
add_action('woocommerce_product_bulk_edit_end', 'dreamfox_sd_bulk_edit_payment', 10, 2);

function dreamfox_sd_bulk_edit_payment() {
    ?>
    <div class="inline-edit-group">
        <label>
            <span class="title"><?php _e('Payment', 'woocommerce'); ?></span>
            <ul class="quick_payment">
                <?php
                global $woo;
                $woo = new WC_Payment_Gateways();
                $payments = $woo->payment_gateways;
                foreach ($payments as $pay) {
                    if (apply_filters('softsdev_show_disabled_gateways', false) || $pay->enabled == 'no') {
                        continue;
                    }
                    $checked = '';
                    ?>  
                    <li>
                        <label for="payment_<?php echo $pay->id; ?>" class="width100"><input type="checkbox" <?php echo $checked; ?> value="<?php echo $pay->id; ?>" name="pays[]" id="payment_<?php echo $pay->id; ?>" /><?php echo $pay->title; ?></label>

                    </li>
                <?php } ?>
            </ul>
        </label>
    </div>
    <?php
}

/**
 * BULK AND QUICK EDIT
 */
add_action('woocommerce_product_quick_edit_save', 'dreamfox_sd_save_quick_edit_payment', 10, 1);
add_action('woocommerce_product_bulk_edit_save', 'dreamfox_sd_save_quick_edit_payment', 10, 1);

function dreamfox_sd_save_quick_edit_payment($product) {
    if (isset($_REQUEST['pays'])) {
        $product_id = $product->id;
        /**
         * product id saving
         */
        $productIds = get_option('woocommerce_product_apply', array());
        if (is_array($productIds) && !in_array($product_id, $productIds)) {
            $productIds[] = $product_id;
            update_option('woocommerce_product_apply', $productIds);
        }
        $payments = array();
        if ($_REQUEST['pays']) {
            foreach ($_REQUEST['pays'] as $pay) {
                $payments[] = $pay;
            }
        }
        if(count($payments)){
            update_post_meta($product_id, 'payments', $payments);
        }
    }    
}
    


add_action("wp_ajax_wppp_quick_edit", "wppp_quick_edit");
add_action("wp_ajax_nopriv_wppp_quick_edit", "wppp_quick_edit");

function wppp_quick_edit() {

    $post_id = $_POST['post_id'];
    echo json_encode(get_post_meta($post_id, 'payments', true));
    die();
}

/**
 * Quick edit
 */
 add_action('admin_head-edit.php', 'wppp_quickedit_get');

  function wppp_quickedit_get() { 
    $html = '<script type="text/javascript">';
    $html .= 'jQuery(document).ready(function() {';
        $html .= 'jQuery("a.editinline").on("click", function() {';

        $html .= 'var id = inlineEditPost.getId(this);';
        $html .= 'jQuery.post(ajaxurl,{action: "wppp_quick_edit",  post_id: id, mode: "inline" },';
        $html .= 'function(data){   jQuery.each(data, function(item, value){ jQuery("#edit-"+id+" .quick_payment input[value="+value+"]").prop("checked", true)}) }';

    $html .= ', "json");});});';
    $html .= '</script>';
    echo $html;
}