<!DOCTYPE html>
<html  lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>800dent</title>
		<link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
		@if($company && $company->small_light_logo_url)
		<meta name="msapplication-TileImage" href="{{ $company->small_light_logo_url }}">
		@endif
		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:300,400,600,700&display=swap">
        <link rel="stylesheet" href="{{ asset('css/pre-loader.css') }}">
        <style>
            .svg-animation {
                animation: svg-animation 3s ease-in-out infinite;
            }

            @keyframes svg-animation {
                0% {
                    opacity: 0.15;
                }
                50% {
                    opacity: 1;
                }
                100% {
                    opacity: 0.15;
                }
            }
        </style>
        @vite('resources/css/app.css')
    </head>
    <body>
        <div id="app">
            <div class="loading-app-container">
                <span>
                    <img class="h-12 svg-animation" src="{{ asset('images/loading.svg') }}">
                </span>
            </div>
        </div>

        <script>
            window.config = {
                'path': '{{ url('/') }}',
                'download_lang_csv_url': "{{ route('api.extra.langs.download') }}",
                'staff_member_sample_file': "{{ asset('images/sample_staff_members.csv') }}",
                'perPage': 10,
				'product_name': "{{ $appName }}",
				'product_version': "{{ $appVersion }}",
				'theme_mode': "{{ $themeMode }}",
				'appChecking': true,
				'app_version': "{{ $appVersion }}",
				'app_env': "{{ $appEnv }}",
            };
        </script>

        @vite('resources/js/app.js')
    </body>
</html>
