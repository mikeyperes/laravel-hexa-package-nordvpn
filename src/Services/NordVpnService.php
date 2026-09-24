<?php

namespace hexa_package_nordvpn\Services;

use hexa_core\Models\Setting;
use hexa_core\Services\CredentialService;
use Illuminate\Support\Facades\Http;

/**
 * NordVPN SOCKS5 route: service credentials (Hexa Core CredentialService), the selected server,
 * a connection test, and the proxy profile the Browser Worker protected route consumes.
 */
class NordVpnService
{
    private const SERVER_SETTING = 'nordvpn_socks5_server';

    public function __construct(private CredentialService $credentials)
    {
    }

    /**
     * The configured SOCKS5 servers, keyed by server key.
     *
     * @return array<string, array{label: string, host: string}>
     */
    public function servers(): array
    {
        return (array) config('nordvpn.servers', []);
    }

    /**
     * The selected server key, falling back to the configured default.
     */
    public function selectedServer(): string
    {
        $key = (string) Setting::getValue(self::SERVER_SETTING, '');

        return array_key_exists($key, $this->servers()) ? $key : (string) config('nordvpn.default_server');
    }

    /**
     * Store the selected server key.
     *
     * @throws \InvalidArgumentException When the key is not a configured server.
     */
    public function selectServer(string $key): void
    {
        if (! array_key_exists($key, $this->servers())) {
            throw new \InvalidArgumentException('Unknown NordVPN server.');
        }
        Setting::setValue(self::SERVER_SETTING, $key, 'packages');
    }

    /**
     * Whether both service credentials are stored.
     */
    public function configured(): bool
    {
        return $this->credential('username') !== '' && $this->credential('password') !== '';
    }

    /**
     * Proxy profile in the shape the Browser Worker proxy writer accepts. Contains the credentials;
     * internal use only, never render it.
     *
     * @return array<string, string>
     */
    public function proxyProfile(): array
    {
        $server = $this->servers()[$this->selectedServer()] ?? ['label' => '', 'host' => ''];

        return [
            'key' => 'nordvpn',
            'name' => 'NordVPN - '.$server['label'],
            'provider' => 'nordvpn',
            'protocol' => 'socks5',
            'endpoint_mode' => 'manual',
            'proxy_auth_mode' => 'username_password',
            'proxy_host' => (string) $server['host'],
            'proxy_port' => (string) config('nordvpn.socks5_port', 1080),
            'proxy_username' => $this->credential('username'),
            'proxy_password' => $this->credential('password'),
        ];
    }

    /**
     * Log into the selected SOCKS5 server and report the exit address. Never returns credentials.
     *
     * @return array{success: bool, message: string, data?: array<string, string>}
     */
    public function test(): array
    {
        if (! $this->configured()) {
            return ['success' => false, 'message' => 'Save the NordVPN service username and password first.'];
        }
        $profile = $this->proxyProfile();
        $proxy = 'socks5h://'.rawurlencode($profile['proxy_username']).':'.rawurlencode($profile['proxy_password'])
            .'@'.$profile['proxy_host'].':'.$profile['proxy_port'];

        try {
            $response = Http::withOptions(['proxy' => $proxy])
                ->timeout((int) config('nordvpn.test_timeout_seconds', 20))
                ->acceptJson()
                ->get((string) config('nordvpn.egress_lookup_url'));
        } catch (\Throwable) {
            return ['success' => false, 'message' => 'NordVPN refused the login or the server did not answer. Check the service credentials and server.'];
        }
        if (! $response->successful() || ! filter_var($response->json('ip'), FILTER_VALIDATE_IP)) {
            return ['success' => false, 'message' => 'Connected, but the exit address could not be read.'];
        }
        $data = [
            'ip' => (string) $response->json('ip'),
            'place' => implode(', ', array_filter([(string) $response->json('city'), (string) $response->json('region'), (string) $response->json('country')])),
            'network' => trim((string) preg_replace('/^AS\d+\s+/', '', (string) $response->json('org'))),
        ];

        return [
            'success' => true,
            'message' => 'Connected through NordVPN. Exit '.$data['ip'].' · '.$data['place'].($data['network'] !== '' ? ' · '.$data['network'] : ''),
            'data' => $data,
        ];
    }

    /**
     * One stored service credential ('username' or 'password'), or an empty string.
     */
    private function credential(string $name): string
    {
        $key = (string) config('nordvpn.credential_keys.'.$name, '');

        return trim((string) $this->credentials->get((string) config('nordvpn.credential_slug', 'nordvpn'), $key));
    }
}
