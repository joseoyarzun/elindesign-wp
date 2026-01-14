<?php

/**
* Plugin Name: Dreamfox Media Payment gateway per Product for Woocommerce Premium
* Plugin URI: https://www.dreamfoxmedia.com/project/woocommerce-payment-gateway-per-product-premium/ 
* Version: 3.2.6
* Update URI: https://api.freemius.com
* Author: Dreamfox
* Author URI: www.dreamfoxmedia.com 
* Description: Extend Woocommerce plugin to add payments methods to a product
* Requires at least: 5.0
* Tested up to: 6.2.2
* WC requires at least: 5.0.0
* WC tested up to: 7.7.1
* Text Domain: dreamfoxmedia
* Domain Path: /languages
* @Developer : Hoang Xuan Hao / Marco van Loghum Slaterus ( Dreamfoxmedia )
*/
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
require_once dirname( __FILE__ ) . '/inc/functions.php';

if ( function_exists( 'dfm_pgppfw_fs' ) ) {
    dfm_pgppfw_fs()->set_basename( true, __FILE__ );
} else {
    // DO NOT REMOVE THIS IF, IT IS ESSENTIAL FOR THE `function_exists` CALL ABOVE TO PROPERLY WORK.
    
    if ( !function_exists( 'dfm_pgppfw_fs' ) ) {
        // Create a helper function for easy SDK access.
        function dfm_pgppfw_fs()
        {
            global  $dfm_pgppfw_fs ;
            
            if ( !isset( $dfm_pgppfw_fs ) ) {
                // Activate multisite network integration.
                if ( !defined( 'WP_FS__PRODUCT_4167_MULTISITE' ) ) {
                    define( 'WP_FS__PRODUCT_4167_MULTISITE', true );
                }
                // Include Freemius SDK.
                require_once dirname( __FILE__ ) . '/freemius/start.php';
                $dfm_pgppfw_fs = fs_dynamic_init( array(
                    'id'              => '4167',
                    'slug'            => 'dfm-payment-gateway-per-product-for-woocommerce',
                    'type'            => 'plugin',
                    'public_key'      => 'pk_5a51c11bf6bf5275ffda4baf7fbaa',
                    'is_premium'      => true,
                    'premium_suffix'  => 'Premium',
                    'has_addons'      => false,
                    'has_paid_plans'  => true,
                    'has_affiliation' => 'all',
                    'menu'            => array(
                    'slug'        => 'dfm-pgppfw',
                    'contact'     => false,
                    'support'     => false,
                    'affiliation' => false,
                    'parent'      => array(
                    'slug' => 'woocommerce',
                ),
                ),
                    'is_live'         => true,
                ) );
            }
            
            return $dfm_pgppfw_fs;
        }
        
        // Init Freemius.
        dfm_pgppfw_fs();
        // Signal that SDK was initiated.
        do_action( 'dfm_pgppfw_fs_loaded' );
    }

}

