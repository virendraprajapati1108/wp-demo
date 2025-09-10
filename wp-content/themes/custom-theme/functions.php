
<?php

$theme = wp_get_theme();
$version = $theme->get('Version');

/** Post thumbanail support to theme */
add_theme_support('post-thumbnails');

/** Regster the header menu */
function mytheme_register_menus()
{
    register_nav_menu('main-menu', __('Main Menu'));
}
add_action('init', 'mytheme_register_menus');

/** Add requesr file for custom code */
require_once get_template_directory() . '/inc/custom-post-types.php';
require_once get_template_directory() . '/inc/custom-ajax.php';
require_once get_template_directory() . '/inc/rest-api.php';

/**
 * Enqueue scripts and styles.
 */

function mycustom_theme_name_scripts()
{
    // wp_enqueue_script('jquery');
    wp_enqueue_style('style-name', get_template_directory_uri() . '/style.css', array(), filemtime(get_template_directory_uri() . '/style.css'), 'all');
    wp_enqueue_style('header', get_template_directory_uri() . '/css/header-style.css', array(), filemtime(get_template_directory_uri() . '/css/header-style.css'), 'all');
    wp_enqueue_style('footer', get_template_directory_uri() . '/css/footer-style.css', array(), filemtime(get_template_directory_uri() . '/css/footer-style.css'), 'all');
    wp_enqueue_script('script-name', get_template_directory_uri() . '/js/example.js', array(), filemtime(get_template_directory_uri() . '/js/example.js'), true);
    wp_enqueue_script('my-ajax-script', get_template_directory_uri() . '/js/ajax.js', array('jquery'), filemtime(get_template_directory_uri() . '/js/ajax.js'), true);
    wp_localize_script(
        'my-ajax-script',
        'ajax_object',
        array(
            'ajax_url' => admin_url('admin-ajax.php')
        )
    );
}
add_action('wp_enqueue_scripts', 'mycustom_theme_name_scripts');
