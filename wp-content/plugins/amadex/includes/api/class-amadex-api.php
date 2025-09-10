<?php
/**
 * API class for Amadex plugin
 *
 * @package Amadex
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Amadex API Class
 */
class Amadex_API {
    
    /**
     * API settings
     *
     * @var array
     */
    private $settings;
    
    /**
     * API access token
     *
     * @var string
     */
    private $access_token;
    
    /**
     * Token expiration
     *
     * @var int
     */
    private $token_expires;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->settings = get_option('amadex_api_settings', array());
        $this->setup_ajax_handlers();
        
        // Register REST API routes
        add_action('rest_api_init', array($this, 'register_rest_routes'));
    }
    
    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        register_rest_route('amadex/v1', '/airports', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_airports'),
            'permission_callback' => '__return_true'
        ));
    }
    
    /**
     * Get airports that match the search term
     */
    public function get_airports($request) {
        $search_term = sanitize_text_field($request->get_param('term'));
        
        if (empty($search_term) || strlen($search_term) < 2) {
            return new WP_REST_Response(array(), 200);
        }
        
        // Get airports from database
        $airports = $this->search_airports($search_term);
        
        // Log for debugging
        error_log('Amadex: Searched for airports with term: ' . $search_term . '. Found: ' . count($airports));
        
        return new WP_REST_Response($airports, 200);
    }
    
    /**
     * Search airports by term
     */
    private function search_airports($term) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'amadex_airports';
        $search_term = '%' . $wpdb->esc_like($term) . '%';
        
        // Check if table exists
        if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            error_log('Amadex: Airports table does not exist. Returning default airports.');
            return $this->get_default_airports();
        }
        
        // Check if table has data
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        if ($count == 0) {
            error_log('Amadex: Airports table is empty. Returning default airports.');
            return $this->get_default_airports();
        }
        
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT code, name, city, country 
                FROM $table_name 
                WHERE code LIKE %s 
                OR name LIKE %s 
                OR city LIKE %s 
                ORDER BY 
                    CASE 
                        WHEN code = %s THEN 1 
                        WHEN city = %s THEN 2
                        WHEN code LIKE %s THEN 3
                        WHEN city LIKE %s THEN 4
                        ELSE 5 
                    END
                LIMIT 10",
                $search_term, $search_term, $search_term, $term, $term, $search_term, $search_term
            ),
            ARRAY_A
        );
        
        // If no results, return default airports
        if (empty($results)) {
            error_log('Amadex: No airports found for term: ' . $term . '. Returning default airports.');
            return $this->get_default_airports();
        }
        
        return $results;
    }
    
    /**
     * Get default airports for fallback
     */
    private function get_default_airports() {
        return array(
            array(
                'code' => 'JFK',
                'name' => 'John F. Kennedy International Airport',
                'city' => 'New York',
                'country' => 'United States'
            ),
            array(
                'code' => 'LAX',
                'name' => 'Los Angeles International Airport',
                'city' => 'Los Angeles',
                'country' => 'United States'
            ),
            array(
                'code' => 'LHR',
                'name' => 'Heathrow Airport',
                'city' => 'London',
                'country' => 'United Kingdom'
            ),
            array(
                'code' => 'CDG',
                'name' => 'Charles de Gaulle Airport',
                'city' => 'Paris',
                'country' => 'France'
            ),
            array(
                'code' => 'DXB',
                'name' => 'Dubai International Airport',
                'city' => 'Dubai',
                'country' => 'United Arab Emirates'
            )
        );
    }
    
    /**
     * Setup AJAX handlers
     */
    private function setup_ajax_handlers() {
        add_action('wp_ajax_amadex_test_connection', array($this, 'test_connection'));
        add_action('wp_ajax_amadex_search_flights', array($this, 'search_flights'));
        add_action('wp_ajax_nopriv_amadex_search_flights', array($this, 'search_flights'));
        add_action('wp_ajax_amadex_search_airports', array($this, 'ajax_search_airports'));
        add_action('wp_ajax_nopriv_amadex_search_airports', array($this, 'ajax_search_airports'));
    }
    
    /**
     * AJAX handler for airport search
     */
    public function ajax_search_airports() {
        // Check nonce
        check_ajax_referer('amadex_frontend_nonce', 'nonce');
        
        // Get search term
        $search_term = isset($_POST['term']) ? sanitize_text_field($_POST['term']) : '';
        
        if (empty($search_term) || strlen($search_term) < 2) {
            wp_send_json_error(array('message' => 'Search term too short'));
            return;
        }
        
        // Search airports
        $airports = $this->search_airports($search_term);
        
        // Log for debugging
        error_log('Amadex AJAX: Searched for airports with term: ' . $search_term . '. Found: ' . count($airports));
        
        // Return results
        wp_send_json_success($airports);
    }
    
    /**
     * Get API base URL based on environment
     *
     * @return string
     */
    private function get_api_base_url() {
        $environment = isset($this->settings['environment']) ? $this->settings['environment'] : 'test';
        return $environment === 'production' 
            ? 'https://api.amadeus.com' 
            : 'https://test.api.amadeus.com';
    }
    
    /**
     * Get access token
     *
     * @return string|WP_Error
     */
    public function get_access_token() {
        // Check if we already have a valid token
        if (isset($this->access_token) && isset($this->token_expires) && $this->token_expires > time()) {
            return $this->access_token;
        }
        
        // Get API credentials
        $api_key = isset($this->settings['api_key']) ? $this->settings['api_key'] : '';
        $api_secret = isset($this->settings['api_secret']) ? $this->settings['api_secret'] : '';
        
        // Check if credentials are set
        if (empty($api_key) || empty($api_secret)) {
            return new WP_Error('missing_credentials', __('API key and secret are required', 'amadex'));
        }
        
        // API endpoint for authentication
        $url = $this->get_api_base_url() . '/v1/security/oauth2/token';
        
        // Prepare request
        $args = array(
            'method' => 'POST',
            'timeout' => 15,
            'redirection' => 5,
            'httpversion' => '1.1',
            'headers' => array(
                'Content-Type' => 'application/x-www-form-urlencoded',
            ),
            'body' => array(
                'grant_type' => 'client_credentials',
                'client_id' => $api_key,
                'client_secret' => $api_secret,
            )
        );
        
        // Make request
        $response = wp_remote_post($url, $args);
        
        // Check if request was successful
        if (is_wp_error($response)) {
            $this->log_error('Authentication error: ' . $response->get_error_message());
            return $response;
        }
        
        // Get response code
        $response_code = wp_remote_retrieve_response_code($response);
        
        // Check response code
        if ($response_code !== 200) {
            $error_message = wp_remote_retrieve_response_message($response);
            $this->log_error('Authentication error: ' . $error_message . ' (Code: ' . $response_code . ')');
            return new WP_Error('authentication_error', $error_message);
        }
        
        // Decode response body
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        // Check if we got a valid response
        if (!isset($data['access_token'])) {
            $this->log_error('Authentication error: Invalid response');
            return new WP_Error('invalid_response', __('Invalid authentication response', 'amadex'));
        }
        
        // Store token and expiration
        $this->access_token = $data['access_token'];
        $this->token_expires = time() + $data['expires_in'];
        
        return $this->access_token;
    }
    
    /**
     * Test API connection
     */
    public function test_connection() {
        // Check nonce
        check_ajax_referer('amadex_admin_nonce', 'nonce');
        
        // Check user permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array(
                'message' => __('You do not have permission to perform this action', 'amadex')
            ));
        }
        
        // Get access token
        $token = $this->get_access_token();
        
        if (is_wp_error($token)) {
            wp_send_json_error(array(
                'message' => $token->get_error_message()
            ));
        }
        
        wp_send_json_success(array(
            'message' => __('Successfully connected to the Amadeus API', 'amadex')
        ));
    }
    
    /**
     * Search flights
     */
    public function search_flights() {
        // Check nonce
        check_ajax_referer('amadex_frontend_nonce', 'nonce');
        
        // Get parameters
        $origin = isset($_POST['origin']) ? sanitize_text_field($_POST['origin']) : '';
        $destination = isset($_POST['destination']) ? sanitize_text_field($_POST['destination']) : '';
        $departure_date = isset($_POST['departure_date']) ? sanitize_text_field($_POST['departure_date']) : '';
        $return_date = isset($_POST['return_date']) ? sanitize_text_field($_POST['return_date']) : '';
        $adults = isset($_POST['adults']) ? intval($_POST['adults']) : 1;
        
        // Validate required parameters
        if (empty($origin) || empty($destination) || empty($departure_date)) {
            wp_send_json_error(array(
                'message' => __('Origin, destination, and departure date are required', 'amadex')
            ));
        }
        
        // Check if we need to get data from cache
        $cache_key = 'amadex_flight_' . md5($origin . $destination . $departure_date . $return_date . $adults);
        $cache_duration = $this->get_cache_duration();
        
        if ($cache_duration > 0) {
            $cached_data = get_transient($cache_key);
            if ($cached_data !== false) {
                wp_send_json_success($cached_data);
            }
        }
        
        // Get access token
        $token = $this->get_access_token();
        
        if (is_wp_error($token)) {
            wp_send_json_error(array(
                'message' => $token->get_error_message()
            ));
        }
        
        // Build API endpoint
        $endpoint = $this->get_api_base_url() . '/v2/shopping/flight-offers';
        
        // Build query parameters
        $query_params = array(
            'originLocationCode' => $origin,
            'destinationLocationCode' => $destination,
            'departureDate' => $departure_date,
            'adults' => $adults,
        );
        
        // Add return date if provided
        if (!empty($return_date)) {
            $query_params['returnDate'] = $return_date;
        }
        
        // Build URL with query parameters
        $url = add_query_arg($query_params, $endpoint);
        
        // Get advanced settings
        $advanced_settings = get_option('amadex_advanced_settings', array());
        $timeout = isset($advanced_settings['timeout']) ? intval($advanced_settings['timeout']) : 10;
        
        // Prepare request
        $args = array(
            'method' => 'GET',
            'timeout' => $timeout,
            'redirection' => 5,
            'httpversion' => '1.1',
            'headers' => array(
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ),
        );
        
        // Make request
        $response = wp_remote_get($url, $args);
        
        // Check if request was successful
        if (is_wp_error($response)) {
            $this->log_error('Flight search error: ' . $response->get_error_message());
            wp_send_json_error(array(
                'message' => $response->get_error_message()
            ));
        }
        
        // Get response code
        $response_code = wp_remote_retrieve_response_code($response);
        
        // Check response code
        if ($response_code !== 200) {
            $error_message = wp_remote_retrieve_response_message($response);
            $this->log_error('Flight search error: ' . $error_message . ' (Code: ' . $response_code . ')');
            wp_send_json_error(array(
                'message' => $error_message
            ));
        }
        
        // Decode response body
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        // Store in cache if caching is enabled
        if ($cache_duration > 0) {
            set_transient($cache_key, $data, $cache_duration * MINUTE_IN_SECONDS);
        }
        
        wp_send_json_success($data);
    }
    
    /**
     * Get cache duration
     *
     * @return int
     */
    private function get_cache_duration() {
        $general_settings = get_option('amadex_general_settings', array());
        return isset($general_settings['cache_duration']) ? intval($general_settings['cache_duration']) : 60;
    }
    
    /**
     * Log error message
     *
     * @param string $message
     */
    private function log_error($message) {
        $advanced_settings = get_option('amadex_advanced_settings', array());
        $error_logging = isset($advanced_settings['error_logging']) ? $advanced_settings['error_logging'] : 1;
        
        if ($error_logging) {
            error_log('Amadex Plugin: ' . $message);
        }
    }
}