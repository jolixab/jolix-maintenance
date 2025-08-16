<?php
/**
 * Uninstall script for Jolix Maintenance Mode
 * 
 * This file is executed when the plugin is deleted via WordPress admin.
 * It cleans up all plugin data and options.
 */

namespace jolixab\JolixMaintenance;

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Remove all plugin options
delete_option('jolixabmm_settings');

// For multisite installations
if (is_multisite()) {
    global $wpdb;
    
    $blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
    $original_blog_id = get_current_blog_id();
    
    foreach ($blog_ids as $blog_id) {
        switch_to_blog($blog_id);
        delete_option('jolixabmm_settings');
    }
    
    switch_to_blog($original_blog_id);
}

// Clear any cached data
wp_cache_flush();