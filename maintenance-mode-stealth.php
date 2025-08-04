<?php
/**
 * Plugin Name: Maintenance Mode Stealth
 * Plugin URI: https://jolix.se/en/maintenance-model-stealth-wp-plugin/
 * Description: Simple maintenance mode with custom HTML display. Blocks all access except for logged-in administrators.
 * Version: 1.0.0
 * Author: Fredrik Gustavsson, Jolix AB
 * License: GPLv3
 * Text Domain: maintenance-mode-stealth
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.8
 * Requires PHP: 7.4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class MaintenanceModeSealth {
    
    private $plugin_name = 'maintenance-mode-stealth';
    private $option_name = 'mms_settings';
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'admin_init'));
        add_action('template_redirect', array($this, 'maintenance_mode_check'));
        add_action('wp_login', array($this, 'handle_login'), 10, 2);
        
        // Hook early to catch all requests
        add_action('wp', array($this, 'maintenance_mode_check'));
        add_action('wp_loaded', array($this, 'maintenance_mode_check'));
    }
    
    public function init() {
        // load_plugin_textdomain is no longer needed for WordPress.org hosted plugins
        // WordPress automatically loads translations since version 4.6
    }
    
    public function add_admin_menu() {
        add_options_page(
            __('Maintenance Mode', 'maintenance-mode-stealth'),
            __('Maintenance Mode', 'maintenance-mode-stealth'),
            'manage_options',
            'maintenance-mode-stealth',
            array($this, 'admin_page')
        );
    }
    
    public function admin_init() {
        register_setting($this->option_name, $this->option_name, array($this, 'sanitize_settings'));
        
        add_settings_section(
            'mms_main_section',
            __('Maintenance Mode Settings', 'maintenance-mode-stealth'),
            array($this, 'section_callback'),
            $this->option_name
        );
        
        add_settings_field(
            'enabled',
            __('Enable Maintenance Mode', 'maintenance-mode-stealth'),
            array($this, 'enabled_callback'),
            $this->option_name,
            'mms_main_section'
        );
        
        add_settings_field(
            'tailwind_cdn',
            __('Tailwind CSS CDN URL', 'maintenance-mode-stealth'),
            array($this, 'tailwind_cdn_callback'),
            $this->option_name,
            'mms_main_section'
        );
        
        add_settings_field(
            'html_content',
            __('Maintenance Page HTML', 'maintenance-mode-stealth'),
            array($this, 'html_content_callback'),
            $this->option_name,
            'mms_main_section'
        );
        
        add_settings_field(
            'status_code',
            __('HTTP Status Code', 'maintenance-mode-stealth'),
            array($this, 'status_code_callback'),
            $this->option_name,
            'mms_main_section'
        );
    }
    
    public function section_callback() {
        echo '<p>' . esc_html__('Configure your maintenance mode settings below. Only administrators will be able to access the site when maintenance mode is enabled.', 'maintenance-mode-stealth') . '</p>';
    }
    
    public function enabled_callback() {
        $options = get_option($this->option_name);
        $enabled = isset($options['enabled']) ? $options['enabled'] : false;
        echo '<input type="checkbox" id="enabled" name="' . esc_attr($this->option_name) . '[enabled]" value="1" ' . checked(1, $enabled, false) . ' />';
        echo '<label for="enabled">' . esc_html__('Enable maintenance mode', 'maintenance-mode-stealth') . '</label>';
        echo '<p class="description">' . esc_html__('When enabled, only logged-in administrators can access the site.', 'maintenance-mode-stealth') . '</p>';
    }
    
    public function tailwind_cdn_callback() {
        $options = get_option($this->option_name);
        $tailwind_cdn = isset($options['tailwind_cdn']) ? $options['tailwind_cdn'] : 'https://cdn.tailwindcss.com';
        
        echo '<input type="url" id="tailwind_cdn" name="' . esc_attr($this->option_name) . '[tailwind_cdn]" value="' . esc_attr($tailwind_cdn) . '" style="width: 100%;" />';
        echo '<p class="description">' . esc_html__('Enter the Tailwind CSS CDN URL to use in your templates. Leave empty to not include Tailwind CSS.', 'maintenance-mode-stealth') . '</p>';
        echo '<p class="description"><strong>' . esc_html__('Default:', 'maintenance-mode-stealth') . '</strong> https://cdn.tailwindcss.com</p>';
    }
    
    public function html_content_callback() {
        $options = get_option($this->option_name);
        $html_content = isset($options['html_content']) ? $options['html_content'] : $this->get_default_html();
        
        echo '<div class="mms-editor-wrap">';
        echo '<div class="mms-toolbar">';
        echo '<button type="button" class="button" onclick="insertBasicTemplate()">' . esc_html__('Insert Basic HTML', 'maintenance-mode-stealth') . '</button>';
        echo '<button type="button" class="button" onclick="insertTailwindTemplate()">' . esc_html__('Insert Tailwind Example', 'maintenance-mode-stealth') . '</button>';
        echo '<button type="button" class="button button-secondary" onclick="clearContent()">' . esc_html__('Clear', 'maintenance-mode-stealth') . '</button>';
        echo '</div>';
        
        echo '<textarea id="html_content" name="' . esc_attr($this->option_name) . '[html_content]" rows="25" style="width: 100%; font-family: \'Courier New\', monospace; font-size: 13px;">' . esc_textarea($html_content) . '</textarea>';
        echo '</div>';
        
        echo '<p class="description">' . esc_html__('Enter the complete HTML code for your maintenance page. This will be displayed exactly as entered.', 'maintenance-mode-stealth') . '</p>';
        echo '<p class="description"><strong>' . esc_html__('Note:', 'maintenance-mode-stealth') . '</strong> ' . esc_html__('Include complete HTML structure with &lt;html&gt;, &lt;head&gt;, and &lt;body&gt; tags.', 'maintenance-mode-stealth') . '</p>';
    }
    
    public function status_code_callback() {
        $options = get_option($this->option_name);
        $status_code = isset($options['status_code']) ? $options['status_code'] : '503';
        
        echo '<select id="status_code" name="' . esc_attr($this->option_name) . '[status_code]">';
        echo '<option value="200"' . selected('200', $status_code, false) . '>200 - OK</option>';
        echo '<option value="503"' . selected('503', $status_code, false) . '>503 - Service Unavailable</option>';
        echo '<option value="302"' . selected('302', $status_code, false) . '>302 - Found (Temporary Redirect)</option>';
        echo '</select>';
        echo '<p class="description">' . esc_html__('Choose the HTTP status code to send with the maintenance page.', 'maintenance-mode-stealth') . '</p>';
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
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Maintenance Mode Stealth', 'maintenance-mode-stealth'); ?></h1>
            
            <?php if ($enabled): ?>
                <div class="notice notice-warning">
                    <p><strong><?php esc_html_e('WARNING:', 'maintenance-mode-stealth'); ?></strong> <?php esc_html_e('Maintenance mode is currently ACTIVE. Your site is not accessible to visitors.', 'maintenance-mode-stealth'); ?></p>
                </div>
            <?php endif; ?>
            
            <form method="post" action="options.php">
                <?php
                settings_fields($this->option_name);
                do_settings_sections($this->option_name);
                submit_button();
                ?>
            </form>
        </div>
        
        <style>
        .mms-editor-wrap {
            margin: 10px 0;
        }
        
        .mms-toolbar {
            margin-bottom: 10px;
            padding: 10px;
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .mms-toolbar .button {
            margin-right: 10px;
        }
        
        #html_content {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
        }
        </style>
        
        <script>
        function insertBasicTemplate() {
            var template = '<!DOCTYPE html>\n' +
                '<html lang="en">\n' +
                '<head>\n' +
                '    <meta charset="UTF-8">\n' +
                '    <meta name="viewport" content="width=device-width, initial-scale=1.0">\n' +
                '    <title>Site Under Maintenance</title>\n' +
                '</head>\n' +
                '<body>\n' +
                '    <div style="text-align: center; padding: 50px; font-family: Arial, sans-serif;">\n' +
                '        <h1>Under Maintenance</h1>\n' +
                '        <p>We\'ll be back soon!</p>\n' +
                '    </div>\n' +
                '</body>\n' +
                '</html>';
            document.getElementById('html_content').value = template;
        }
        
        function insertTailwindTemplate() {
            var template = '<!DOCTYPE html>\n' +
                '<html lang="en">\n' +
                '<head>\n' +
                '    <meta charset="UTF-8">\n' +
                '    <meta name="viewport" content="width=device-width, initial-scale=1.0">\n' +
                '    <title>Site Under Maintenance</title>\n' +
                '    <!-- To use external CSS frameworks, add your links here -->\n' +
                '    <style>\n' +
                '        /* Modern utility-first styles */\n' +
                '        body { margin: 0; font-family: system-ui, -apple-system, sans-serif; }\n' +
                '        .gradient-bg { background: linear-gradient(135deg, #3b82f6, #8b5cf6); }\n' +
                '        .min-h-screen { min-height: 100vh; }\n' +
                '        .flex { display: flex; }\n' +
                '        .items-center { align-items: center; }\n' +
                '        .justify-center { justify-content: center; }\n' +
                '        .text-center { text-align: center; }\n' +
                '        .text-white { color: white; }\n' +
                '        .p-8 { padding: 2rem; }\n' +
                '        .max-w-md { max-width: 28rem; }\n' +
                '        .text-6xl { font-size: 4rem; }\n' +
                '        .mb-6 { margin-bottom: 1.5rem; }\n' +
                '        .text-4xl { font-size: 2.5rem; }\n' +
                '        .font-bold { font-weight: bold; }\n' +
                '        .mb-4 { margin-bottom: 1rem; }\n' +
                '        .text-xl { font-size: 1.25rem; }\n' +
                '        .opacity-90 { opacity: 0.9; }\n' +
                '        .glass-box { background: rgba(255,255,255,0.2); backdrop-filter: blur(8px); border-radius: 0.5rem; padding: 1rem; }\n' +
                '        .text-sm { font-size: 0.875rem; }\n' +
                '    </style>\n' +
                '</head>\n' +
                '<body class="gradient-bg min-h-screen flex items-center justify-center">\n' +
                '    <div class="text-center text-white p-8 max-w-md">\n' +
                '        <div class="text-6xl mb-6">🔧</div>\n' +
                '        <h1 class="text-4xl font-bold mb-4">Under Maintenance</h1>\n' +
                '        <p class="text-xl mb-6 opacity-90">We\'re making improvements to serve you better</p>\n' +
                '        <div class="glass-box">\n' +
                '            <p class="text-sm">Expected completion: Soon</p>\n' +
                '        </div>\n' +
                '    </div>\n' +
                '</body>\n' +
                '</html>';
            document.getElementById('html_content').value = template;
        }
        
        function clearContent() {
            if (confirm('Are you sure you want to clear all content?')) {
                document.getElementById('html_content').value = '';
            }
        }
        </script>
        <?php
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
        $html_content = isset($options['html_content']) ? $options['html_content'] : $this->get_default_html();
        $status_code = isset($options['status_code']) ? $options['status_code'] : '503';
        
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
        
        // Output the HTML content directly - this is intentionally not escaped 
        // as it's meant to output raw HTML for the maintenance page
        echo wp_kses_post($html_content);
        
        // Stop WordPress processing
        exit;
    }
    
    private function get_default_html() {
        return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Under Maintenance</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            color: white;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            text-align: center;
        }
        .container {
            max-width: 28rem;
            padding: 2rem;
        }
        .icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
        }
        h1 {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }
        p {
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
            opacity: 0.9;
        }
        .completion-box {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(8px);
            border-radius: 0.5rem;
            padding: 1rem;
        }
        .completion-text {
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🔧</div>
        <h1>Under Maintenance</h1>
        <p>We\'re making improvements to serve you better</p>
        <div class="completion-box">
            <p class="completion-text">Expected completion: Soon</p>
        </div>
    </div>
</body>
</html>';
    }
}

// Initialize the plugin
new MaintenanceModeSealth();

// Activation hook
register_activation_hook(__FILE__, 'maintenance_mode_stealth_activate');
function maintenance_mode_stealth_activate() {
    // Set default options
    $default_options = array(
        'enabled' => false,
        'tailwind_cdn' => 'https://cdn.tailwindcss.com',
        'html_content' => '',
        'status_code' => '503'
    );
    
    add_option('mms_settings', $default_options);
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'maintenance_mode_stealth_deactivate');
function maintenance_mode_stealth_deactivate() {
    // Disable maintenance mode when plugin is deactivated
    $options = get_option('mms_settings');
    if ($options) {
        $options['enabled'] = false;
        update_option('mms_settings', $options);
    }
}

// Uninstall hook
register_uninstall_hook(__FILE__, 'maintenance_mode_stealth_uninstall');
function maintenance_mode_stealth_uninstall() {
    // Remove all plugin options
    delete_option('mms_settings');
}
?>