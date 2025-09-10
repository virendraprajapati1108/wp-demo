<?php
/**
 * Shortcodes class for Amadex plugin
 *
 * @package Amadex
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Amadex Shortcodes Class
 */
class Amadex_Shortcodes {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_shortcode('amadex-flight-search', array($this, 'flight_search_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        // Register styles
        wp_register_style(
            'amadex-frontend-style',
            AMADEX_URL . 'assets/css/frontend.css',
            array(),
            AMADEX_VERSION
        );
        
        // Register jQuery UI datepicker
        wp_register_style(
            'jquery-ui-style',
            'https://code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css',
            array(),
            '1.12.1'
        );
        
        // Register scripts
        wp_register_script(
            'amadex-frontend-script',
            AMADEX_URL . 'assets/js/frontend.js',
            array('jquery', 'jquery-ui-datepicker'),
            AMADEX_VERSION,
            true
        );
        
        // Localize script with proper nonce for AJAX security
        wp_localize_script('amadex-frontend-script', 'amadex_params', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'rest_url' => rest_url('amadex/v1/'), 
            'nonce' => wp_create_nonce('amadex_frontend_nonce'),
            'loading_text' => __('Searching for flights...', 'amadex'),
            'error_message' => __('An error occurred while searching for flights. Please try again.', 'amadex'),
            'no_results_message' => __('No flights found for your search criteria. Please try different dates or locations.', 'amadex'),
        ));
    }
    
    /**
     * Flight search shortcode
     *
     * @param array $atts
     * @return string
     */
    public function flight_search_shortcode($atts) {
        // Enqueue required assets
        wp_enqueue_style('amadex-frontend-style');
        wp_enqueue_style('jquery-ui-style');
        wp_enqueue_script('amadex-frontend-script');
        
        // Get display settings
        $display_settings = get_option('amadex_display_settings', array());
        
        // Parse shortcode attributes
        $atts = shortcode_atts(array(
            'title' => isset($display_settings['search_form_title']) ? $display_settings['search_form_title'] : __('Flight Search', 'amadex'),
            'button_text' => isset($display_settings['button_text']) ? $display_settings['button_text'] : __('Search Flights', 'amadex'),
            'theme' => isset($display_settings['default_theme']) ? $display_settings['default_theme'] : 'light',
        ), $atts, 'amadex-flight-search');
        
        // Start output buffering
        ob_start();
        
        // Get template path
        $template_path = AMADEX_PATH . 'templates/flight-search-form.php';
        
        // Check if template exists
        if (file_exists($template_path)) {
            include $template_path;
        } else {
            // Fallback if template is missing
            $this->render_default_form($atts);
        }
        
        // Get buffer contents
        $output = ob_get_clean();
        
        // Add inline script to ensure proper initialization
        $output .= '<script type="text/javascript">
            jQuery(document).ready(function($) {
                // Add a small delay to ensure all elements are loaded
                setTimeout(function() {
                    // Initialize datepickers
                    $(".amadex-datepicker").datepicker({
                        dateFormat: "yy-mm-dd",
                        minDate: new Date(),
                        changeMonth: true,
                        changeYear: true
                    });
                    
                    // Manual initialization for airport search
                    $(".amadex-airport-search").each(function() {
                        $(this).on("input", function() {
                            var $input = $(this);
                            var $wrapper = $input.closest(".amadex-autocomplete-wrapper");
                            var $results = $wrapper.find(".amadex-autocomplete-results");
                            var searchTerm = $input.val().trim();
                            
                            if (searchTerm.length >= 2) {
                                // Show search results
                                $results.html("<div class=\'amadex-loading-results\'>Searching...</div>").show();
                                
                                // Make AJAX request
                                $.ajax({
                                    url: amadex_params.ajax_url,
                                    type: "POST",
                                    data: {
                                        action: "amadex_search_airports",
                                        term: searchTerm,
                                        nonce: amadex_params.nonce
                                    },
                                    success: function(response) {
                                        if (response.success && response.data) {
                                            var airports = response.data;
                                            var html = "";
                                            
                                            if (airports.length === 0) {
                                                html = "<div class=\'amadex-no-results\'>No airports found</div>";
                                            } else {
                                                for (var i = 0; i < airports.length; i++) {
                                                    html += "<div class=\'amadex-autocomplete-item\' data-code=\'" + airports[i].code + "\'>";
                                                    html += "<strong>" + airports[i].code + "</strong> - ";
                                                    html += airports[i].city + ", " + airports[i].name + " (" + airports[i].country + ")";
                                                    html += "</div>";
                                                }
                                            }
                                            
                                            $results.html(html).show();
                                            
                                            // Add click handlers to results
                                            $results.find(".amadex-autocomplete-item").on("click", function() {
                                                var code = $(this).data("code");
                                                var text = $(this).text();
                                                $input.val(text);
                                                $wrapper.find("input[type=\'hidden\']").val(code);
                                                $results.hide();
                                            });
                                        } else {
                                            $results.html("<div class=\'amadex-error-results\'>Error searching airports</div>");
                                        }
                                    },
                                    error: function() {
                                        $results.html("<div class=\'amadex-error-results\'>Error searching airports</div>");
                                    }
                                });
                            } else {
                                $results.hide();
                            }
                        });
                        
                        // Handle double-click for testing
                        $(this).on("dblclick", function() {
                            var $input = $(this);
                            var $wrapper = $input.closest(".amadex-autocomplete-wrapper");
                            var $results = $wrapper.find(".amadex-autocomplete-results");
                            
                            // Show sample results
                            var html = "";
                            html += "<div class=\'amadex-autocomplete-item\' data-code=\'JFK\'><strong>JFK</strong> - New York, John F. Kennedy International Airport (United States)</div>";
                            html += "<div class=\'amadex-autocomplete-item\' data-code=\'LAX\'><strong>LAX</strong> - Los Angeles, Los Angeles International Airport (United States)</div>";
                            html += "<div class=\'amadex-autocomplete-item\' data-code=\'LHR\'><strong>LHR</strong> - London, Heathrow Airport (United Kingdom)</div>";
                            
                            $results.html(html).show();
                            
                            // Add click handlers to results
                            $results.find(".amadex-autocomplete-item").on("click", function() {
                                var code = $(this).data("code");
                                var text = $(this).text();
                                $input.val(text);
                                $wrapper.find("input[type=\'hidden\']").val(code);
                                $results.hide();
                            });
                        });
                    });
                    
                    // Hide results when clicking outside
                    $(document).on("click", function(e) {
                        if (!$(e.target).closest(".amadex-autocomplete-wrapper").length) {
                            $(".amadex-autocomplete-results").hide();
                        }
                    });
                }, 500);
            });
        </script>';
        
        return $output;
    }
    
