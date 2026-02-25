<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $company->name }}</title>
    <link rel="icon" href="{{ $company->logo_url }}" />
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Nunito', sans-serif;
        }
        
        /* Fallback content in case Vue fails to load */
        .fallback-content {
            max-width: 600px;
            margin: 100px auto;
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
    </style>

    <!-- Make company data available globally -->
    <script>
        // Make company data available to the app
        window.company = @json($company);
        window.initialCompanySlug = "{{ $company->company_slug }}";
        
        // Check if #landing-app element exists
        window.logLoadingState('#landing-app exists: ' + (document.getElementById('landing-app') ? 'yes' : 'no'));
        
        // Check if #app element exists (shouldn't be present)
        window.logLoadingState('#app exists: ' + (document.getElementById('app') ? 'yes (PROBLEM!)' : 'no (good)'));
        
        // Log all script tags currently in the page
        window.logLoadingState('Scripts in page:');
        document.querySelectorAll('script').forEach(function(script, index) {
            var src = script.src || '[inline script]';
            window.logLoadingState('Script ' + (index + 1) + ': ' + src);
        });
        
        // Log when DOM is fully loaded
        window.addEventListener('DOMContentLoaded', function() {
            window.logLoadingState('DOM content loaded');
            
            // After DOM loads, check again for elements
            window.logLoadingState('After DOM - #landing-app exists: ' + (document.getElementById('landing-app') ? 'yes' : 'no'));
            window.logLoadingState('After DOM - #app exists: ' + (document.getElementById('app') ? 'yes (PROBLEM!)' : 'no (good)'));
            
            // Check scripts again after DOM loads
            window.logLoadingState('Scripts after DOM load:');
            document.querySelectorAll('script').forEach(function(script, index) {
                var src = script.src || '[inline script]';
                window.logLoadingState('Post-DOM Script ' + (index + 1) + ': ' + src);
            });
        });
    </script>
    
    <!-- Important: We're using a completely separate Vite build for the landing page -->
    @vite(['resources/js/landing.js'])
</head>
<body class="antialiased">
    <!-- DEBUGGING: This should only appear on landing pages -->
    <div id="landing-page-marker" style="position: fixed; top: 0; left: 0; background: red; color: white; padding: 5px; z-index: 9999; font-size: 12px;">
        LANDING PAGE TEMPLATE LOADED
    </div>
    
    <!-- Root element for landing page Vue application -->
    <div id="landing-app" data-company-slug="{{ $company->slug }}"></div>
    
    <!-- Debug element to show what scripts are loading -->
    <div id="script-debug" style="display:none; position:fixed; bottom: 10px; left: 10px; background: rgba(0,0,0,0.7); color: white; padding: 10px; border-radius: 5px; font-family: monospace; max-width: 80%; z-index: 10000;">
        <strong>Scripts loaded:</strong>
        <div id="script-list"></div>
    </div>
    
    <script>
        // Debug script loading
        document.addEventListener('DOMContentLoaded', function() {
            const scriptDebug = document.getElementById('script-debug');
            const scriptList = document.getElementById('script-list');
            
            // Show script debug panel with keyboard shortcut (Ctrl+Alt+D)
            document.addEventListener('keydown', function(e) {
                if (e.ctrlKey && e.altKey && e.key === 'd') {
                    scriptDebug.style.display = scriptDebug.style.display === 'none' ? 'block' : 'none';
                }
            });
            
            // List all scripts loaded on the page
            const scripts = document.querySelectorAll('script');
            scripts.forEach(function(script) {
                const scriptItem = document.createElement('div');
                scriptItem.textContent = script.src || '[inline script]';
                scriptItem.style.fontSize = '11px';
                scriptItem.style.marginTop = '3px';
                scriptItem.style.wordBreak = 'break-all';
                scriptList.appendChild(scriptItem);
            });
        });
    </script>
    
    <!-- Fallback content in case Vue fails to load -->
    <div class="fallback-content" id="fallback" style="display:none;">
        <h2>{{ $company->name }}</h2>
        <p>Cargando la página, por favor espere...</p>
        <img src="/images/loading.svg" alt="Cargando..." style="width: 50px; height: 50px;">
        
        <div style="margin-top: 20px;">
            <p>Si la página no carga después de unos segundos, intente recargar.</p>
            <button onclick="location.reload()" style="padding: 8px 16px; background: #4299e1; color: white; border: none; border-radius: 4px; cursor: pointer;">
                Recargar página
            </button>
        </div>
    </div>
    
    <script>
        // Show fallback content after a delay if Vue hasn't loaded
        setTimeout(function() {
            if (document.getElementById('landing-app').childElementCount === 0) {
                document.getElementById('fallback').style.display = 'block';
                window.logLoadingState('Showing fallback content after timeout');
            }
        }, 3000);
        
        // Add keyboard shortcut for debugging
        document.addEventListener('keydown', function(e) {
            // Ctrl+Alt+I to show debug info
            if (e.ctrlKey && e.altKey && e.key === 'i') {
                // Create debug popup
                var debugEl = document.createElement('div');
                debugEl.style.position = 'fixed';
                debugEl.style.top = '10px';
                debugEl.style.right = '10px';
                debugEl.style.width = '80%';
                debugEl.style.maxWidth = '600px';
                debugEl.style.maxHeight = '80%';
                debugEl.style.overflow = 'auto';
                debugEl.style.backgroundColor = '#f8f9fa';
                debugEl.style.border = '1px solid #dee2e6';
                debugEl.style.borderRadius = '4px';
                debugEl.style.padding = '15px';
                debugEl.style.boxShadow = '0 0 10px rgba(0,0,0,0.2)';
                debugEl.style.zIndex = '10000';
                debugEl.style.fontFamily = 'monospace';
                debugEl.style.fontSize = '12px';
                
                // Add close button
                var closeBtn = document.createElement('button');
                closeBtn.textContent = '×';
                closeBtn.style.position = 'absolute';
                closeBtn.style.top = '5px';
                closeBtn.style.right = '5px';
                closeBtn.style.border = 'none';
                closeBtn.style.background = 'none';
                closeBtn.style.fontSize = '20px';
                closeBtn.style.cursor = 'pointer';
                closeBtn.onclick = function() { document.body.removeChild(debugEl); };
                debugEl.appendChild(closeBtn);
                
                // Add heading
                var heading = document.createElement('h3');
                heading.textContent = 'Landing Page Debug Info';
                heading.style.marginTop = '0';
                debugEl.appendChild(heading);
                
                // Add general info
                var generalInfo = document.createElement('div');
                generalInfo.innerHTML = '<strong>Page URL:</strong> ' + location.href + '<br>' +
                    '<strong>Company Slug:</strong> ' + window.initialCompanySlug + '<br>' +
                    '<strong>Script Entry:</strong> resources/js/landing.js<br>' +
                    '<strong>App Element:</strong> #landing-app' +
                    '<strong>Child Elements:</strong> ' + document.getElementById('landing-app').childElementCount;
                debugEl.appendChild(generalInfo);
                
                // Add log entries
                var logHeader = document.createElement('h4');
                logHeader.textContent = 'Loading Log';
                debugEl.appendChild(logHeader);
                
                var logEntries = document.createElement('div');
                logEntries.style.maxHeight = '300px';
                logEntries.style.overflowY = 'auto';
                logEntries.style.border = '1px solid #dee2e6';
                logEntries.style.padding = '5px';
                
                if (window.loadingLog && window.loadingLog.length) {
                    window.loadingLog.forEach(function(entry) {
                        var entryEl = document.createElement('div');
                        entryEl.textContent = entry.time + ': ' + entry.message;
                        entryEl.style.borderBottom = '1px solid #eee';
                        entryEl.style.paddingBottom = '3px';
                        entryEl.style.marginBottom = '3px';
                        logEntries.appendChild(entryEl);
                    });
                } else {
                    logEntries.textContent = 'No log entries found';
                }
                
                debugEl.appendChild(logEntries);
                
                // Add script tags info
                var scriptsHeader = document.createElement('h4');
                scriptsHeader.textContent = 'Script Tags';
                debugEl.appendChild(scriptsHeader);
                
                var scriptsInfo = document.createElement('div');
                scriptsInfo.style.maxHeight = '200px';
                scriptsInfo.style.overflowY = 'auto';
                
                var scripts = document.querySelectorAll('script');
                scripts.forEach(function(script, index) {
                    var scriptEl = document.createElement('div');
                    scriptEl.textContent = (index + 1) + '. ' + (script.src || '[inline script]');
                    scriptEl.style.fontSize = '10px';
                    scriptEl.style.wordBreak = 'break-all';
                    scriptEl.style.marginBottom = '3px';
                    scriptsInfo.appendChild(scriptEl);
                });
                
                debugEl.appendChild(scriptsInfo);
                
                // Add to body
                document.body.appendChild(debugEl);
            }
        });
    </script>
</body>
</html>
