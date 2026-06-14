<!DOCTYPE html>
<html lang="pt-BR">
<head>
    @php
        use App\Support\PwaBranding;
        use App\Support\TatameiroBranding;

        $currentTenant = $tenant ?? null;
        $tenantSite = $currentTenant?->site;
        $academyName = $tenantSite?->academy_name ?? $currentTenant?->name ?? 'Academia';
        $pwaShortName = PwaBranding::shortName($currentTenant, $tenantSite);
        $appLogoUrl = TatameiroBranding::appLogoUrl($tenantSite, $currentTenant);
        $faviconUrl = TatameiroBranding::faviconUrl($tenantSite, $currentTenant);
        $appleTouchIconUrl = \App\Support\TenantPwaIcons::appleTouchIconHref();
        $isStudentPortal = request()->is('student', 'student/*');
        $isAdminPortal = request()->is('admin', 'admin/*', 'login');
        $themeColor = $tenantSite?->app_header_color ?? '#1b1b18';
        $appTitle = $isStudentPortal
            ? "Portal do Aluno | {$academyName}"
            : (request()->is('login') ? "Login | {$academyName}" : "Administração | {$academyName}");
        $appDescription = $isAdminPortal
            ? "Painel de gestão da {$academyName}."
            : ($isStudentPortal
                ? "Portal do aluno da {$academyName}."
                : "{$academyName} — área do aluno e administração da academia.");
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

    <meta name="description" content="{{ $appDescription }}">
    <link rel="icon" href="{{ $faviconUrl }}" type="image/png">
    @if($isAdminPortal)
        <link rel="manifest" href="{{ url('/admin/manifest.webmanifest') }}">
        <meta name="theme-color" content="{{ $themeColor }}">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="{{ $pwaShortName }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ $appleTouchIconUrl }}">
    @endif
    <title>{{ $appTitle }}</title>
    @vite('resources/js/app.js')
</head>
<body>
    @php
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
                'logo_path' => $tenantSite?->logo_path,
                'logo_url' => $appLogoUrl,
                'nav_logo_url' => $faviconUrl,
                'footer_logo_url' => $tenantSite?->footer_logo_url,
                'hero_logo_url' => $tenantSite?->hero_logo_url,
            ],
        ];
    @endphp
    <script>
        window.__APP_TENANT__ = @json($appTenant);
    </script>
    <div id="app"></div>
</body>
</html>
