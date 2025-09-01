<?php

// Book Custom Post type

function register_books_post_type()
{
    $labels = array(
        'name'               => 'Books',
        'singular_name'      => 'Book',
        'add_new'            => 'Add New Book',
        'add_new_item'       => 'Add New Book',
        'edit_item'          => 'Edit Book',
        'new_item'           => 'New Book',
        'view_item'          => 'View Book',
        'search_items'       => 'Search Books',
        'not_found'          => 'No books found',
        'menu_name'          => 'Books'
    );

    register_post_type('book', array(
        'labels'        => $labels,
        'public'        => true,
        'has_archive'   => true,
        'rewrite'       => array('slug' => 'books'),
        'supports'      => array('title', 'excerpt', 'thumbnail'),
        'show_in_rest'  => true,
        'menu_icon'     => 'dashicons-book'
    ));
}

add_action('init', 'register_books_post_type');

function register_book_taxonomies()
{
    // Fiction taxonomy labels
    $fiction_labels = array(
        'name'              => 'Fiction Categories',
        'singular_name'     => 'Fiction Category',
        'search_items'      => 'Search Fiction Categories',
        'all_items'         => 'All Fiction Categories',
        'parent_item'       => 'Parent Fiction Category',
        'parent_item_colon' => 'Parent Fiction Category:',
        'edit_item'         => 'Edit Fiction Category',
        'update_item'       => 'Update Fiction Category',
        'add_new_item'      => 'Add New Fiction Category',
        'new_item_name'     => 'New Fiction Category Name',
        'menu_name'         => 'Fiction'
    );

    register_taxonomy('fiction', 'book', array(
        'labels'            => $fiction_labels,
        'hierarchical'      => true,
        'show_in_rest'      => true,
        'public'            => true,
        'rewrite'           => array('slug' => 'fiction'),
    ));

    // Non-Fiction taxonomy labels
    $non_fiction_labels = array(
        'name'              => 'Non-Fiction Categories',
        'singular_name'     => 'Non-Fiction Category',
        'search_items'      => 'Search Non-Fiction Categories',
        'all_items'         => 'All Non-Fiction Categories',
        'parent_item'       => 'Parent Non-Fiction Category',
        'parent_item_colon' => 'Parent Non-Fiction Category:',
        'edit_item'         => 'Edit Non-Fiction Category',
        'update_item'       => 'Update Non-Fiction Category',
        'add_new_item'      => 'Add New Non-Fiction Category',
        'new_item_name'     => 'New Non-Fiction Category Name',
        'menu_name'         => 'Non-Fiction'
    );

    register_taxonomy('non_fiction', 'book', array(
        'labels'            => $non_fiction_labels,
        'hierarchical'      => true,
        'show_in_rest'      => true,
        'public'            => true,
        'rewrite'           => array('slug' => 'non-fiction'),
    ));
}
add_action('init', 'register_book_taxonomies');
