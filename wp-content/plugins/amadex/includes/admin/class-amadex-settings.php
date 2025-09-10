<?php
/**
 * Settings class for Amadex plugin
 *
 * @package Amadex
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Amadex Settings Class
 */
class Amadex_Settings {
    
    /**
     * Settings tabs
     *
     * @var array
     */
    private $tabs;
    
    /**
     * Current tab
     *
     * @var string
     */
    private $current_tab;
    
    /**
     * Constructor
     */
    public function __construct() {
        // Initialize tabs
        $this->tabs = array(
            'general' => __('General', 'amadex'),
            'api' => __('API Settings', 'amadex'),
            'display' => __('Display Options', 'amadex'),
            'advanced' => __('Advanced', 'amadex')
        );
        
        // Register settings
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        // Register settings sections and fields
        
        // General settings
        register_setting('amadex_general_settings', 'amadex_general_settings');
        
        add_settings_section(
            'amadex_general_section',
            __('General Settings', 'amadex'),
            array($this, 'general_section_callback'),
            'amadex_general_settings'
        );
        
        add_settings_field(
            'enable_plugin',
            __('Enable Plugin', 'amadex'),
            array($this, 'enable_plugin_callback'),
            'amadex_general_settings',
            'amadex_general_section'
        );
        
        add_settings_field(
            'cache_duration',
            __('Cache Duration (minutes)', 'amadex'),
            array($this, 'cache_duration_callback'),
            'amadex_general_settings',
            'amadex_general_section'
        );
        
        // API settings
        register_setting('amadex_api_settings', 'amadex_api_settings');
        
        add_settings_section(
            'amadex_api_section',
            __('Amadeus API Credentials', 'amadex'),
            array($this, 'api_section_callback'),
            'amadex_api_settings'
        );
        
        add_settings_field(
            'api_key',
            __('API Key', 'amadex'),
            array($this, 'api_key_callback'),
            'amadex_api_settings',
            'amadex_api_section'
        );
        
        add_settings_field(
            'api_secret',
            __('API Secret', 'amadex'),
            array($this, 'api_secret_callback'),
            'amadex_api_settings',
            'amadex_api_section'
        );
        
        add_settings_field(
            'environment',
            __('API Environment', 'amadex'),
            array($this, 'environment_callback'),
            'amadex_api_settings',
            'amadex_api_section'
        );
        
        // Display options
        register_setting('amadex_display_settings', 'amadex_display_settings');
        
        add_settings_section(
            'amadex_display_section',
            __('Display Options', 'amadex'),
            array($this, 'display_section_callback'),
            'amadex_display_settings'
        );
        
        add_settings_field(
            'search_form_title',
            __('Search Form Title', 'amadex'),
            array($this, 'search_form_title_callback'),
            'amadex_display_settings',
            'amadex_display_section'
        );
        
        add_settings_field(
            'button_text',
            __('Search Button Text', 'amadex'),
            array($this, 'button_text_callback'),
            'amadex_display_settings',
            'amadex_display_section'
        );
        
        add_settings_field(
            'default_theme',
            __('Default Theme', 'amadex'),
            array($this, 'default_theme_callback'),
            'amadex_display_settings',
            'amadex_display_section'
        );
        
        add_settings_field(
            'custom_css',
            __('Custom CSS', 'amadex'),
            array($this, 'custom_css_callback'),
            'amadex_display_settings',
            'amadex_display_section'
        );
        
        // Advanced settings
        register_setting('amadex_advanced_settings', 'amadex_advanced_settings');
        
        add_settings_section(
            'amadex_advanced_section',
            __('Advanced Settings', 'amadex'),
            array($this, 'advanced_section_callback'),
            'amadex_advanced_settings'
        );
        
        add_settings_field(
            'timeout',
            __('API Timeout (seconds)', 'amadex'),
            array($this, 'timeout_callback'),
            'amadex_advanced_settings',
            'amadex_advanced_section'
        );
        
        add_settings_field(
            'error_logging',
            __('Error Logging', 'amadex'),
            array($this, 'error_logging_callback'),
            'amadex_advanced_settings',
            'amadex_advanced_section'
        );
        
        add_settings_field(
            'debug_mode',
            __('Debug Mode', 'amadex'),
            array($this, 'debug_mode_callback'),
            'amadex_advanced_settings',
            'amadex_advanced_section'
        );
    }
    
    /**
     * General section callback
     */
    public function general_section_callback() {
        echo '<p>' . __('Configure general plugin settings.', 'amadex') . '</p>';
    }
    
