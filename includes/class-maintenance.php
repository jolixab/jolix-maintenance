<?php
/**
 * Maintenance mode logic for Jolix Maintenance Mode
 * 
 * Handles the frontend maintenance mode checking and display
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Jolix_Maintenance_Handler {
    
    private $option_name = 'jm_settings';
    
    public function __construct() {
        add_action('template_redirect', array($this, 'maintenance_mode_check'));
        add_action('wp_login', array($this, 'handle_login'), 10, 2);
        
        // Hook early to catch all requests
        add_action('wp', array($this, 'maintenance_mode_check'));
        add_action('wp_loaded', array($this, 'maintenance_mode_check'));
    }
    
    public function maintenance_mode_check() {
        // Skip if we're in admin area or doing AJAX
        if (is_admin() || wp_doing_ajax() || wp_doing_cron()) {
            return;
        }
        
        // Skip if maintenance mode is not enabled
        $options = get_option($this->option_name);
        if (!isset($options['enabled']) || !$options['enabled']) {
            return;
        }
        
        // Allow access for administrators
        if (current_user_can('manage_options')) {
            return;
        }
        
        // Skip for login/logout pages
        $request_uri = '';
        if (isset($_SERVER['REQUEST_URI'])) {
            $request_uri = sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI']));
        }
        
        if (strpos($request_uri, 'wp-login') !== false || 
            strpos($request_uri, 'wp-admin') !== false ||
            strpos($request_uri, 'login') !== false) {
            return;
        }
        
        // Display maintenance page
        $this->display_maintenance_page();
    }
    
    public function handle_login($user_login, $user) {
        // If user is admin and maintenance mode is on, redirect to admin
        $options = get_option($this->option_name);
        if (isset($options['enabled']) && $options['enabled'] && user_can($user, 'manage_options')) {
            wp_redirect(admin_url());
            exit;
        }
    }
    
    private function display_maintenance_page() {
        $options = get_option($this->option_name);
        $html_content = isset($options['html_content']) ? $options['html_content'] : '';
        $status_code = isset($options['status_code']) ? $options['status_code'] : '503';
        
        // If no custom content, load default template
        if (empty($html_content)) {
            $template_loader = new Jolix_Maintenance_Templates();
            $html_content = $template_loader->get_default_template();
        }
        
        // Set HTTP status code
        $this->set_http_headers($status_code);
        
        // Output the HTML content directly - this is intentionally not escaped 
        // as it's meant to output raw HTML for the maintenance page
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo $html_content;
        
        // Stop WordPress processing
        exit;
    }
    
    private function set_http_headers($status_code) {
        // Set HTTP status code
        if ($status_code === '503') {
            header('HTTP/1.1 503 Service Temporarily Unavailable');
            header('Status: 503 Service Temporarily Unavailable');
            header('Retry-After: 7200'); // 2 hours
        } elseif ($status_code === '302') {
            header('HTTP/1.1 302 Found');
            header('Status: 302 Found');
        } else {
            header('HTTP/1.1 200 OK');
            header('Status: 200 OK');
        }
        
        // Prevent caching
        header('Cache-Control: max-age=0, no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: Wed, 11 Jan 1984 05:00:00 GMT');
    }
}