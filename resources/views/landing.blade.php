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
    </script>
    
    <!-- Important: We're using a completely separate Vite build for the landing page -->
    @vite(['resources/js/landing.js'])
</head>
<body class="antialiased">
    <!-- Root element for landing page Vue application -->
    <div id="landing-app" data-company-slug="{{ $company->company_slug }}"></div>
    
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
            }
        }, 3000);
    </script>
</body>
</html>
