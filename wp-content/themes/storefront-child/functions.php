<?php

/** Remove the product after the successdully product recieved */
add_action('woocommerce_order_status_completed', 'delete_flight_products_after_checkout');
function delete_flight_products_after_checkout($order_id)
{
    $order = wc_get_order($order_id);
    foreach ($order->get_items() as $item) {
        $product_id = $item->get_product_id();
        wp_delete_post($product_id, true);
    }
}
