<?php

function pcbdw_is_supported_taxonomy( $taxonomy ) {
    return in_array( $taxonomy, array( 'product_cat', 'product_tag', 'product_brand' ), true ) || strpos( $taxonomy, 'pa_' ) === 0;
}