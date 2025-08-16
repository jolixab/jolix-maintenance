<?php
/**
 * Admin interface for Jolix Maintenance Mode
 * 
 * Handles the WordPress admin settings page and form processing
 */

namespace jolixab\JolixMaintenance;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Jolix_Maintenance_Admin {
    
    private $option_name = 'jolixabmm_settings';
    private $plugin_name = 'jolix-maintenance';
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'admin_init'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    public function add_admin_menu() {
        add_options_page(
            __('Maintenance Mode', 'jolix-maintenance'),
            __('Maintenance Mode', 'jolix-maintenance'),
            'manage_options',
            'jolixabmm-maintenance',
            array($this, 'admin_page')
        );
    }
    
    public function admin_init() {
        register_setting($this->option_name, $this->option_name, array($this, 'sanitize_settings'));
        
        add_settings_section(
            'jolixabmm_main_section',
            __('Maintenance Mode Settings', 'jolix-maintenance'),
            array($this, 'section_callback'),
            $this->option_name
        );
        
        add_settings_field(
            'enabled',
            __('Enable Maintenance Mode', 'jolix-maintenance'),
            array($this, 'enabled_callback'),
            $this->option_name,
            'jolixabmm_main_section'
        );
        
        add_settings_field(
            'tailwind_cdn',
            __('Tailwind CSS CDN URL', 'jolix-maintenance'),
            array($this, 'tailwind_cdn_callback'),
            $this->option_name,
            'jolixabmm_main_section'
        );
        
        add_settings_field(
            'html_content',
            __('Maintenance Page HTML', 'jolix-maintenance'),
            array($this, 'html_content_callback'),
            $this->option_name,
            'jolixabmm_main_section'
        );
        
        add_settings_field(
            'status_code',
            __('HTTP Status Code', 'jolix-maintenance'),
            array($this, 'status_code_callback'),
            $this->option_name,
            'jolixabmm_main_section'
        );
    }
    
    public function enqueue_admin_scripts($hook) {
        if ('settings_page_jolixabmm-maintenance' !== $hook) {
            return;
        }
        
        wp_enqueue_script(
            'jolixabmm-maintenance-admin',
            plugin_dir_url(__FILE__) . '../assets/js/admin.js',
            array(),
            '1.1',
            true
        );
        
        // Pass plugin URL to JavaScript
        wp_localize_script('jolixabmm-maintenance-admin', 'jolixabmmAdmin', array(
            'pluginUrl' => plugin_dir_url(__FILE__) . '../'
        ));
        
        wp_enqueue_style(
            'jolixabmm-maintenance-admin',
            plugin_dir_url(__FILE__) . '../assets/css/admin.css',
            array(),
            '1.1'
        );
    }
    
    public function section_callback() {
        echo '<p>' . esc_html__('Configure your maintenance mode settings below. Only administrators will be able to access the site when maintenance mode is enabled.', 'jolix-maintenance') . '</p>';
    }
    
    public function enabled_callback() {
        $options = get_option($this->option_name);
        $enabled = isset($options['enabled']) ? $options['enabled'] : false;
        echo '<input type="checkbox" id="enabled" name="' . esc_attr($this->option_name) . '[enabled]" value="1" ' . checked(1, $enabled, false) . ' />';
        echo '<label for="enabled">' . esc_html__('Enable maintenance mode', 'jolix-maintenance') . '</label>';
        echo '<p class="description">' . esc_html__('When enabled, only logged-in administrators can access the site.', 'jolix-maintenance') . '</p>';
    }
    
    public function tailwind_cdn_callback() {
        $options = get_option($this->option_name);
        $tailwind_cdn = isset($options['tailwind_cdn']) ? $options['tailwind_cdn'] : 'https://cdn.tailwindcss.com';
        
        echo '<input type="url" id="tailwind_cdn" name="' . esc_attr($this->option_name) . '[tailwind_cdn]" value="' . esc_attr($tailwind_cdn) . '" style="width: 100%;" />';
        echo '<p class="description">' . esc_html__('Enter the Tailwind CSS CDN URL to use in your templates. Leave empty to not include Tailwind CSS.', 'jolix-maintenance') . '</p>';
        echo '<p class="description"><strong>' . esc_html__('Default:', 'jolix-maintenance') . '</strong> https://cdn.tailwindcss.com</p>';
    }
    
    public function html_content_callback() {
        $options = get_option($this->option_name);
        $template_loader = new Jolix_Maintenance_Templates();
        $html_content = isset($options['html_content']) ? $options['html_content'] : $template_loader->get_default_template();
        
        echo '<div class="jolixabmm-editor-wrap">';
        echo '<div class="jolixabmm-toolbar">';
        echo '<button type="button" class="button" onclick="jolixabmm_insertBasicTemplate()">' . esc_html__('Insert Basic HTML', 'jolix-maintenance') . '</button>';
        echo '<button type="button" class="button" onclick="jolixabmm_insertTailwindTemplate()">' . esc_html__('Insert Tailwind Example', 'jolix-maintenance') . '</button>';
        echo '<button type="button" class="button button-secondary" onclick="jolixabmm_clearContent()">' . esc_html__('Clear', 'jolix-maintenance') . '</button>';
        echo '</div>';
        
        echo '<textarea id="html_content" name="' . esc_attr($this->option_name) . '[html_content]" rows="25" style="width: 100%; font-family: \'Courier New\', monospace; font-size: 13px;">' . esc_textarea($html_content) . '</textarea>';
        echo '</div>';
        
        echo '<p class="description">' . esc_html__('Enter the complete HTML code for your maintenance page. This will be displayed exactly as entered.', 'jolix-maintenance') . '</p>';
        echo '<p class="description"><strong>' . esc_html__('Note:', 'jolix-maintenance') . '</strong> ' . esc_html__('Include complete HTML structure with &lt;html&gt;, &lt;head&gt;, and &lt;body&gt; tags.', 'jolix-maintenance') . '</p>';
    }
    
    public function status_code_callback() {
        $options = get_option($this->option_name);
        $status_code = isset($options['status_code']) ? $options['status_code'] : '503';
        
        echo '<select id="status_code" name="' . esc_attr($this->option_name) . '[status_code]">';
        echo '<option value="200"' . selected('200', $status_code, false) . '>200 - OK</option>';
        echo '<option value="503"' . selected('503', $status_code, false) . '>503 - Service Unavailable</option>';
        echo '<option value="302"' . selected('302', $status_code, false) . '>302 - Found (Temporary Redirect)</option>';
        echo '</select>';
        echo '<p class="description">' . esc_html__('Choose the HTTP status code to send with the maintenance page.', 'jolix-maintenance') . '</p>';
    }
    
    public function sanitize_settings($input) {
        $sanitized = array();
        
        $sanitized['enabled'] = isset($input['enabled']) ? true : false;
        $sanitized['tailwind_cdn'] = isset($input['tailwind_cdn']) ? esc_url_raw($input['tailwind_cdn']) : '';
        $sanitized['html_content'] = isset($input['html_content']) ? $input['html_content'] : '';
        $sanitized['status_code'] = isset($input['status_code']) ? sanitize_text_field($input['status_code']) : '503';
        
        return $sanitized;
    }
    
    public function admin_page() {
        $options = get_option($this->option_name);
        $enabled = isset($options['enabled']) ? $options['enabled'] : false;
        
        include plugin_dir_path(__FILE__) . '../templates/admin-page.php';
    }
}