    /**
     * Render default search form
     *
     * @param array $atts
     */
    private function render_default_form($atts) {
        $theme_class = 'amadex-theme-' . esc_attr($atts['theme']);
        ?>
        <div class="amadex-flight-search-container <?php echo $theme_class; ?>">
            <div class="amadex-flight-search-header">
                <h3 class="amadex-flight-search-title"><?php echo esc_html($atts['title']); ?></h3>
            </div>
            
            <div class="amadex-flight-search-form-container">
                <form id="amadex-flight-search-form" class="amadex-flight-search-form">
                    <?php wp_nonce_field('amadex_frontend_nonce', 'amadex_nonce'); ?>
                    
                    <div class="amadex-form-row">
                        <div class="amadex-form-group">
                            <label for="amadex-origin-search"><?php _e('Origin', 'amadex'); ?></label>
                            <div class="amadex-autocomplete-wrapper">
                                <input type="text" id="amadex-origin-search" class="amadex-airport-search" placeholder="<?php _e('Search city or airport', 'amadex'); ?>">
                                <input type="hidden" id="amadex-origin" name="origin" required>
                                <div class="amadex-autocomplete-results"></div>
                            </div>
                            <small class="amadex-form-help"><?php _e('Search by city, airport name, or code', 'amadex'); ?></small>
                        </div>
                        
                        <div class="amadex-form-group">
                            <label for="amadex-destination-search"><?php _e('Destination', 'amadex'); ?></label>
                            <div class="amadex-autocomplete-wrapper">
                                <input type="text" id="amadex-destination-search" class="amadex-airport-search" placeholder="<?php _e('Search city or airport', 'amadex'); ?>">
                                <input type="hidden" id="amadex-destination" name="destination" required>
                                <div class="amadex-autocomplete-results"></div>
                            </div>
                            <small class="amadex-form-help"><?php _e('Search by city, airport name, or code', 'amadex'); ?></small>
                        </div>
                    </div>
                    
                    <div class="amadex-form-row">
                        <div class="amadex-form-group">
                            <label for="amadex-departure-date"><?php _e('Departure Date', 'amadex'); ?></label>
                            <input type="text" id="amadex-departure-date" name="departure_date" class="amadex-datepicker" placeholder="<?php _e('YYYY-MM-DD', 'amadex'); ?>" required>
                        </div>
                        
                        <div class="amadex-form-group">
                            <label for="amadex-return-date"><?php _e('Return Date', 'amadex'); ?></label>
                            <input type="text" id="amadex-return-date" name="return_date" class="amadex-datepicker" placeholder="<?php _e('YYYY-MM-DD (Optional)', 'amadex'); ?>">
                            <small class="amadex-form-help"><?php _e('Leave empty for one-way flights', 'amadex'); ?></small>
                        </div>
                    </div>
                    
                    <div class="amadex-form-row">
                        <div class="amadex-form-group">
                            <label for="amadex-adults"><?php _e('Passengers', 'amadex'); ?></label>
                            <input type="number" id="amadex-adults" name="adults" min="1" max="9" value="1">
                        </div>
                        
                        <div class="amadex-form-group amadex-form-submit">
                            <button type="submit" class="amadex-search-button"><?php echo esc_html($atts['button_text']); ?></button>
                        </div>
                    </div>
                </form>
            </div>
            
            <div id="amadex-loading" class="amadex-loading" style="display: none;">
                <div class="amadex-spinner"></div>
                <p><?php _e('Searching for flights...', 'amadex'); ?></p>
            </div>
            
            <div id="amadex-flight-results" class="amadex-flight-results"></div>
        </div>
        <?php
    }
}