    /**
     * API section callback
     */
    public function api_section_callback() {
        echo '<p>' . __('Enter your Amadeus API credentials. You can obtain these from the <a href="https://developers.amadeus.com" target="_blank">Amadeus for Developers</a> portal.', 'amadex') . '</p>';
    }
    
    /**
     * Display section callback
     */
    public function display_section_callback() {
        echo '<p>' . __('Customize the appearance of the flight search form.', 'amadex') . '</p>';
    }
    
    /**
     * Advanced section callback
     */
    public function advanced_section_callback() {
        echo '<p>' . __('Advanced settings for experienced users. Only modify these if you know what you\'re doing.', 'amadex') . '</p>';
    }
    
    /**
     * Enable plugin callback
     */
    public function enable_plugin_callback() {
        $options = get_option('amadex_general_settings');
        $value = isset($options['enable_plugin']) ? $options['enable_plugin'] : 1;
        ?>
        <label for="enable_plugin">
            <input type="checkbox" id="enable_plugin" name="amadex_general_settings[enable_plugin]" value="1" <?php checked(1, $value); ?>>
            <?php _e('Enable Amadex plugin functionality', 'amadex'); ?>
        </label>
        <?php
    }
    
    /**
     * Cache duration callback
     */
    public function cache_duration_callback() {
        $options = get_option('amadex_general_settings');
        $value = isset($options['cache_duration']) ? $options['cache_duration'] : 60;
        ?>
        <input type="number" id="cache_duration" name="amadex_general_settings[cache_duration]" value="<?php echo esc_attr($value); ?>" min="0" step="1" class="small-text">
        <p class="description"><?php _e('Duration in minutes to cache API responses. Set to 0 to disable caching.', 'amadex'); ?></p>
        <?php
    }
    
    /**
     * API key callback
     */
    public function api_key_callback() {
        $options = get_option('amadex_api_settings');
        $value = isset($options['api_key']) ? $options['api_key'] : '';
        ?>
        <input type="text" id="api_key" name="amadex_api_settings[api_key]" value="<?php echo esc_attr($value); ?>" class="regular-text">
        <p class="description"><?php _e('Your Amadeus API Key (Client ID)', 'amadex'); ?></p>
        <?php
    }
    
    /**
     * API secret callback
     */
    public function api_secret_callback() {
        $options = get_option('amadex_api_settings');
        $value = isset($options['api_secret']) ? $options['api_secret'] : '';
        ?>
        <input type="password" id="api_secret" name="amadex_api_settings[api_secret]" value="<?php echo esc_attr($value); ?>" class="regular-text">
        <p class="description"><?php _e('Your Amadeus API Secret (Client Secret)', 'amadex'); ?></p>
        <?php
    }
    
    /**
     * Environment callback
     */
    public function environment_callback() {
        $options = get_option('amadex_api_settings');
        $value = isset($options['environment']) ? $options['environment'] : 'test';
        ?>
        <select id="environment" name="amadex_api_settings[environment]">
            <option value="test" <?php selected('test', $value); ?>><?php _e('Test', 'amadex'); ?></option>
            <option value="production" <?php selected('production', $value); ?>><?php _e('Production', 'amadex'); ?></option>
        </select>
        <p class="description"><?php _e('Choose between test and production environments. Use test for development.', 'amadex'); ?></p>
        <?php
    }
    
    /**
     * Search form title callback
     */
    public function search_form_title_callback() {
        $options = get_option('amadex_display_settings');
        $value = isset($options['search_form_title']) ? $options['search_form_title'] : __('Flight Search', 'amadex');
        ?>
        <input type="text" id="search_form_title" name="amadex_display_settings[search_form_title]" value="<?php echo esc_attr($value); ?>" class="regular-text">
        <p class="description"><?php _e('Title displayed above the search form', 'amadex'); ?></p>
        <?php
    }
    
    /**
     * Button text callback
     */
    public function button_text_callback() {
        $options = get_option('amadex_display_settings');
        $value = isset($options['button_text']) ? $options['button_text'] : __('Search Flights', 'amadex');
        ?>
        <input type="text" id="button_text" name="amadex_display_settings[button_text]" value="<?php echo esc_attr($value); ?>" class="regular-text">
        <p class="description"><?php _e('Text for the search button', 'amadex'); ?></p>
        <?php
    }
    
    /**
     * Default theme callback
     */
    public function default_theme_callback() {
        $options = get_option('amadex_display_settings');
        $value = isset($options['default_theme']) ? $options['default_theme'] : 'light';
        ?>
        <select id="default_theme" name="amadex_display_settings[default_theme]">
            <option value="light" <?php selected('light', $value); ?>><?php _e('Light', 'amadex'); ?></option>
            <option value="dark" <?php selected('dark', $value); ?>><?php _e('Dark', 'amadex'); ?></option>
        </select>
        <p class="description"><?php _e('Default theme for the search form. Can be overridden in the shortcode.', 'amadex'); ?></p>
        <?php
    }
    