if ( dfm_pgppfw_fs()->is__premium_only( 'premium' ) ) {
    require_once dirname( __FILE__ ) . '/inc/quick_bulk_edit_patch__premium_only.php';
}
/**
* For multi Network
*/
if ( !function_exists( 'is_plugin_active_for_network' ) || !function_exists( 'is_plugin_active' ) ) {
    require_once ABSPATH . '/wp-admin/includes/plugin.php';
}
/**
* Check is free plugin is installed then we will deactivate free first
*/
//if ( is_plugin_active( 'woocommerce-product-payments/woocommerce-payment-gateway-per-product.php' ) ) {
//    deactivate_plugins( 'woocommerce-product-payments/woocommerce-payment-gateway-per-product.php' );
//}
/**
* Check if WooCommerce is active
*/

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) && !function_exists( 'softsdev_product_payments_settings' ) || is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) {
    /* ----------------------------------------------------- */
    if ( isset( $_GET['product-payment-ignore-notice'] ) && $_GET['product-payment-ignore-notice'] == 1 ) {
        update_option( 'product-payment-ignore-notice', '1' );
    }
    function product_payment_ignore_notice()
    {
        if ( isset( $_GET['product-payment-ignore-notice'] ) ) {
            update_option( 'product_payments_alert', 1 );
        }
    }
    
    add_action( 'admin_init', 'product_payment_ignore_notice' );
    // Submenu on woocommerce section
    add_action( 'admin_menu', 'softsdev_product_payments_submenu_page' );
    
    if ( dfm_pgppfw_fs()->is__premium_only( 'premium' ) ) {
        remove_action( 'admin_menu', 'softsdev_product_payments_submenu_page' );
        add_action( 'admin_menu', 'softsdev_product_payments_submenu_page__premium_only' );
    }
    
    /* ----------------------------------------------------- */
    add_action( 'admin_enqueue_scripts', 'softsdev_product_payments_enqueue' );
    /* ----------------------------------------------------- */
    function softsdev_product_payments_submenu_page()
    {
        add_submenu_page(
            'woocommerce',
            __( 'Product Payment', 'softsdev' ),
            __( 'Product Payment', 'softsdev' ),
            'manage_options',
            'dfm-pgppfw',
            'softsdev_product_payments_settings'
        );
    }
    
    function softsdev_product_payments_submenu_page__premium_only()
    {
        add_submenu_page(
            'woocommerce',
            __( 'Product Payment', 'softsdev' ),
            __( 'Product Payment', 'softsdev' ),
            'manage_options',
            'dfm-pgppfw',
            'softsdev_product_payments_settings__premium_only'
        );
    }
    
    function softsdev_product_payments_enqueue()
    {
        wp_enqueue_style( 'softsdev_product_payments_enqueue', plugin_dir_url( __FILE__ ) . '/css/style.css' );
    }
    
    /**
     * 
     * @param string $text
     * @return string
     */
    function softsdev_product_payments_footer_text( $text )
    {
        if ( isset( $_GET['page'] ) && strpos( plugin_basename( wp_unslash( $_GET['page'] ) ), 'dfm-pgppfw' ) === 0 ) {
            $text = '<a href="https://www.dreamfoxmedia.com" target="_blank">www.dreamfoxmedia.com</a>';
        }
        return $text;
    }
    
    /**
     * 
     * @param string $text
     * @return string
     */
    function softsdev_product_payments_update_footer( $text )
    {
        if ( isset( $_GET['page'] ) && strpos( plugin_basename( wp_unslash( $_GET['page'] ) ), 'dfm-pgppfw' ) === 0 ) {
            $text = 'Version 1.4.3';
        }
        return $text;
    }
    
    /**
     * Type: updated,error,update-nag
     */
    if ( !function_exists( 'softsdev_notice' ) ) {
        function softsdev_notice( $message, $type )
        {
            $html = <<<EOD
        <div class="{$type} notice">
        <p>{$message}</p>
        </div>
EOD;
            echo  $html ;
        }
    
    }
    /**
     * 
     */
    /**
     * Setting form of product payment
     */
    add_action( 'add_meta_boxes', 'wpp_meta_box_add' );
    /**
     * 
     */
    function wpp_meta_box_add()
    {
        global  $post ;
        if ( isset( $post->ID ) && is_product_eligible( $post->ID ) ) {
            add_meta_box(
                'payments',
                'Payments',
                'wpp_payments_form',
                'product',
                'side',
                'core'
            );
        }
    }
    
    /**
     * 
     * @global type $post
     * @global WC_Payment_Gateways $woo
     */
    function wpp_payments_form()
    {
        global  $post, $woo ;
        $productIds = get_option( 'woocommerce_product_apply', array() );
        if ( is_array( $productIds ) ) {
            foreach ( $productIds as $key => $product ) {
                if ( !get_post( $product ) || get_post_meta( $product, 'payments', true ) && !count( get_post_meta( $product, 'payments', true ) ) ) {
                    unset( $productIds[$key] );
                }
            }
        }
        update_option( 'woocommerce_product_apply', $productIds );
        $postPayments = ( get_post_meta( $post->ID, 'payments', true ) ? get_post_meta( $post->ID, 'payments', true ) : array() );
        $woo = new WC_Payment_Gateways();
        //$payments = $woo->get_available_payment_gateways();
        $payments = $woo->payment_gateways;
        foreach ( $payments as $pay ) {
            if ( apply_filters( 'softsdev_show_disabled_gateways', false ) || $pay->enabled === 'no' ) {
                continue;
            }
            $checked = '';
            if ( is_array( $postPayments ) && in_array( $pay->id, $postPayments ) ) {
                $checked = ' checked="yes" ';
            }
            ?>  
        <input type="checkbox" <?php 
            echo  $checked ;
            ?> value="<?php 
            echo  $pay->id ;
            ?>" name="pays[]" id="payment_<?php 
            echo  $pay->id ;
            ?>" />
        <label for="payment_<?php 
            echo  $pay->id ;
            ?>"><?php 
            echo  $pay->title ;
            ?></label>  
        <br />  
        <?php 
        }
    }
    
    add_action(
        'save_post',
        'wpp_meta_box_save',
        10,
        2
    );
    /**
     * 
     * @param type $post_id
     * @param type $post
     * @return type
     */
    function wpp_meta_box_save( $post_id, $post )
    {
        // Restrict to save for autosave
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE || isset( $_REQUEST['action'] ) && sanitize_title( $_REQUEST['action'] ) != 'editpost' ) {
            return $post_id;
        }
        // Restrict to save for revisions
        if ( isset( $post->post_type ) && $post->post_type == 'revision' ) {
            return $post_id;
        }
        
        if ( get_post_type() === 'product' && isset( $_POST['pays'] ) ) {
            $productIds = get_option( 'woocommerce_product_apply', array() );
            
            if ( is_array( $productIds ) && !in_array( $post_id, $productIds ) ) {
                $productIds[] = $post_id;
                update_option( 'woocommerce_product_apply', $productIds );
            }
            
            //delete_post_meta($post_id, 'payments');
            $payments = array();
            $post_payments = array_filter( array_map( 'sanitize_title', $_POST['pays'] ) );
            if ( $post_payments ) {
                foreach ( $post_payments as $pay ) {
                    $payments[] = $pay;
                }
            }
            update_post_meta( $post_id, 'payments', $payments );
        } elseif ( get_post_type() === 'product' ) {
            update_post_meta( $post_id, 'payments', array() );
        }
    
    }
    
    /**
     *
     * 
     * 
     * @global type $woocommerce
     * @param type $available_gateways
     * @return type
     */
    function wpppayment_gateway_disable_country( $available_gateways )
    {
        global  $woocommerce ;
        $arrayKeys = array_keys( $available_gateways );
        /**
         * default setting
         */
        $softsdev_wpp_plugin_settings = get_option( 'sdwpp_plugin_settings', array(
            'softsdev_selected_cats' => '',
            'default_payment'        => '',
        ) );
        $default_payment = unserialize( $softsdev_wpp_plugin_settings['default_payment'] );
        $is_default_pay_needed = false;
        foreach ( $available_gateways as $gateway_id => $gateway ) {
            // check by categories
            
            if ( dfm_per_categories_enabled() ) {
                $included_cats = dfm_per_categories_include_get_option( $gateway_id );
                
                if ( dfm_per_categories_do_disable( $included_cats, true ) ) {
                    unset( $available_gateways[$gateway_id] );
                    continue;
                }
                
                $excluded_cats = dfm_per_categories_exclude_get_option( $gateway_id );
                
                if ( dfm_per_categories_do_disable( $excluded_cats ) ) {
                    unset( $available_gateways[$gateway_id] );
                    continue;
                }
            
            }
            
            // check by tags
            
            if ( dfm_per_tags_enabled() ) {
                $included_tags = dfm_per_tags_include_get_option( $gateway_id );
                
                if ( dfm_per_tags_do_disable( $included_tags, true ) ) {
                    unset( $available_gateways[$gateway_id] );
                    continue;
                }
                
                $excluded_tags = dfm_per_tags_exclude_get_option( $gateway_id );
                
                if ( dfm_per_tags_do_disable( $excluded_tags ) ) {
                    unset( $available_gateways[$gateway_id] );
                    continue;
                }
            
            }
        
        }
        /**
         * checking all cart products
         */
        
        if ( is_object( $woocommerce->cart ) ) {
            $items = $woocommerce->cart->cart_contents;
            $itemsPays = '';
            if ( is_array( $items ) ) {
                foreach ( $items as $item ) {
                    // check by products
                    if ( !is_product_eligible( $item['product_id'] ) ) {
                        continue;
                    }
                    $itemsPays = get_post_meta( $item['product_id'], 'payments', true );
                    if ( is_array( $itemsPays ) && count( $itemsPays ) ) {
                        foreach ( $arrayKeys as $key ) {
                            if ( array_key_exists( $key, $available_gateways ) && !in_array( $available_gateways[$key]->id, $itemsPays ) ) {
                                
                                if ( $default_payment == $key ) {
                                    $is_default_pay_needed = true;
                                    $default_payment_obj = $available_gateways[$key];
                                    unset( $available_gateways[$key] );
                                } else {
                                    unset( $available_gateways[$key] );
                                }
                            
                            }
                        }
                    }
                }
            }
            /**
             * set default payment if there is none
             */
            if ( $is_default_pay_needed && count( $available_gateways ) == 0 ) {
                $available_gateways[$default_payment] = $default_payment_obj;
            }
        }
        
        return $available_gateways;
    }
    
    add_filter( 'woocommerce_available_payment_gateways', 'wpppayment_gateway_disable_country' );
    function softsdev_product_payments_settings()
    {
        wp_enqueue_style( 'softsdev_select2_css', plugins_url( '/vendor/select2/css/select2.min.css', __FILE__ ) );
        wp_enqueue_script( 'softsdev_select2_js', plugins_url( '/vendor/select2/js/select2.min.js', __FILE__ ) );
        $categories = get_terms( array(
            'taxonomy'   => 'product_cat',
            'hide_empty' => false,
        ) );
        $softsdev_wpp_plugin_settings = get_option( 'sdwpp_plugin_settings', array(
            'softsdev_selected_cats' => '',
            'default_payment'        => '',
        ) );
        if ( is_array( $softsdev_wpp_plugin_settings ) ) {
            $softsdev_selected_cats = unserialize( $softsdev_wpp_plugin_settings['softsdev_selected_cats'] );
        }
        if ( !$softsdev_selected_cats ) {
            $softsdev_selected_cats = array();
        }
        ob_start();
        ?>

<select class="js-softsdev_selected_cats" name="sdwpp_setting[softsdev_selected_cats][]" multiple="multiple" style="width: 100%;">
    <?php 
        foreach ( $categories as $category ) {
            ?>
        <option value="<?php 
            echo  $category->term_id ;
            ?>"<?php 
            echo  ( in_array( $category->term_id, $softsdev_selected_cats ) ? ' selected="selected"' : '' ) ;
            ?>><?php 
            echo  $category->name ;
            ?></option>
    <?php 
        }
        ?>
</select>
<p>You can select any 2 categories for this functionality due to free plugin.</p>

<script>
    (function($) {
        var softsdev = {
            select2: function() {
                $('.js-softsdev_selected_cats').select2({
                    maximumSelectionLength: 2
                });
            },
        }

        $(document).ready(function() {
            for (var func in softsdev) {
                if (softsdev[func] instanceof Function) {
                    softsdev[func]();
                }
            }
        });
    })(jQuery);
</script>
<?php 
        $additional_html = ob_get_clean();
        softsdev_product_payments_settings_part( $additional_html );
    }
    
    function softsdev_product_payments_settings__premium_only()
    {
        softsdev_product_payments_settings_part();
    }
    
    add_action( 'init', 'softsdev_product_payments_save_settings' );
    function softsdev_product_payments_save_settings()
    {
        
        if ( isset( $_POST['sdwpp_setting'] ) ) {
            update_option( 'sdwpp_plugin_settings', array_filter( array_map( 'serialize', $_POST['sdwpp_setting'] ) ) );
            softsdev_notice( 'Woocommerce Payment Gateway per Product setting is updated.', 'updated' );
        }
    
    }
    
    function softsdev_product_payments_settings_part( $additional_html = '' )
    {
        wp_enqueue_style( 'softsdev_select2_css', plugins_url( '/vendor/select2/css/select2.min.css', __FILE__ ) );
        wp_enqueue_script( 'softsdev_select2_js', plugins_url( '/vendor/select2/js/select2.min.js', __FILE__ ) );
        wp_register_script( 'dd_horztab_script', plugins_url( '/js/dd_horizontal_tabs.js', __FILE__ ) );
        wp_enqueue_script( 'dd_horztab_script' );
        add_filter( 'admin_footer_text', 'softsdev_product_payments_footer_text' );
        add_filter( 'update_footer', 'softsdev_product_payments_update_footer' );
        echo  '<div class="wrap wrap-mc-paid"><div id="icon-tools" class="icon32"></div>' ;
        echo  '<h2 class="title">' . __( 'Woocommerce Product Payments', 'softsdev' ) . '</h2>' ;
        ?>

<div class="left-dd-paid ">
    <div class="left_box_container">
        <ul class="horz_tabs">
            <li <?php 
        if ( !isset( $_GET['tab'] ) ) {
            ?> class="active"  <?php 
        }
        ?> id="payment_information">
                <a href="javascript:;">Information</a>
            </li>
            <li id="payment_settings" <?php 
        if ( isset( $_GET['tab'] ) && $_GET['tab'] == 'payment_settings' ) {
            ?>class="active"  <?php 
        }
        ?>>
                <a href="javascript:;">Settings</a>
            </li>
            <li id="payment_per_categories" <?php 
        if ( isset( $_GET['tab'] ) && $_GET['tab'] == 'payment_per_categories' ) {
            ?>class="active"  <?php 
        }
        ?>>
                <a href="javascript:;">Per Categories</a>
            </li>
            <li id="payment_per_tags" <?php 
        if ( isset( $_GET['tab'] ) && $_GET['tab'] == 'payment_per_tags' ) {
            ?>class="active"  <?php 
        }
        ?>>
                <a href="javascript:;">Per Tags</a>
            </li>
            <li id="payment_newsletter" <?php 
        if ( isset( $_GET['tab'] ) && $_GET['tab'] == 'payment_newsletter' ) {
            ?>class="active"  <?php 
        }
        ?>>
                <a href="javascript:;">Newsletter</a>
            </li>
            <li id="payment_faq" >
                <a href="javascript:;">FAQ</a>
            </li>
            <li id="payment_support" <?php 
        if ( isset( $_GET['tab'] ) && $_GET['tab'] == 'payment_support' ) {
            ?>class="active"  <?php 
        }
        ?>>
                <a href="javascript:;">Support</a>
            </li>
            <li id="payment_dfmplugins">
                <a href="javascript:;">DFM Plugins</a>
            </li>
        </ul>
    </div>
</div>

<div class="right-dd-paid ">
  <div id="tab_payment_information" class="postbox <?php 
        if ( !isset( $_GET['tab'] ) ) {
            ?>active<?php 
        }
        ?>" style="padding: 10px; margin: 10px 0px;">
      <?php 
        add_filter( 'admin_footer_text', 'softsdev_product_payments_footer_text' );
        add_filter( 'update_footer', 'softsdev_product_payments_update_footer' );
        echo  '<div class="wrap wrap-mc-paid"><div id="icon-tools" class="icon32"></div></div>' ;
        echo  '<h2 class="title">' . __( 'Woocommerce Product Payments - Information', 'softsdev' ) . '</h2>' ;
        ?>
			  			<img src="<?php 
        echo  plugins_url( 'img/attention.png', __FILE__ ) ;
        ?>"><br>
			IMPORTANT: We are using a new license system. If you have trouble with your license then see this link:<br>
			<a href="https://support.dreamfoxmedia.com/support/solutions/articles/72000533747-transferring-our-licenses-from-dreamfoxmedia-to-freemius" target="_blank">Click here to see the complete article</a>
		
      <p>This plugin for WooCommerce Payment Gateway per Product, by tag or per category and lets you select the available payment method for each (individual) product.<br>
      This plugin will allow the admin to select the available payment gateway for each individual product. This is done by <a href="edit.php?post_type=product" >products</a><br>
	  <p><img src="<?php 
        echo  plugins_url( 'img/pgpp1.png', __FILE__ ) ;
        ?>">&nbsp;&nbsp;&nbsp;<img src="<?php 
        echo  plugins_url( 'img/pgpp2.png', __FILE__ ) ;
        ?>"></p> 
      For TAG and CATEGORIES you can set these by clicking the menu items on the left.<br>
	  Admin can select for each (individual) product the payment gateway that will be used by checkout. If no selection is made, then the default payment gateways are displayed.<br>
      If you for example only select paypal then only paypal will available for that product by checking out.</p>
	
  </div>

  <div id="tab_payment_settings" class="postbox <?php 
        if ( isset( $_GET['tab'] ) && $_GET['tab'] == 'payment_settings' ) {
            ?>active<?php 
        }
        ?>" style="padding: 10px; margin: 10px 0px;">
    <?php 
        add_filter( 'admin_footer_text', 'softsdev_product_payments_footer_text' );
        add_filter( 'update_footer', 'softsdev_product_payments_update_footer' );
        echo  '<div class="wrap wrap-mc-paid"><div id="icon-tools" class="icon32"></div></div>' ;
        echo  '<h2 class="title">' . __( 'Woocommerce Product Payments - Settings', 'softsdev' ) . '</h2>' ;
        ?>


    <?php 
        /**
         * Settings default
         */
        $softsdev_wpp_plugin_settings = get_option( 'sdwpp_plugin_settings', array(
            'softsdev_selected_cats' => '',
            'default_payment'        => '',
        ) );
        $default_payment = unserialize( $softsdev_wpp_plugin_settings['default_payment'] );
        ?>
    <form id="woo_sdwpp" action="<?php 
        echo  $_SERVER['PHP_SELF'] . '?page=dfm-pgppfw&tab=payment_settings' ;
        ?>" method="post">
        <div style="padding: 10px 0; margin: 10px 0px;">
          <?php 
        echo  $additional_html ;
        ?>
        


        <h3 class="hndle"><?php 
        echo  __( 'Default Payment option( If not match any.)', 'softsdev' ) ;
        ?></h3>
        <?php 
        $woo = new WC_Payment_Gateways();
        $payments = $woo->payment_gateways;
        ?>
        <select id="sdwpp_default_payment" name="sdwpp_setting[default_payment]">
            <option value="none" <?php 
        selected( $default_payment, 'none' );
        ?>>None</option>
            <?php 
        foreach ( $payments as $pay ) {
            /**
             *  skip if payment in disbled from admin
             */
            if ( $pay->enabled === 'no' ) {
                continue;
            }
            echo  "<option value = '" . $pay->id . "' " . selected( $default_payment, $pay->id ) . ">" . $pay->title . "</option>" ;
        }
        ?>
        </select>
        <br />
        <small><?php 
        echo  __( 'If in some case payment option not show then this will default one set', 'softsdev' ) ;
        ?></small>
        </div>
        <input class="button-large button-primary" type="submit" value="Save changes" />
    </form>
  </div>
<?php 
        
        if ( dfm_pgppfw_fs()->is__premium_only() ) {
            ?>	
  <div id="tab_payment_per_categories" class="postbox <?php 
            echo  ( isset( $_GET['tab'] ) && $_GET['tab'] == 'payment_per_categories' ? 'active' : '' ) ;
            ?>" style="padding: 10px; margin: 10px 0px;">
        <div class="wrap wrap-mc-paid"><div id="icon-tools" class="icon32"></div></div>
        <h2 class="title"><?php 
            echo  __( 'Woocommerce Product Payments - Per Categories', 'softsdev' ) ;
            ?></h2>

        <?php 
            include dirname( __FILE__ ) . '/inc/per_categories.php';
            ?>
  </div>

  <div id="tab_payment_per_tags" class="postbox <?php 
            echo  ( isset( $_GET['tab'] ) && $_GET['tab'] == 'payment_per_tags' ? 'active' : '' ) ;
            ?>" style="padding: 10px; margin: 10px 0px;">
        <div class="wrap wrap-mc-paid"><div id="icon-tools" class="icon32"></div></div>
        <h2 class="title"><?php 
            echo  __( 'Woocommerce Product Payments - Per Tags', 'softsdev' ) ;
            ?></h2>

        <?php 
            include dirname( __FILE__ ) . '/inc/per_tags.php';
            ?>
  </div>
<?php 
        }
        
        ?>	
  <div id="tab_payment_newsletter" class="postbox" style="padding: 10px; margin: 10px 0px;">
    <?php 
        add_filter( 'admin_footer_text', 'softsdev_product_payments_footer_text' );
        add_filter( 'update_footer', 'softsdev_product_payments_update_footer' );
        echo  '<div class="wrap wrap-mc-paid"><div id="icon-tools" class="icon32"></div></div>' ;
        echo  '<h2 class="title">' . __( 'Woocommerce Product Payments - Newsletter', 'softsdev' ) . '</h2>' ;
        ?>
    <!-- Begin Mailjet Form -->
	<iframe width="540" height="505" src="https://322fdba5.sibforms.com/serve/MUIEADPSqc91xZQAhD93GZEuPI0STBa6IDtiRPRy1s2sWDXpIahq0YCn_hTynzANungZ-IBXlkdiqtxS5LWTX2PnNO4HXf3zdrDPhYfqPMOU5dTl_slePr-U4hKHdS0HY622pFWMdMMfj40dLxrwCm1gCkrwuC5SLHSNKOfjzFKVX5WkfG6W2aOhHybGkbdXqxCZmXoHswZbB_uJ" frameborder="0" scrolling="auto" allowfullscreen style="display: block;margin-left: auto;margin-right: auto;max-width: 100%;"></iframe>
	<!--  END - mailjet form -->
  </div>

  <div id="tab_payment_faq" class="postbox" style="padding: 10px; margin: 10px 0px;">
    <?php 
        add_filter( 'admin_footer_text', 'softsdev_product_payments_footer_text' );
        add_filter( 'update_footer', 'softsdev_product_payments_update_footer' );
        echo  '<div class="wrap wrap-mc-paid"><div id="icon-tools" class="icon32"></div></div>' ;
        echo  '<h2 class="title">' . __( 'Woocommerce Product Payments - FAQ', 'softsdev' ) . '</h2>' ;
        ?>
    <h4 class="mc4wp-title"><?php 
        echo  __( 'Looking for help?', 'Woocommerce Payment Gateway Per Product' ) ;
        ?></h4>
    <p>Below you see the link to the complete FAQ available at: <a href="https://support.dreamfoxmedia.com?utm_source=wp-plugin&utm_medium=wcpgpp-p&utm_campaign=faqall" target="_blank">dreamfoxmedia.com</a></p>
    <ul class="ul-square">
    <li><a href="https://support.dreamfoxmedia.com/support/solutions?utm_source=wp-plugin&utm_medium=wcpgpp-p&utm_campaign=faqall" target="_blank">Click here to see the complete FAQ section</a></li>
    </ul>

    <p>Or see this link to the most read FAQs for the payment plugin available at: <a href="https://support.dreamfoxmedia.com/support/solutions/72000275598?utm_source=wp-plugin&utm_medium=wcpgpp-p&utm_campaign=faqall" target="_blank">Dreamfoxmedia.com</a></p>


    <p><?php 
        echo  sprintf( __( 'If your answer can not be found in the resources listed above, please use our supportsystem <a href="%s">here</a>.' ), 'https://support.dreamfoxmedia.com' ) ;
        ?></p>
    <p>Found a bug? Please open an issue <a href="https://support.dreamfoxmedia.com/support/tickets/new#utm_source=wp-plugin&utm_medium=wcpgpp-p&utm_campaign=issue" target="_blank">here.</a></p>
  </div>

  <div id="tab_payment_support" class="postbox <?php 
        if ( isset( $_GET['tab'] ) && $_GET['tab'] == 'payment_support' ) {
            ?>active<?php 
        }
        ?>" style="padding: 10px; margin: 10px 0px;">
    <?php 
        add_filter( 'admin_footer_text', 'softsdev_product_payments_footer_text' );
        add_filter( 'update_footer', 'softsdev_product_payments_update_footer' );
        echo  '<div class="wrap wrap-mc-paid"><div id="icon-tools" class="icon32"></div></div>' ;
        echo  '<h2 class="title">' . __( 'Woocommerce Product Payments - Support Form', 'softsdev' ) . '</h2>' ;
        $user = wp_get_current_user();
        $plugin_data = get_plugin_data( __FILE__ );
        ?>
    <div class="supportform" style="display:block; background-color:#FFF; margin:20px; padding:10px;">
    <form action="admin.php?page=dfm-pgppfw&tab=payment_support" id="support_form" method="post" class="mymail-form-2 mymail-form mymail-form-submit extern">

    <table width="100%" class="form-table">
    <tr>
    <th>
    <label><?php 
        echo  __( 'Name', 'dreamfox_dd' ) ;
        ?></label>
    <img width="16" height="16" src="<?php 
        echo  plugins_url( 'img/help.png', __FILE__ ) ;
        ?>" class="help_tip" title="<?php 
        echo  __( 'Name', 'dreamfox_dd' ) ;
        ?>">
    </th>
    <td>
    <input id="name" name="name" type="textbox" size="30" value="<?php 
        echo  $user->data->user_nicename ;
        ?>" />
    </td>
    </tr>


    <tr>
    <th>
    <label><?php 
        echo  __( 'Email', 'dreamfox_dd' ) ;
        ?></label>
    <img width="16" height="16" src="<?php 
        echo  plugins_url( 'img/help.png', __FILE__ ) ;
        ?>" class="help_tip" title="<?php 
        echo  __( 'Email', 'dreamfox_dd' ) ;
        ?>">
    </th>
    <td>
    <input id="email" name="email" type="textbox" size="30" value="<?php 
        echo  $user->data->user_email ;
        ?>" />
    </td>
    </tr>
    <tr>
    <th>
    <label><?php 
        echo  __( 'Plugin Name', 'dreamfox_dd' ) ;
        ?></label>
    <img width="16" height="16" src="<?php 
        echo  plugins_url( 'img/help.png', __FILE__ ) ;
        ?>" class="help_tip" title="<?php 
        echo  __( 'Plugin Name', 'dreamfox_dd' ) ;
        ?>">
    </th>
    <td>
    <input readonly id="plugin_name" name="plugin_name" type="textbox"  size="60" value="Woocommerce Payment Gateway Per Product Premium" />

    </td>
    </tr>
    <tr>
    <th>
    <label><?php 
        echo  __( 'Version Number', 'dreamfox_dd' ) ;
        ?></label>
    <img width="16" height="16" src="<?php 
        echo  plugins_url( 'img/help.png', __FILE__ ) ;
        ?>" class="help_tip" title="<?php 
        echo  __( 'Plugin Name', 'dreamfox_dd' ) ;
        ?>">
    </th>
    <td>
    <input readonly id="version_number" name="version_number" type="textbox"  size="30" value="<?php 
        echo  $plugin_data['Version'] ;
        ?>
    " />

    </td>
    </tr>
    <tr>
    <th>
    <label><?php 
        echo  __( 'License', 'dreamfox_dd' ) ;
        ?></label>
    <img width="16" height="16" src="<?php 
        echo  plugins_url( 'img/help.png', __FILE__ ) ;
        ?>" class="help_tip" title="<?php 
        echo  __( 'License', 'dreamfox_dd' ) ;
        ?>">
    </th>
    <td>
    <input readonly id="license" name="license" type="textbox"  size="30" value="<?php 
        echo  get_option( 'product_payments_license_key' ) ;
        ?>" />

    </td>
    </tr>

    <tr>
    <th>
    <label><?php 
        echo  __( 'Details of Problem', 'dreamfox_dd' ) ;
        ?></label>
    <img width="16" height="16" src="<?php 
        echo  plugins_url( 'img/help.png', __FILE__ ) ;
        ?>" class="help_tip" title="<?php 
        echo  __( 'Details of Problem', 'dreamfox_dd' ) ;
        ?>">
    </th>
    <td>

    <textarea name="detail_problem" id="detail_problem" style="width:300px;height:300px"></textarea>
    <input type="hidden" name="action" value="raise_product_payment_support_email" />
    </td>
    </tr>

    <tr>
    <th>&nbsp;</th>
    <td align="left">
    <button onclick="support_form();" type="button" class="button-large button-primary">Submit Support Ticket</button>
    </td>
    </tr>
    </table>

    </form>
    </div>
  </div>

  <div id="tab_payment_dfmplugins" class="postbox" style="padding: 10px; margin: 10px 0px;">
    <?php 
        add_filter( 'admin_footer_text', 'softsdev_product_payments_footer_text' );
        add_filter( 'update_footer', 'softsdev_product_payments_update_footer' );
        echo  '<div class="wrap wrap-mc-paid"><div id="icon-tools" class="icon32"></div></div>' ;
        echo  '<h2 class="title">' . __( 'Woocommerce Product Payments - Dreamfox Media Plugins', 'softsdev' ) . '</h2>' ;
        ?>
    <?php 
        $url = 'https://raw.githubusercontent.com/dreamfoxmedia/dreamfoxmedia/gh-pages/plugins/dfmplugins.json';
        $response = wp_remote_get( $url, array() );
        $response_code = wp_remote_retrieve_response_code( $response );
        $response_body = wp_remote_retrieve_body( $response );
        
        if ( $response_code != 200 || is_wp_error( $response ) ) {
            echo  '<div class="error below-h2"><p>There was an error retrieving the list from the server.</p></div>' ;
            switch ( $response_code ) {
                case '403':
                    echo  '<div class="error below-h2"><p>Seems your host is blocking <strong>' . dirname( $url ) . '</strong>. Please request to white list this domain </p></div>' ;
                    break;
            }
            wp_die();
        }
        
        $addons = json_decode( $response_body );
        // set_transient( 'mymail_addons', $addons, 3600 );
        $plugin_http_path = plugins_url();
        ?>
    <div class="wrap">
        <h3>Here you see our great Free and Premium Plugins of Dreamfox Media</h3>
        <link href="<?php 
        echo  $plugin_http_path ;
        ?>/woocommerce-delivery-date-premium/css/addons-style.min.css?ver=2.1.23" rel="stylesheet" type="text/css">

        <ul class="addons-wrap">
            <?php 
        foreach ( $addons as $addon ) {
            if ( !empty($addon->hidden) ) {
                continue;
            }
            $addon->link = ( isset( $addon->link ) ? add_query_arg( array(
                'utm_source'   => 'Dreamfox Media Plugin Page',
                'utm_medium'   => 'link',
                'utm_campaign' => 'Dreamfox Plugins Add Ons',
            ), $addon->link ) : '' );
            ?>
                <li class="mymail-addon <?php 
            if ( !empty($addon->is_free) ) {
                echo  ' is-free' ;
            }
            if ( !empty($addon->is_feature) ) {
                echo  ' is-feature' ;
            }
            
            if ( isset( $addon->image ) ) {
                $image = str_replace( 'http//', '//', $addon->image );
            } elseif ( isset( $addon->image_ ) ) {
                $image = str_replace( 'http//', '//', $addon->image_ );
            }
            
            ?>">
                <div class="bgimage" style="min-height: 500px; background-repeat: no-repeat; background-image:url(<?php 
            echo  $image ;
            ?>)">
                <?php 
            
            if ( isset( $addon->wpslug ) ) {
                ?>
                <a href="plugin-install.php?tab=plugin-information&plugin=<?php 
                echo  dirname( $addon->wpslug ) ;
                ?>&from=import&TB_iframe=true&width=745&height=745" class="thickbox">&nbsp;</a>
                <?php 
            } else {
                ?>
                <a href="<?php 
                echo  $addon->link ;
                ?>">&nbsp;</a>
                <?php 
            }
            
            ?>
                </div>
                <h4><?php 
            echo  $addon->name ;
            ?></h4>
                <p class="author">by
                <?php 
            
            if ( $addon->author_url ) {
                echo  '<a href="' . $addon->author_url . '">' . $addon->author . '</a>' ;
            } else {
                echo  $addon->author ;
            }
            
            ?>
                </p>
                <p class="description"><?php 
            echo  $addon->description ;
            ?></p>
                <div class="action-links">
                <?php 
            
            if ( !empty($addon->wpslug) ) {
                ?>
                <?php 
                
                if ( is_dir( dirname( WP_PLUGIN_DIR . '/' . $addon->wpslug ) ) ) {
                    ?>
                <?php 
                    
                    if ( is_plugin_active( $addon->wpslug ) ) {
                        ?>
                <a class="button" href="<?php 
                        echo  wp_nonce_url( 'plugins.php?action=deactivate&amp;plugin=' . $addon->wpslug, 'deactivate-plugin_' . $addon->wpslug ) ;
                        ?>"><?php 
                        _e( 'Deactivate', 'mymail' );
                        ?></a>
                <?php 
                    } elseif ( is_plugin_inactive( $addon->wpslug ) ) {
                        ?>
                <a class="button" href="<?php 
                        echo  wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $addon->wpslug, 'activate-plugin_' . $addon->wpslug ) ;
                        ?>"><?php 
                        _e( 'Activate', 'mymail' );
                        ?></a>
                <?php 
                    }
                    
                    ?>
                <?php 
                } else {
                    ?>
                <?php 
                    
                    if ( current_user_can( 'install_plugins' ) || current_user_can( 'update_plugins' ) ) {
                        ?>
                <a class="button button-primary" href="<?php 
                        echo  wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=' . dirname( $addon->wpslug ) . '&mymail-addon' ), 'install-plugin_' . dirname( $addon->wpslug ) ) ;
                        ?>"><?php 
                        _e( 'Install', 'mymail' );
                        ?></a>
                <?php 
                    }
                    
                    ?>
                <?php 
                }
                
                ?>
                <?php 
            } else {
                ?>
                <a class="button button-primary" href="<?php 
                echo  $addon->link ;
                ?>"><?php 
                _e( 'Purchase', 'mymail' );
                ?></a>
                <?php 
            }
            
            ?>
                </div>
                </li>
            <?php 
        }
        ?>
        </ul>
    </div>
  </div>
