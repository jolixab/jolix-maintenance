<?php
/**
 * Template loader for Jolix Maintenance Mode
 * 
 * Manages and loads HTML templates for maintenance pages
 */

namespace jolixab\JolixMaintenance;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Jolix_Maintenance_Templates {
    
    private $template_dir;
    
    public function __construct() {
        $this->template_dir = plugin_dir_path(__FILE__) . '../templates/';
    }
    
    public function get_default_template() {
        $template_file = $this->template_dir . 'default.html';
        
        if (file_exists($template_file)) {
            return file_get_contents($template_file);
        }
        
        // Fallback to inline template if file doesn't exist
        return $this->get_inline_default_template();
    }
    
    public function get_basic_template() {
        $template_file = $this->template_dir . 'basic.html';
        
        if (file_exists($template_file)) {
            return file_get_contents($template_file);
        }
        
        // Fallback to inline template
        return $this->get_inline_basic_template();
    }
    
    public function get_tailwind_template() {
        $template_file = $this->template_dir . 'tailwind.html';
        
        if (file_exists($template_file)) {
            return file_get_contents($template_file);
        }
        
        // Fallback to inline template
        return $this->get_inline_tailwind_template();
    }
    
    private function get_inline_default_template() {
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
    
    private function get_inline_basic_template() {
        return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Under Maintenance</title>
</head>
<body>
    <div style="text-align: center; padding: 50px; font-family: Arial, sans-serif;">
        <h1>Under Maintenance</h1>
        <p>We\'ll be back soon!</p>
    </div>
</body>
</html>';
    }
    
    private function get_inline_tailwind_template() {
        return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Under Maintenance</title>
    <!-- To use external CSS frameworks, add your links here -->
    <style>
        /* Modern utility-first styles */
        body { margin: 0; font-family: system-ui, -apple-system, sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #3b82f6, #8b5cf6); }
        .min-h-screen { min-height: 100vh; }
        .flex { display: flex; }
        .items-center { align-items: center; }
        .justify-center { justify-content: center; }
        .text-center { text-align: center; }
        .text-white { color: white; }
        .p-8 { padding: 2rem; }
        .max-w-md { max-width: 28rem; }
        .text-6xl { font-size: 4rem; }
        .mb-6 { margin-bottom: 1.5rem; }
        .text-4xl { font-size: 2.5rem; }
        .font-bold { font-weight: bold; }
        .mb-4 { margin-bottom: 1rem; }
        .text-xl { font-size: 1.25rem; }
        .opacity-90 { opacity: 0.9; }
        .glass-box { background: rgba(255,255,255,0.2); backdrop-filter: blur(8px); border-radius: 0.5rem; padding: 1rem; }
        .text-sm { font-size: 0.875rem; }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center">
    <div class="text-center text-white p-8 max-w-md">
        <div class="text-6xl mb-6">🔧</div>
        <h1 class="text-4xl font-bold mb-4">Under Maintenance</h1>
        <p class="text-xl mb-6 opacity-90">We\'re making improvements to serve you better</p>
        <div class="glass-box">
            <p class="text-sm">Expected completion: Soon</p>
        </div>
    </div>
</body>
</html>';
    }
}