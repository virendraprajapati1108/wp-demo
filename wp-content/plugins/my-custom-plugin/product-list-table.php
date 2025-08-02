<?php

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class CPM_Product_List_Table extends WP_List_Table
{

    public function __construct()
    {
        parent::__construct([
            'singular' => 'Product',
            'plural'   => 'Products',
            'ajax'     => false
        ]);
    }

    public function get_columns()
    {
        return [
            'id'            => 'ID',
            'title'         => 'Title',
            'manufacturer'  => 'Manufacturer',
            'price'         => 'Price',
            'availability'  => 'Availability',
            'is_featured'   => 'Featured',
        ];
    }

    public function prepare_items()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cpm_products';

        $query = "SELECT * FROM $table_name";
        $products = $wpdb->get_results($query, ARRAY_A);

        $this->items = $products;

        $columns = $this->get_columns();

        $hidden = [];
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = [$columns, $hidden, $sortable];
    }

    public function column_default($item, $column_name)
    {
        return $item[$column_name];
    }
}
