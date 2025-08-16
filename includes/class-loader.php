<?php
/**
 * Class loader for Jolix Maintenance Mode
 * 
 * Handles loading of all plugin classes and initialization
 */

namespace jolixab\JolixMaintenance;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Jolix_Maintenance_Loader {
    
    private static $instance = null;
    private $classes_loaded = array();
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }
    
    private function load_dependencies() {
        $includes_path = plugin_dir_path(__FILE__);
        
        // Load core classes
        $this->load_class('jolixab\\JolixMaintenance\\Jolix_Maintenance_Templates', $includes_path . 'class-templates.php');
        $this->load_class('jolixab\\JolixMaintenance\\Jolix_Maintenance_Handler', $includes_path . 'class-maintenance.php');
        
        // Load admin classes only in admin
        if (is_admin()) {
            $this->load_class('jolixab\\JolixMaintenance\\Jolix_Maintenance_Admin', $includes_path . 'class-admin.php');
        }
    }
    
    private function load_class($class_name, $file_path) {
        if (!class_exists($class_name) && file_exists($file_path)) {
            require_once $file_path;
            $this->classes_loaded[] = $class_name;
        }
    }
    
    private function init_hooks() {
        // Initialize maintenance handler
        if (class_exists('jolixab\\JolixMaintenance\\Jolix_Maintenance_Handler')) {
            new Jolix_Maintenance_Handler();
        }
        
        // Initialize admin interface only in admin
        if (is_admin() && class_exists('jolixab\\JolixMaintenance\\Jolix_Maintenance_Admin')) {
            new Jolix_Maintenance_Admin();
        }
    }
    
    public function get_loaded_classes() {
        return $this->classes_loaded;
    }
}