<?php

namespace hexa_package_nordvpn\Providers;

use hexa_package_nordvpn\Services\NordVpnService;
use Illuminate\Support\ServiceProvider;

class NordVpnServiceProvider extends ServiceProvider
{
    /**
     * Register config and the service.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/nordvpn.php', 'nordvpn');
        $this->app->singleton(NordVpnService::class);
    }

    /**
     * Register routes, views, and the Hexa Core package card with its settings page.
     */
    public function boot(): void
    {
        if (! config('nordvpn.enabled', true)) {
            return;
        }

        $this->loadRoutesFrom(__DIR__.'/../../routes/nordvpn.php');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'nordvpn');

        $icon = 'M12 3l7 4v5c0 4.5-3 8-7 9-4-1-7-4.5-7-9V7l7-4z';
        $registry = app(\hexa_core\Services\PackageRegistryService::class);
        $registry->registerSidebarSettingsLink('NordVPN', 'nordvpn.settings', 60);
        $registry->registerPackage('nordvpn', 'hexawebsystems/laravel-hexa-package-nordvpn', [
            'title' => 'NordVPN',
            'color' => 'blue',
            'icon' => $icon,
            'description' => 'NordVPN SOCKS5 route for Browser Worker sessions: service credentials, server and connection test.',
            'settingsRoute' => 'nordvpn.settings',
            'settingsShellClass' => 'max-w-3xl',
            'docsSlug' => 'nordvpn',
            'instructions' => [
                'Open NordVPN manual setup in your Nord account and choose Set up NordVPN manually.',
                'Open the Service credentials tab (Nord may email a verification code first).',
                'Save the service username and password below. They are not your Nord login.',
                'Choose a SOCKS5 server and run Test connection to see the exit address.',
                'In the Browser Console, a browser bound to NordVPN shows a NordVPN route button.',
            ],
            'apiLinks' => [
                ['label' => 'Service credentials', 'url' => (string) config('nordvpn.links.service_credentials')],
            ],
        ]);

        if (class_exists(\hexa_core\Services\DocumentationService::class)) {
            app(\hexa_core\Services\DocumentationService::class)->register('nordvpn', 'NordVPN', 'hexawebsystems/laravel-hexa-package-nordvpn', [
                ['title' => 'Overview', 'content' => '<p>NordVPN SOCKS5 servers as a Browser Worker protected route. Credentials live in Hexa Core CredentialService.</p>'],
                ['title' => 'Usage', 'content' => '<p><code>NordVpnService::proxyProfile()</code> returns the proxy profile the Browser Worker proxy writer accepts; <code>test()</code> reports the exit address.</p>'],
            ]);
        }
    }
}
