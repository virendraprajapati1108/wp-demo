<?php
/* Template Name: Book Flight */
get_header();

// Check if WooCommerce is active
if (! class_exists('WooCommerce')) {
    echo "<p style='color:red;'>WooCommerce is not installed or activated.</p>";
    get_footer();
    exit;
}

// Check if flight_id exists
if (! isset($_GET['flight_id']) || empty($_GET['flight_id'])) {
    echo "<p style='color:red;'>Invalid flight selected.</p>";
    get_footer();
    exit;
}

$flight_id = intval($_GET['flight_id']);

// Fetch flight details from Project A API
$response = wp_remote_get("http://localhost/wp_demo2/wp-json/wp/v2/flights/{$flight_id}");

if (is_wp_error($response)) {
    echo "<p style='color:red;'>Unable to fetch flight details. Please try again later.</p>";
    get_footer();
    exit;
}

$flight = json_decode(wp_remote_retrieve_body($response));

if (! $flight || ! isset($flight->acf->price)) {
    echo "<p style='color:red;'>Flight details not available. Please try again.</p>";
    get_footer();
    exit;
}

// Create a unique product name for WooCommerce
$product_name = $flight->title->rendered . ' - ' . $flight->acf->flight_number;

// Check if product already exists (avoid duplicates)
$product_id = wc_get_product_id_by_sku('flight-' . $flight_id);

if (! $product_id) {
    // Create a temporary WooCommerce product
    $product_id = wp_insert_post(array(
        'post_title'    => $product_name,
        'post_content'  => 'Flight booking for ' . $flight->acf->flight_number,
        'post_status'   => 'publish',
        'post_type'     => 'product'
    ));

    // Set product SKU based on flight ID (avoid duplicates)
    update_post_meta($product_id, '_sku', 'flight-' . $flight_id);

    // Set product price
    update_post_meta($product_id, '_price', $flight->acf->price);
    update_post_meta($product_id, '_regular_price', $flight->acf->price);

    // Make it virtual and downloadable
    update_post_meta($product_id, '_virtual', 'yes');
    update_post_meta($product_id, '_downloadable', 'no');

    // Set stock status to available
    update_post_meta($product_id, '_stock_status', 'instock');
}

if (function_exists('wc_load_cart')) {
    wc_load_cart();
}

// Clear WooCommerce cart before adding new flight
WC()->cart->empty_cart();

// Add product to cart
$added = WC()->cart->add_to_cart($product_id);

if ($added) {
    // Set cart cookies immediately
    WC()->cart->calculate_totals();

    // Force WooCommerce to save the session
    WC()->session->set('cart', WC()->cart->get_cart());

    // Redirect to checkout page
    wp_safe_redirect(wc_get_checkout_url());
    exit;
} else {
    echo "<p style='color:red;'>Something went wrong while adding the flight to the cart.</p>";
}

get_footer();
