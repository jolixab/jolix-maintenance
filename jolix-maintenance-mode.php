<?php
/**
 * Plugin Name: Jolix Maintenance Mode
 * Plugin URI: https://jolix.se/en/jolix-maintenance-wp-plugin/
 * Description: Simple maintenance mode with custom HTML display. Blocks all access except for logged-in administrators.
 * Version: 1.1
 * Author: Fredrik Gustavsson, Jolix AB
 * License: GPLv3
 * Text Domain: jolix-maintenance
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.8
 * Requires PHP: 7.4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main plugin class
 */
class JolixMaintenance {
    
    const VERSION = '1.1';
    const PLUGIN_FILE = __FILE__;
    
    private static $instance = null;
    private $loader;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->define_constants();
        $this->load_dependencies();
        $this->init();
    }
    
    private function define_constants() {
        define('JOLIX_MAINTENANCE_VERSION', self::VERSION);
        define('JOLIX_MAINTENANCE_PLUGIN_FILE', self::PLUGIN_FILE);
        define('JOLIX_MAINTENANCE_PLUGIN_DIR', plugin_dir_path(self::PLUGIN_FILE));
        define('JOLIX_MAINTENANCE_PLUGIN_URL', plugin_dir_url(self::PLUGIN_FILE));
        define('JOLIX_MAINTENANCE_INCLUDES_DIR', JOLIX_MAINTENANCE_PLUGIN_DIR . 'includes/');
        define('JOLIX_MAINTENANCE_TEMPLATES_DIR', JOLIX_MAINTENANCE_PLUGIN_DIR . 'templates/');
        define('JOLIX_MAINTENANCE_ASSETS_URL', JOLIX_MAINTENANCE_PLUGIN_URL . 'assets/');
    }
    
    private function load_dependencies() {
        require_once JOLIX_MAINTENANCE_INCLUDES_DIR . 'class-loader.php';
        $this->loader = Jolix_Maintenance_Loader::get_instance();
    }
    
    private function init() {
        // Register activation/deactivation hooks
        register_activation_hook(self::PLUGIN_FILE, array($this, 'activate'));
        register_deactivation_hook(self::PLUGIN_FILE, array($this, 'deactivate'));
    }
    
    public function activate() {
        // Set default options
        $default_options = array(
            'enabled' => false,
            'tailwind_cdn' => 'https://cdn.tailwindcss.com',
            'html_content' => '',
            'status_code' => '503'
        );
        
        add_option('jm_settings', $default_options);
    }
    
    public function deactivate() {
        // Disable maintenance mode when plugin is deactivated
        $options = get_option('jm_settings');
        if ($options) {
            $options['enabled'] = false;
            update_option('jm_settings', $options);
        }
    }
    
    public static function uninstall() {
        // Remove all plugin options
        delete_option('jm_settings');
        
        // For multisite installations
        if (is_multisite()) {
            global $wpdb;
            
            $blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
            $original_blog_id = get_current_blog_id();
            
            foreach ($blog_ids as $blog_id) {
                switch_to_blog($blog_id);
                delete_option('jm_settings');
            }
            
            switch_to_blog($original_blog_id);
        }
        
        // Clear any cached data
        wp_cache_flush();
    }
}

// Initialize the plugin
JolixMaintenance::get_instance();

// Uninstall hook
register_uninstall_hook(__FILE__, array('JolixMaintenance', 'uninstall'));