    /**
     * Custom CSS callback
     */
    public function custom_css_callback() {
        $options = get_option('amadex_display_settings');
        $value = isset($options['custom_css']) ? $options['custom_css'] : '';
        ?>
        <textarea id="custom_css" name="amadex_display_settings[custom_css]" rows="8" cols="50" class="large-text code"><?php echo esc_textarea($value); ?></textarea>
        <p class="description"><?php _e('Add custom CSS to style the search form and results', 'amadex'); ?></p>
        <?php
    }
    
    /**
     * Timeout callback
     */
    public function timeout_callback() {
        $options = get_option('amadex_advanced_settings');
        $value = isset($options['timeout']) ? $options['timeout'] : 10;
        ?>
        <input type="number" id="timeout" name="amadex_advanced_settings[timeout]" value="<?php echo esc_attr($value); ?>" min="1" max="60" step="1" class="small-text">
        <p class="description"><?php _e('API request timeout in seconds (1-60)', 'amadex'); ?></p>
        <?php
    }
    
    /**
     * Error logging callback
     */
    public function error_logging_callback() {
        $options = get_option('amadex_advanced_settings');
        $value = isset($options['error_logging']) ? $options['error_logging'] : 1;
        ?>
        <label for="error_logging">
            <input type="checkbox" id="error_logging" name="amadex_advanced_settings[error_logging]" value="1" <?php checked(1, $value); ?>>
            <?php _e('Enable error logging', 'amadex'); ?>
        </label>
        <p class="description"><?php _e('Log API errors and plugin issues for debugging purposes', 'amadex'); ?></p>
        <?php
    }
    
    /**
     * Debug mode callback
     */
    public function debug_mode_callback() {
        $options = get_option('amadex_advanced_settings');
        $value = isset($options['debug_mode']) ? $options['debug_mode'] : 0;
        ?>
        <label for="debug_mode">
            <input type="checkbox" id="debug_mode" name="amadex_advanced_settings[debug_mode]" value="1" <?php checked(1, $value); ?>>
            <?php _e('Enable debug mode', 'amadex'); ?>
        </label>
        <p class="description"><?php _e('Display additional debugging information in the browser console', 'amadex'); ?></p>
        <?php
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        // Get current tab
        $this->current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
        
        // Validate current tab
        if (!array_key_exists($this->current_tab, $this->tabs)) {
            $this->current_tab = 'general';
        }
        
        // Get current tab settings page
        $current_tab_page = 'amadex_' . $this->current_tab . '_settings';
        ?>
        <div class="wrap amadex-admin">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="amadex-admin-header">
                <div class="amadex-logo">
                    <img src="<?php echo AMADEX_URL; ?>assets/images/amadex-logo.png" alt="Amadex Logo">
                </div>
            </div>
            
            <div class="amadex-settings-tabs">
                <h2 class="nav-tab-wrapper">
                    <?php
                    foreach ($this->tabs as $tab_id => $tab_name) {
                        $tab_url = add_query_arg(array(
                            'page' => 'amadex-settings',
                            'tab' => $tab_id
                        ), admin_url('admin.php'));
                        $active = $this->current_tab === $tab_id ? 'nav-tab-active' : '';
                        echo '<a href="' . esc_url($tab_url) . '" class="nav-tab ' . $active . '">' . esc_html($tab_name) . '</a>';
                    }
                    ?>
                </h2>
            </div>
            
            <div class="amadex-settings-content">
                <form method="post" action="options.php">
                    <?php
                    // Output security fields for the registered setting
                    settings_fields($current_tab_page);
                    
                    // Output setting sections and their fields
                    do_settings_sections($current_tab_page);
                    
                    // Output save settings button
                    submit_button(__('Save Settings', 'amadex'));
                    ?>
                </form>
            </div>
            
            <?php if ($this->current_tab === 'api'): ?>
            <div class="amadex-card">
                <div class="amadex-card-header">
                    <h2><?php _e('Test API Connection', 'amadex'); ?></h2>
                </div>
                <div class="amadex-card-body">
                    <p><?php _e('Click the button below to test your API connection.', 'amadex'); ?></p>
                    <button id="amadex-test-api" class="button button-secondary">
                        <?php _e('Test Connection', 'amadex'); ?>
                    </button>
                    <div id="amadex-test-result" class="amadex-test-result"></div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php
    }
 }