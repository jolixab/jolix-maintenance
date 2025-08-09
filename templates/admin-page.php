<?php
/**
 * Admin page template for Jolix Maintenance Mode
 * 
 * This template is loaded by the admin class to display the settings page
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1><?php esc_html_e('Jolix Maintenance Mode', 'jolix-maintenance-mode'); ?></h1>
    
    <?php if ($enabled): ?>
        <div class="notice notice-warning">
            <p><strong><?php esc_html_e('WARNING:', 'jolix-maintenance-mode'); ?></strong> <?php esc_html_e('Maintenance mode is currently ACTIVE. Your site is not accessible to visitors.', 'jolix-maintenance-mode'); ?></p>
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
.jm-editor-wrap {
    margin: 10px 0;
}

.jm-toolbar {
    margin-bottom: 10px;
    padding: 10px;
    background: #f8f9fa;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.jm-toolbar .button {
    margin-right: 10px;
}

#html_content {
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 10px;
}
</style>