</div>



<?php 
    }
    
    function product_payment_support_email()
    {
        global  $dreamfox_dd_version ;
        
        if ( isset( $_POST['action'] ) && $_POST['action'] == 'raise_product_payment_support_email' ) {
            $name = $_POST['name'];
            $email = $_POST['email'];
            $plugin_name = $_POST['plugin_name'];
            $license = $_POST['license'];
            $detail_problem = $_POST['detail_problem'];
            $to_email = 'support@dreamfoxmedia.com';
            $sep = "\n\n";
            $subject = 'Support Ticket for the Plugin : Woocommerce Payment Gateway Per Product Premium';
            $message = "Hi there," . $sep . "Here are the details of the support ticket : " . $sep . " Name : " . $name . $sep . " Email : " . $email . $sep . " Plugin : " . $plugin_name . $sep . " Version : " . $dreamfox_dd_version . $sep . " License Code : " . $license . $sep . " Details of Problem : " . $sep . $detail_problem . $sep . " Thanks," . $sep . "Admin";
            wp_mail( $to_email, $subject, $message );
            softsdev_notice( 'Support email has been sent successfully', 'updated' );
            reset( $_POST );
        }
    
    }
    
    add_action( 'init', 'product_payment_support_email' );
    /**
     * 
     * @param type $product_id
     * @return boolean
     */
    function is_product_eligible( $product_id )
    {
        if ( dfm_pgppfw_fs()->is__premium_only( 'premium' ) ) {
            return true;
        }
        // Product object
        $product_object = wc_get_product( $product_id );
        if ( !$product_object || $product_object->post_type != 'product' ) {
            return false;
        }
        $softsdev_wpp_plugin_settings = get_option( 'sdwpp_plugin_settings', array(
            'softsdev_selected_cats' => '',
            'default_payment'        => '',
        ) );
        $softsdev_selected_cats = unserialize( $softsdev_wpp_plugin_settings['softsdev_selected_cats'] );
        
        if ( $softsdev_selected_cats ) {
            $is_eligible = false;
            // Get visiblity
            $current_visibility = $product_object->get_catalog_visibility();
            // Get Category Ids
            $cat_ids = wp_get_post_terms( $product_id, 'product_cat', array(
                'fields' => 'ids',
            ) );
            // Convert saved array in to list
            $softsdev_selected_cats = ( is_array( $softsdev_selected_cats ) ? $softsdev_selected_cats : array( $softsdev_selected_cats ) );
            foreach ( $cat_ids as $cat_id ) {
                
                if ( in_array( $cat_id, $softsdev_selected_cats ) ) {
                    $is_eligible = true;
                    break;
                }
            
            }
            // check visiblity in array or now define
            
            if ( $is_eligible && in_array( $current_visibility, array( 'catalog', 'visible' ) ) ) {
                $is_eligible = true;
            } else {
                $is_eligible = false;
            }
            
            // return eligiblity
            return $is_eligible;
        }
        
        return false;
    }
    
    add_action( 'init', 'softsdev_product_payments_save_per_categories' );
    function softsdev_product_payments_save_per_categories()
    {
        
        if ( isset( $_POST['dfm_per_categories'] ) ) {
            $enabled = ( isset( $_POST['dfm_per_categories_enable'] ) ? 1 : 0 );
            update_option( 'dfm_per_categories_enable', $enabled );
            $available_gateways = WC()->payment_gateways->payment_gateways();
            foreach ( $available_gateways as $gateway_id => $gateway ) {
                $field_include = dfm_per_categories_include_field_name( $gateway_id );
                $field_exclude = dfm_per_categories_exclude_field_name( $gateway_id );
                update_option( $field_include, ( isset( $_POST[$field_include] ) ? $_POST[$field_include] : [] ) );
                update_option( $field_exclude, ( isset( $_POST[$field_exclude] ) ? $_POST[$field_exclude] : [] ) );
            }
            softsdev_notice( 'Woocommerce Payment Gateway per categories is updated.', 'updated' );
        }
    
    }
    
    add_action( 'init', 'softsdev_product_payments_save_per_tags' );
    function softsdev_product_payments_save_per_tags()
    {
        
        if ( isset( $_POST['dfm_per_tags'] ) ) {
            $enabled = ( isset( $_POST['dfm_per_tags_enable'] ) ? 1 : 0 );
            update_option( 'dfm_per_tags_enable', $enabled );
            $available_gateways = WC()->payment_gateways->payment_gateways();
            foreach ( $available_gateways as $gateway_id => $gateway ) {
                $field_include = dfm_per_tags_include_field_name( $gateway_id );
                $field_exclude = dfm_per_tags_exclude_field_name( $gateway_id );
                update_option( $field_include, ( isset( $_POST[$field_include] ) ? $_POST[$field_include] : [] ) );
                update_option( $field_exclude, ( isset( $_POST[$field_exclude] ) ? $_POST[$field_exclude] : [] ) );
            }
            softsdev_notice( 'Woocommerce Payment Gateway per tags is updated.', 'updated' );
        }
    
    }

}
