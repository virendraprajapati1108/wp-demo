<?php

/**
 * Plugin Name: Custom Product Manager
 * Description: A custom plugin to add products from the WordPress backend.
 * Version: 1.0.0
 * Author: Virendra Prajapati
 */

if (!defined('ABSPATH')) {
    exit();
}

add_action('admin_menu', 'cpm_register_admin_menu');

function cpm_register_admin_menu()
{
    add_menu_page(
        'Add Product',
        'Add Product',
        'manage_options',
        'cpm-add-product',
        'cpm_add_product_page_html',
        'dashicons-cart',
        20
    );

    add_menu_page(
        'Product List',
        'Product List',
        'manage_options',
        'product-list',
        'cpm_render_product_list_page',
        'dashicons-products',
        21
    );
}

function cpm_render_product_list_page()
{
    require_once plugin_dir_path(__FILE__) . 'product-list-table.php';

    $productListTable = new CPM_Product_List_Table();
    $productListTable->prepare_items();
?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Product List</h1>
        <form method="post">
            <?php
            $productListTable->display();
            ?>
        </form>
    </div>
<?php
}


function cpm_add_product_page_html()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $product_saved = false;

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cpm_nonce']) && wp_verify_nonce($_POST['cpm_nonce'], 'cpm_add_product_form')) {

        $title = sanitize_text_field($_POST['product_title']);
        $description = sanitize_textarea_field($_POST['product_description']);
        $availability = isset($_POST['availability']) ? implode(',', array_map('sanitize_text_field', $_POST['availability'])) : '';
        $manufacturer = sanitize_text_field($_POST['manufacturer']);
        $is_featured = isset($_POST['is_featured']) ? sanitize_text_field($_POST['is_featured']) : '';
        $price = floatval($_POST['product_price']);

        $errors = [];

        if (empty($title)) $errors[] = 'Title is required.';
        if (empty($description)) $errors[] = 'Description is required.';
        if (empty($availability)) $errors[] = 'Availability is required.';
        if (empty($manufacturer)) $errors[] = 'Manufacturer is required.';
        if (!in_array($is_featured, ['yes', 'no'])) $errors[] = 'Select if product is featured.';
        if ($price <= 0) $errors[] = 'Enter a valid price.';

        if (empty($errors)) {
            global $wpdb;
            $table = $wpdb->prefix . 'cpm_products';

            $wpdb->insert($table, [
                'title' => $title,
                'description' => $description,
                'availability' => $availability,
                'manufacturer' => $manufacturer,
                'is_featured' => $is_featured,
                'price' => $price,
            ]);

            echo '<div class="notice notice-success"><p>Product saved successfully!</p></div>';

            $product_saved = true;
        } else {
            echo '<div class="notice notice-error"><ul>';
            foreach ($errors as $error) {
                echo '<li>' . esc_html($error) . '</li>';
            }
            echo '</ul></div>';
        }
    }

    $title = (!$product_saved && isset($_POST['product_title'])) ? esc_attr($_POST['product_title']) : '';
    $description = (!$product_saved && isset($_POST['product_description'])) ? esc_textarea($_POST['product_description']) : '';
    $availability = (!$product_saved && isset($_POST['availability'])) ? $_POST['availability'] : [];
    $manufacturer = (!$product_saved && isset($_POST['manufacturer'])) ? esc_attr($_POST['manufacturer']) : '';
    $is_featured = (!$product_saved && isset($_POST['is_featured'])) ? $_POST['is_featured'] : '';
    $price = (!$product_saved && isset($_POST['product_price'])) ? esc_attr($_POST['product_price']) : '';

?>
    <div class="wrap">
        <h1>Add New Product</h1>
        <form method="post" action="">
            <?php wp_nonce_field('cpm_add_product_form', 'cpm_nonce'); ?>

            <table class="form-table">
                <tr>
                    <th scope="row"><label for="product_title">Title <span style="color:red">*</span></label></th>
                    <td>
                        <input name="product_title" type="text" id="product_title" class="regular-text" value="<?php echo $title; ?>" />
                        <p class="description">Enter the product title.</p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="product_description">Description <span style="color:red">*</span></label></th>
                    <td>
                        <textarea name="product_description" id="product_description" rows="5" class="large-text"><?php echo $description; ?></textarea>
                        <p class="description">Enter the product description.</p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">Availability <span style="color:red">*</span></th>
                    <td>
                        <label><input type="checkbox" name="availability[]" value="Client" <?php checked(in_array('Client', $availability)); ?> /> Client</label><br>
                        <label><input type="checkbox" name="availability[]" value="Distributor" <?php checked(in_array('Distributor', $availability)); ?> /> Distributor</label>
                        <p class="description">Choose the product availability.</p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="manufacturer">Manufacture By: <span style="color:red">*</span></label></th>
                    <td>
                        <select name="manufacturer" id="manufacturer">
                            <option value="">--select--</option>
                            <option value="Apple" <?php selected($manufacturer, 'Apple'); ?>>Apple</option>
                            <option value="Samsung" <?php selected($manufacturer, 'Samsung'); ?>>Samsung</option>
                            <option value="Sony" <?php selected($manufacturer, 'Sony'); ?>>Sony</option>
                        </select>
                        <p class="description">Choose the product manufacturing.</p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">Featured Product? <span style="color:red">*</span></th>
                    <td>
                        <label><input type="radio" name="is_featured" value="yes" <?php checked($is_featured, 'yes'); ?> /> Yes</label><br>
                        <label><input type="radio" name="is_featured" value="no" <?php checked($is_featured, 'no'); ?> /> No</label>
                        <p class="description">Enter if the product is featured.</p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="product_price">Price <span style="color:red">*</span></label></th>
                    <td>
                        <input name="product_price" type="number" id="product_price" class="regular-text" step="0.01" value="<?php echo $price; ?>" />
                        <p class="description">Enter the product price.</p>
                    </td>
                </tr>
            </table>
            <?php submit_button('Save'); ?>
        </form>
    </div>

<?php
}

register_activation_hook(__FILE__, 'cpm_create_product_table');

function cpm_create_product_table()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'cpm_products';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        title varchar(255) NOT NULL,
        description text NOT NULL,
        availability text,
        manufacturer varchar(100),
        is_featured varchar(10),
        price float,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
