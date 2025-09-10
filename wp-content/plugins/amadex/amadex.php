<?php
/**
 * Plugin Name:     Amadex
 * Plugin URI:      https://wphacks4u.com
 * Description:     Advanced flight search and booking solution powered by Amadeus API. Easily integrate flight search functionality into your WordPress website.
 * Version:         1.0.0
 * Requires PHP:    5.6
 * Author:          wphacks4u
 * Author URI:      https://wphacks4u.com
 * License:         GPL v2 or later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:     amadex
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('AMADEX_VERSION', '1.0.0');
define('AMADEX_PATH', plugin_dir_path(__FILE__));
define('AMADEX_URL', plugin_dir_url(__FILE__));
define('AMADEX_BASENAME', plugin_basename(__FILE__));

// Include required files
require_once(AMADEX_PATH . 'includes/admin/class-amadex-admin.php');
require_once(AMADEX_PATH . 'includes/admin/class-amadex-settings.php');
require_once(AMADEX_PATH . 'includes/frontend/class-amadex-shortcodes.php');
require_once(AMADEX_PATH . 'includes/api/class-amadex-api.php');

/**
 * Main Amadex Plugin Class
 */
class Amadex {
    /**
     * Instance variable
     *
     * @var Amadex
     */
    private static $instance = null;

    /**
     * Admin class instance
     *
     * @var Amadex_Admin
     */
    public $admin;

    /**
     * Settings class instance
     *
     * @var Amadex_Settings
     */
    public $settings;

    /**
     * API class instance
     *
     * @var Amadex_API
     */
    public $api;

    /**
     * Get singleton instance
     *
     * @return Amadex
     */
    public static function get_instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
        $this->init_classes();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        add_filter('plugin_action_links_' . AMADEX_BASENAME, array($this, 'add_plugin_action_links'));
    }

    /**
     * Initialize classes
     */
    private function init_classes() {
        $this->admin = new Amadex_Admin();
        $this->settings = new Amadex_Settings();
        $this->api = new Amadex_API();
        
        // Initialize shortcodes
        new Amadex_Shortcodes();
    }

    /**
     * Plugin activation
     */
    public function activate() {
        // Default settings initialization
        $default_settings = array(
            'api_key' => '',
            'api_secret' => '',
            'environment' => 'test'
        );
        
        if (!get_option('amadex_api_settings')) {
            update_option('amadex_api_settings', $default_settings);
        }
        
        // Create airports table
        $this->create_airports_table();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Create airports database table
     */
    private function create_airports_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'amadex_airports';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            code varchar(3) NOT NULL,
            name varchar(255) NOT NULL,
            city varchar(100) NOT NULL,
            country varchar(100) NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY code (code),
            KEY city (city)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Check if table is empty
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        
        if ($count == 0) {
            // Import default airports data
            $this->import_default_airports();
        }
    }

    /**
     * Import default airports data
     */
    private function import_default_airports() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'amadex_airports';
        
        // Add some major airports as default data
        $airports = array(
            array('JFK', 'John F. Kennedy International Airport', 'New York', 'United States'),
            array('LAX', 'Los Angeles International Airport', 'Los Angeles', 'United States'),
            array('LHR', 'Heathrow Airport', 'London', 'United Kingdom'),
            array('CDG', 'Charles de Gaulle Airport', 'Paris', 'France'),
            array('DXB', 'Dubai International Airport', 'Dubai', 'United Arab Emirates'),
            array('DEL', 'Indira Gandhi International Airport', 'Delhi', 'India'),
            array('ATL', 'Hartsfield-Jackson Atlanta International Airport', 'Atlanta', 'United States'),
            array('PEK', 'Beijing Capital International Airport', 'Beijing', 'China'),
            array('HND', 'Tokyo Haneda Airport', 'Tokyo', 'Japan'),
            array('ORD', 'O\'Hare International Airport', 'Chicago', 'United States'),
            array('LGW', 'Gatwick Airport', 'London', 'United Kingdom'),
            array('FRA', 'Frankfurt Airport', 'Frankfurt', 'Germany'),
            array('IST', 'Istanbul Airport', 'Istanbul', 'Turkey'),
            array('AMS', 'Amsterdam Airport Schiphol', 'Amsterdam', 'Netherlands'),
            array('SIN', 'Singapore Changi Airport', 'Singapore', 'Singapore'),
            array('ICN', 'Incheon International Airport', 'Seoul', 'South Korea'),
            array('BOM', 'Chhatrapati Shivaji Maharaj International Airport', 'Mumbai', 'India'),
            array('SYD', 'Sydney Airport', 'Sydney', 'Australia'),
            array('MEX', 'Mexico City International Airport', 'Mexico City', 'Mexico'),
            array('YYZ', 'Toronto Pearson International Airport', 'Toronto', 'Canada')
        );
        
        foreach ($airports as $airport) {
            $wpdb->insert(
                $table_name,
                array(
                    'code' => $airport[0],
                    'name' => $airport[1],
                    'city' => $airport[2],
                    'country' => $airport[3]
                )
            );
        }
    }

    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain('amadex', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    /**
     * Add settings link to plugin action links
     *
     * @param array $links
     * @return array
     */
    public function add_plugin_action_links($links) {
        $settings_link = '<a href="' . admin_url('admin.php?page=amadex-settings') . '">' . __('Settings', 'amadex') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
}

// Initialize the plugin
function amadex() {
    return Amadex::get_instance();
}

// Start the plugin
amadex();