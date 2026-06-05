<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

    <meta name="description" content="B-Triad Jiu-Jitsu — área do aluno e administração da academia.">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <title>Academia</title>
    @vite('resources/js/app.js')
</head>
<body>
    @php
        $currentTenant = $tenant ?? null;
        $tenantSite = $currentTenant?->site;
        $tenantLogoPath = $tenantSite?->logo_path;
        $appTenant = [
            'name' => $currentTenant?->name,
            'slug' => $currentTenant?->slug,
            'site' => [
                'academy_name' => $tenantSite?->academy_name,
                'primary_color' => $tenantSite?->primary_color,
                'header_color' => $tenantSite?->header_color,
                'background_color' => $tenantSite?->background_color,
                'trial_button_color' => $tenantSite?->trial_button_color,
                'portal_button_color' => $tenantSite?->portal_button_color,
                'app_primary_color' => $tenantSite?->app_primary_color,
                'app_header_color' => $tenantSite?->app_header_color,
                'app_background_color' => $tenantSite?->app_background_color,
                'app_login_background_color' => $tenantSite?->app_login_background_color,
                'logo_path' => $tenantLogoPath,
                'logo_url' => $tenantSite?->logo_url,
            ],
        ];
    @endphp
    <script>
        window.__APP_TENANT__ = @json($appTenant);
    </script>
    <div id="app"></div>
</body>
</html>
