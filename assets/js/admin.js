/**
 * Admin JavaScript for Jolix Maintenance
 */

function insertBasicTemplate() {
    fetch(jolixMaintenanceAdmin.pluginUrl + 'templates/basic.html')
        .then(response => response.text())
        .then(template => {
            document.getElementById('html_content').value = template;
        })
        .catch(() => {
            // Fallback inline template
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
        });
}

function insertTailwindTemplate() {
    fetch(jolixMaintenanceAdmin.pluginUrl + 'templates/tailwind.html')
        .then(response => response.text())
        .then(template => {
            document.getElementById('html_content').value = template;
        })
        .catch(() => {
            // Fallback inline template
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
        });
}

function clearContent() {
    if (confirm('Are you sure you want to clear all content?')) {
        document.getElementById('html_content').value = '';
    }
}