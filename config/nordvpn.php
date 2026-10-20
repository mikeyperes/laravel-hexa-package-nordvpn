<?php

return [
    'enabled' => env('NORDVPN_PACKAGE_ENABLED', true),
    'version' => '1.7.0',

    // Hexa Core CredentialService slug and key names for the NordVPN service credentials.
    'credential_slug' => 'nordvpn',
    'credential_keys' => [
        'username' => 'service_username',
        'password' => 'service_password',
    ],

    // NordVPN SOCKS5 servers (service credentials, port 1080). The selected key is stored in Settings.
    'socks5_port' => 1080,
    'default_server' => 'atlanta',
    'servers' => [
        'atlanta' => ['label' => 'Atlanta, US', 'host' => 'atlanta.us.socks.nordhold.net'],
        'chicago' => ['label' => 'Chicago, US', 'host' => 'chicago.us.socks.nordhold.net'],
        'dallas' => ['label' => 'Dallas, US', 'host' => 'dallas.us.socks.nordhold.net'],
        'los-angeles' => ['label' => 'Los Angeles, US', 'host' => 'los-angeles.us.socks.nordhold.net'],
        'new-york' => ['label' => 'New York, US', 'host' => 'new-york.us.socks.nordhold.net'],
        'phoenix' => ['label' => 'Phoenix, US', 'host' => 'phoenix.us.socks.nordhold.net'],
        'san-francisco' => ['label' => 'San Francisco, US', 'host' => 'san-francisco.us.socks.nordhold.net'],
        'us' => ['label' => 'United States (any)', 'host' => 'us.socks.nordhold.net'],
        'amsterdam' => ['label' => 'Amsterdam, NL', 'host' => 'amsterdam.nl.socks.nordhold.net'],
    ],

    // Servers the server-side exit route (proxy package EgressRouter) rotates through, one per attempt.
    'egress_servers' => ['atlanta', 'new-york', 'dallas', 'chicago', 'los-angeles'],

    // NordVPN HTTPS proxy servers (same service credentials, port 89): a large pool of exits beside the
    // SOCKS5 servers above. The list comes from Nord's server API and is cached; attempt N uses the Nth host.
    // Keep pool_size small: every host in use takes one of the account's device slots.
    'https_proxy' => [
        'port' => 89,
        'servers_api' => 'https://api.nordvpn.com/v1/servers/recommendations',
        'country_id' => 228,
        'pool_size' => 3,
        'cache_hours' => 6,
    ],

    // Exit-address lookup used by the connection test.
    'egress_lookup_url' => env('NORDVPN_EGRESS_LOOKUP_URL', 'https://ipinfo.io/json'),
    'test_timeout_seconds' => 20,

    'links' => [
        'service_credentials' => 'https://my.nordaccount.com/dashboard/nordvpn/manual-configuration/',
    ],
];
