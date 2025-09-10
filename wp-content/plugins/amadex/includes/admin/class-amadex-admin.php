<?php
/**
 * Admin class for Amadex plugin
 *
 * @package Amadex
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Amadex Admin Class
 */
class Amadex_Admin {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Admin styles and scripts
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }
    
    /**
     * Add admin menu and submenus
     */
    public function add_admin_menu() {
        // Main menu
        add_menu_page(
            __('Amadex', 'amadex'),
            __('Amadex', 'amadex'),
            'manage_options',
            'amadex-dashboard',
            array($this, 'dashboard_page'),
            'dashicons-airplane',
            30
        );
        
        // Dashboard submenu
        add_submenu_page(
            'amadex-dashboard',
            __('Dashboard', 'amadex'),
            __('Dashboard', 'amadex'),
            'manage_options',
            'amadex-dashboard',
            array($this, 'dashboard_page')
        );
        
        // API Settings submenu
        add_submenu_page(
            'amadex-dashboard',
            __('API Settings', 'amadex'),
            __('API Settings', 'amadex'),
            'manage_options',
            'amadex-settings',
            array($this, 'settings_page')
        );
        
        // Documentation submenu
        add_submenu_page(
            'amadex-dashboard',
            __('Documentation', 'amadex'),
            __('Documentation', 'amadex'),
            'manage_options',
            'amadex-documentation',
            array($this, 'documentation_page')
        );
        
        // Contact Us submenu
        add_submenu_page(
            'amadex-dashboard',
            __('Contact Us', 'amadex'),
            __('Contact Us', 'amadex'),
            'manage_options',
            'amadex-contact',
            array($this, 'contact_page')
        );
        
        // Airports submenu
        add_submenu_page(
            'amadex-dashboard',
            __('Airports', 'amadex'),
            __('Airports', 'amadex'),
            'manage_options',
            'amadex-airports',
            array($this, 'airports_page')
        );
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        // Only load on plugin pages
        if (strpos($hook, 'amadex') === false) {
            return;
        }
        
        // Admin CSS
        wp_enqueue_style(
            'amadex-admin-style',
            AMADEX_URL . 'assets/css/admin.css',
            array(),
            AMADEX_VERSION
        );
        
        // Admin JS
        wp_enqueue_script(
            'amadex-admin-script',
            AMADEX_URL . 'assets/js/admin.js',
            array('jquery'),
            AMADEX_VERSION,
            true
        );
        
        // Add localized script with nonce
        wp_localize_script(
            'amadex-admin-script',
            'amadex_admin',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('amadex_admin_nonce')
            )
        );
    }
    
    /**
     * Dashboard page callback
     */
    public function dashboard_page() {
        ?>
        <div class="wrap amadex-admin">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="amadex-admin-header">
                <div class="amadex-logo">
                    <img src="<?php echo AMADEX_URL; ?>assets/images/amadex-logo.png" alt="Amadex Logo">
                </div>
                <div class="amadex-version">
                    <span><?php printf(__('Version %s', 'amadex'), AMADEX_VERSION); ?></span>
                </div>
            </div>
            
            <div class="amadex-admin-cards">
                <div class="amadex-card">
                    <div class="amadex-card-header">
                        <h2><?php _e('API Connection Status', 'amadex'); ?></h2>
                    </div>
                    <div class="amadex-card-body">
                        <?php
                        $settings = get_option('amadex_api_settings');
                        if (empty($settings['api_key']) || empty($settings['api_secret'])) {
                            echo '<div class="amadex-status amadex-status-error">';
                            echo '<span class="dashicons dashicons-warning"></span>';
                            echo '<p>' . __('API credentials not configured', 'amadex') . '</p>';
                            echo '</div>';
                            echo '<p>' . __('Please configure your Amadeus API credentials in the API Settings section.', 'amadex') . '</p>';
                            echo '<a href="' . admin_url('admin.php?page=amadex-settings') . '" class="button button-primary">' . __('Configure API Settings', 'amadex') . '</a>';
                        } else {
                            echo '<div class="amadex-status amadex-status-success">';
                            echo '<span class="dashicons dashicons-yes-alt"></span>';
                            echo '<p>' . __('API credentials configured', 'amadex') . '</p>';
                            echo '</div>';
                            echo '<p>' . __('Your Amadeus API credentials are configured and ready to use.', 'amadex') . '</p>';
                            echo '<a href="' . admin_url('admin.php?page=amadex-settings') . '" class="button button-secondary">' . __('Update API Settings', 'amadex') . '</a>';
                        }
                        ?>
                    </div>
                </div>
                
                <div class="amadex-card">
                    <div class="amadex-card-header">
                        <h2><?php _e('Quick Start', 'amadex'); ?></h2>
                    </div>
                    <div class="amadex-card-body">
                        <p><?php _e('Use the shortcode below to display the flight search form on any page or post:', 'amadex'); ?></p>
                        <div class="amadex-shortcode-wrap">
                            <code>[amadex-flight-search]</code>
                            <button class="amadex-copy-shortcode button button-secondary" data-shortcode="[amadex-flight-search]">
                                <span class="dashicons dashicons-clipboard"></span>
                            </button>
                        </div>
                        <p><?php _e('Need help?', 'amadex'); ?> <a href="<?php echo admin_url('admin.php?page=amadex-documentation'); ?>"><?php _e('Check our documentation', 'amadex'); ?></a></p>
                    </div>
                </div>
            </div>
            
            <div class="amadex-admin-cards">
                <div class="amadex-card amadex-card-full">
                    <div class="amadex-card-header">
                        <h2><?php _e('Recent Searches', 'amadex'); ?></h2>
                    </div>
                    <div class="amadex-card-body">
                        <p><?php _e('No recent searches found.', 'amadex'); ?></p>
                        <p><?php _e('Search statistics will appear here after users start using the flight search form on your website.', 'amadex'); ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Settings page callback
     */
    public function settings_page() {
        // Settings page is handled by the Amadex_Settings class
        amadex()->settings->render_settings_page();
    }
    
    /**
     * Documentation page callback
     */
    public function documentation_page() {
        ?>
        <div class="wrap amadex-admin">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="amadex-admin-header">
                <div class="amadex-logo">
                    <img src="<?php echo AMADEX_URL; ?>assets/images/amadex-logo.png" alt="Amadex Logo">
                </div>
            </div>
            
            <div class="amadex-admin-cards">
                <div class="amadex-card amadex-card-full">
                    <div class="amadex-card-header">
                        <h2><?php _e('Documentation', 'amadex'); ?></h2>
                    </div>
                    <div class="amadex-card-body">
                        <div class="amadex-documentation-tabs">
                            <div class="amadex-tabs-nav">
                                <button class="amadex-tab-button active" data-tab="getting-started"><?php _e('Getting Started', 'amadex'); ?></button>
                                <button class="amadex-tab-button" data-tab="shortcodes"><?php _e('Shortcodes', 'amadex'); ?></button>
                                <button class="amadex-tab-button" data-tab="api-info"><?php _e('API Information', 'amadex'); ?></button>
                                <button class="amadex-tab-button" data-tab="faq"><?php _e('FAQ', 'amadex'); ?></button>
                            </div>
                            
                            <div class="amadex-tab-content active" id="getting-started">
                                <h3><?php _e('Getting Started with Amadex', 'amadex'); ?></h3>
                                <p><?php _e('Follow these steps to get started with Amadex:', 'amadex'); ?></p>
                                <ol>
                                    <li>
                                        <strong><?php _e('Sign up for an Amadeus Developer Account', 'amadex'); ?></strong><br>
                                        <?php _e('Visit <a href="https://developers.amadeus.com" target="_blank">Amadeus for Developers</a> to create an account and register your application.', 'amadex'); ?>
                                    </li>
                                    <li>
                                        <strong><?php _e('Get API Key and Secret', 'amadex'); ?></strong><br>
                                        <?php _e('After registering your application, you will receive an API Key and Secret.', 'amadex'); ?>
                                    </li>
                                    <li>
                                        <strong><?php _e('Configure Amadex Settings', 'amadex'); ?></strong><br>
                                        <?php _e('Enter your API Key and Secret in the API Settings page.', 'amadex'); ?>
                                    </li>
                                    <li>
                                        <strong><?php _e('Add the Flight Search Form to Your Website', 'amadex'); ?></strong><br>
                                        <?php _e('Use the shortcode [amadex-flight-search] to display the flight search form on any page or post.', 'amadex'); ?>
                                    </li>
                                </ol>
                            </div>
                            
                            <div class="amadex-tab-content" id="shortcodes">
                                <h3><?php _e('Available Shortcodes', 'amadex'); ?></h3>
                                <div class="amadex-shortcode-doc">
                                    <h4>[amadex-flight-search]</h4>
                                    <p><?php _e('Displays the flight search form.', 'amadex'); ?></p>
                                    <h5><?php _e('Parameters:', 'amadex'); ?></h5>
                                    <ul>
                                        <li><code>title</code> - <?php _e('Custom title for the form (default: "Flight Search")', 'amadex'); ?></li>
                                        <li><code>button_text</code> - <?php _e('Custom text for the search button (default: "Search Flights")', 'amadex'); ?></li>
                                        <li><code>theme</code> - <?php _e('Form theme: "light" or "dark" (default: "light")', 'amadex'); ?></li>
                                    </ul>
                                    <h5><?php _e('Example:', 'amadex'); ?></h5>
                                    <code>[amadex-flight-search title="Find Your Flight" button_text="Find Flights" theme="dark"]</code>
                                </div>
                            </div>
                            
                            <div class="amadex-tab-content" id="api-info">
                                <h3><?php _e('Amadeus API Information', 'amadex'); ?></h3>
                                <p><?php _e('The Amadex plugin uses the Amadeus Flight Offers Search API to retrieve flight information.', 'amadex'); ?></p>
                                <p><?php _e('The Flight Offers Search API provides a list of flight offers based on your search parameters, including:', 'amadex'); ?></p>
                                <ul>
                                    <li><?php _e('Origin and Destination', 'amadex'); ?></li>
                                    <li><?php _e('Departure and Return Dates', 'amadex'); ?></li>
                                    <li><?php _e('Number of Passengers', 'amadex'); ?></li>
                                    <li><?php _e('Travel Class', 'amadex'); ?></li>
                                    <li><?php _e('and more...', 'amadex'); ?></li>
                                </ul>
                                <p><?php _e('For more information about the Amadeus API, visit:', 'amadex'); ?></p>
                                <a href="https://developers.amadeus.com/self-service/category/air" target="_blank" class="button button-secondary"><?php _e('Amadeus API Documentation', 'amadex'); ?></a>
                            </div>
                            
                            <div class="amadex-tab-content" id="faq">
                                <h3><?php _e('Frequently Asked Questions', 'amadex'); ?></h3>
                                
                                <div class="amadex-faq-item">
                                    <div class="amadex-faq-question"><?php _e('How do I get an Amadeus API key?', 'amadex'); ?></div>
                                    <div class="amadex-faq-answer">
                                        <p><?php _e('You need to create an account on the <a href="https://developers.amadeus.com" target="_blank">Amadeus for Developers</a> portal, then create a new application to get your API key and secret.', 'amadex'); ?></p>
                                    </div>
                                </div>
                                
                                <div class="amadex-faq-item">
                                    <div class="amadex-faq-question"><?php _e('Does the plugin support booking functionality?', 'amadex'); ?></div>
                                    <div class="amadex-faq-answer">
                                        <p><?php _e('Currently, the plugin only supports searching for flights. Booking functionality may be added in future versions.', 'amadex'); ?></p>
                                    </div>
                                </div>
                                
                                <div class="amadex-faq-item">
                                    <div class="amadex-faq-question"><?php _e('How do I style the flight search form?', 'amadex'); ?></div>
                                    <div class="amadex-faq-answer">
                                        <p><?php _e('You can use the "theme" parameter in the shortcode to choose between light and dark themes. For custom styling, you can add your own CSS to your theme.', 'amadex'); ?></p>
                                    </div>
                                </div>
                                
                                <div class="amadex-faq-item">
                                    <div class="amadex-faq-question"><?php _e('Are there any API usage limits?', 'amadex'); ?></div>
                                    <div class="amadex-faq-answer">
                                        <p><?php _e('Yes, Amadeus API has usage limits depending on your subscription plan. Please check your Amadeus Developer account for your specific limits.', 'amadex'); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Contact Us page callback
     */
    public function contact_page() {
        ?>
        <div class="wrap amadex-admin">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="amadex-admin-header">
                <div class="amadex-logo">
                    <img src="<?php echo AMADEX_URL; ?>assets/images/amadex-logo.png" alt="Amadex Logo">
                </div>
            </div>
            
            <div class="amadex-admin-cards">
                <div class="amadex-card">
                    <div class="amadex-card-header">
                        <h2><?php _e('Contact Information', 'amadex'); ?></h2>
                    </div>
                    <div class="amadex-card-body">
                        <div class="amadex-contact-info">
                            <div class="amadex-contact-item">
                                <span class="dashicons dashicons-admin-site-alt3"></span>
                                <div class="amadex-contact-detail">
                                    <h3><?php _e('Website', 'amadex'); ?></h3>
                                    <p><a href="https://wphacks4u.com" target="_blank">wphacks4u.com</a></p>
                                </div>
                            </div>
                            <div class="amadex-contact-item">
                                <span class="dashicons dashicons-email-alt"></span>
                                <div class="amadex-contact-detail">
                                    <h3><?php _e('Email', 'amadex'); ?></h3>
                                    <p><a href="mailto:support@wphacks4u.com">support@wphacks4u.com</a></p>
                                </div>
                            </div>
                            <div class="amadex-contact-item">
                                <span class="dashicons dashicons-twitter"></span>
                                <div class="amadex-contact-detail">
                                    <h3><?php _e('Twitter', 'amadex'); ?></h3>
                                    <p><a href="https://twitter.com/wphacks4u" target="_blank">@wphacks4u</a></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="amadex-card">
                    <div class="amadex-card-header">
                        <h2><?php _e('Support', 'amadex'); ?></h2>
                    </div>
                    <div class="amadex-card-body">
                        <p><?php _e('For plugin support, please use one of the following options:', 'amadex'); ?></p>
                        <ul class="amadex-support-options">
                            <li>
                                <a href="https://wphacks4u.com/support" target="_blank" class="button button-primary">
                                    <span class="dashicons dashicons-admin-users"></span>
                                    <?php _e('Support Portal', 'amadex'); ?>
                                </a>
                            </li>
                            <li>
                                <a href="https://wphacks4u.com/documentation" target="_blank" class="button button-secondary">
                                    <span class="dashicons dashicons-book"></span>
                                    <?php _e('Documentation', 'amadex'); ?>
                                </a>
                            </li>
                            <li>
                                <a href="https://github.com/wphacks4u/amadex" target="_blank" class="button button-secondary">
                                    <span class="dashicons dashicons-code-standards"></span>
                                    <?php _e('GitHub Repository', 'amadex'); ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="amadex-admin-cards">
                <div class="amadex-card amadex-card-full">
                    <div class="amadex-card-header">
                        <h2><?php _e('Send us a Message', 'amadex'); ?></h2>
                    </div>
                    <div class="amadex-card-body">
                        <form id="amadex-contact-form" class="amadex-contact-form">
                            <div class="amadex-form-row">
                                <div class="amadex-form-group">
                                    <label for="amadex-name"><?php _e('Name', 'amadex'); ?></label>
                                    <input type="text" id="amadex-name" name="name" required>
                                </div>
                                <div class="amadex-form-group">
                                    <label for="amadex-email"><?php _e('Email', 'amadex'); ?></label>
                                    <input type="email" id="amadex-email" name="email" required>
                                </div>
                            </div>
                            <div class="amadex-form-group">
                                <label for="amadex-subject"><?php _e('Subject', 'amadex'); ?></label>
                                <input type="text" id="amadex-subject" name="subject" required>
                            </div>
                            <div class="amadex-form-group">
                                <label for="amadex-message"><?php _e('Message', 'amadex'); ?></label>
                                <textarea id="amadex-message" name="message" rows="5" required></textarea>
                            </div>
                            <div class="amadex-form-submit">
                                <button type="submit" class="button button-primary"><?php _e('Send Message', 'amadex'); ?></button>
                                <div id="amadex-contact-response"></div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Airports page callback
     */
    public function airports_page() {
        // Handle airport operations
        if (isset($_POST['action']) && check_admin_referer('amadex_airports_nonce')) {
            $action = sanitize_text_field($_POST['action']);
            
            if ($action === 'add_airport' && current_user_can('manage_options')) {
                $this->add_airport();
            } elseif ($action === 'delete_airport' && current_user_can('manage_options')) {
                $this->delete_airport();
            }
        }
        
        // Get airports
        global $wpdb;
        $table_name = $wpdb->prefix . 'amadex_airports';
        $airports = $wpdb->get_results("SELECT * FROM $table_name ORDER BY code", ARRAY_A);
        ?>
        <div class="wrap amadex-admin">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="amadex-admin-header">
                <div class="amadex-logo">
                    <img src="<?php echo AMADEX_URL; ?>assets/images/amadex-logo.png" alt="Amadex Logo">
                </div>
            </div>
            
            <div class="amadex-admin-cards">
                <div class="amadex-card">
                    <div class="amadex-card-header">
                        <h2><?php _e('Add New Airport', 'amadex'); ?></h2>
                    </div>
                    <div class="amadex-card-body">
                        <form method="post" action="">
                            <?php wp_nonce_field('amadex_airports_nonce'); ?>
                            <input type="hidden" name="action" value="add_airport">
                            
                            <div class="amadex-form-row">
                                <div class="amadex-form-group">
                                    <label for="airport-code"><?php _e('Airport Code', 'amadex'); ?></label>
                                    <input type="text" id="airport-code" name="code" required maxlength="3" pattern="[A-Z]{3}">
                                    <small class="description"><?php _e('3-letter IATA code (uppercase)', 'amadex'); ?></small>
                                </div>
                                
                                <div class="amadex-form-group">
                                    <label for="airport-name"><?php _e('Airport Name', 'amadex'); ?></label>
                                    <input type="text" id="airport-name" name="name" required>
                                </div>
                            </div>
                            
                            <div class="amadex-form-row">
                                <div class="amadex-form-group">
                                    <label for="airport-city"><?php _e('City', 'amadex'); ?></label>
                                    <input type="text" id="airport-city" name="city" required>
                                </div>
                                
                                <div class="amadex-form-group">
                                    <label for="airport-country"><?php _e('Country', 'amadex'); ?></label>
                                    <input type="text" id="airport-country" name="country" required>
                                </div>
                            </div>
                            
                            <div class="amadex-form-submit">
                                <button type="submit" class="button button-primary"><?php _e('Add Airport', 'amadex'); ?></button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="amadex-card amadex-card-full">
                    <div class="amadex-card-header">
                        <h2><?php _e('Airports', 'amadex'); ?></h2>
                    </div>
                    <div class="amadex-card-body">
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th><?php _e('Code', 'amadex'); ?></th>
                                    <th><?php _e('Name', 'amadex'); ?></th>
                                    <th><?php _e('City', 'amadex'); ?></th>
                                    <th><?php _e('Country', 'amadex'); ?></th>
                                    <th><?php _e('Actions', 'amadex'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($airports)): ?>
                                    <tr>
                                        <td colspan="5"><?php _e('No airports found.', 'amadex'); ?></td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($airports as $airport): ?>
                                        <tr>
                                            <td><?php echo esc_html($airport['code']); ?></td>
                                            <td><?php echo esc_html($airport['name']); ?></td>
                                            <td><?php echo esc_html($airport['city']); ?></td>
                                            <td><?php echo esc_html($airport['country']); ?></td>
                                            <td>
                                                <form method="post" style="display:inline;">
                                                    <?php wp_nonce_field('amadex_airports_nonce'); ?>
                                                    <input type="hidden" name="action" value="delete_airport">
                                                    <input type="hidden" name="airport_id" value="<?php echo esc_attr($airport['id']); ?>">
                                                    <button type="submit" class="button button-small" onclick="return confirm('<?php _e('Are you sure you want to delete this airport?', 'amadex'); ?>')">
                                                        <?php _e('Delete', 'amadex'); ?>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Add airport to database
     */
    private function add_airport() {
        global $wpdb;
        
        $code = strtoupper(sanitize_text_field($_POST['code']));
        $name = sanitize_text_field($_POST['name']);
        $city = sanitize_text_field($_POST['city']);
        $country = sanitize_text_field($_POST['country']);
        
        // Validate airport code
        if (!preg_match('/^[A-Z]{3}$/', $code)) {
            add_settings_error('amadex_airports', 'invalid_code', __('Airport code must be 3 uppercase letters.', 'amadex'), 'error');
            return;
        }
        
        $table_name = $wpdb->prefix . 'amadex_airports';
        
        // Check if airport code already exists
        $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE code = %s", $code));
        
        if ($exists) {
            add_settings_error('amadex_airports', 'duplicate_code', __('Airport code already exists.', 'amadex'), 'error');
            return;
        }
        
        // Insert airport
        $result = $wpdb->insert(
            $table_name,
            array(
                'code' => $code,
                'name' => $name,
                'city' => $city,
                'country' => $country
            ),
            array('%s', '%s', '%s', '%s')
        );
        
        if ($result) {
            add_settings_error('amadex_airports', 'airport_added', __('Airport added successfully.', 'amadex'), 'success');
        } else {
            add_settings_error('amadex_airports', 'airport_error', __('Error adding airport.', 'amadex'), 'error');
        }
    }
    
    /**
     * Delete airport from database
     */
    private function delete_airport() {
        global $wpdb;
        
        $airport_id = intval($_POST['airport_id']);
        
        $table_name = $wpdb->prefix . 'amadex_airports';
        
        $result = $wpdb->delete(
            $table_name,
            array('id' => $airport_id),
            array('%d')
        );
        
        if ($result) {
            add_settings_error('amadex_airports', 'airport_deleted', __('Airport deleted successfully.', 'amadex'), 'success');
        } else {
            add_settings_error('amadex_airports', 'airport_error', __('Error deleting airport.', 'amadex'), 'error');
        }
    }
}