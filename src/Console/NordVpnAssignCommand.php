<?php

namespace hexa_package_nordvpn\Console;

use hexa_package_nordvpn\Services\NordVpnService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

class NordVpnAssignCommand extends Command
{
    protected $signature = 'nordvpn:assign
        {profile : Browser Worker session (profile) ID}
        {server? : NordVPN server key; omit to show the session\'s current assignment and exit IP}
        {--allow-shared : Allow a server another NordVPN session already uses (same IP pool)}
        {--json : Print the result as JSON}';

    protected $description = 'Give one browser session its own NordVPN server, switch it onto that route, and report the NordVPN flag: server, exit IP and an IP-check link.';

    /**
     * Assign (or show) a session's NordVPN server and print the flag line.
     */
    public function handle(NordVpnService $nordvpn): int
    {
        $profile = strtolower(trim((string) $this->argument('profile')));
        $key = trim((string) $this->argument('server'));
        $writerClass = 'hexa_package_browser_worker\\Services\\BrowserProxyConfigWriter';
        $routesClass = 'hexa_package_browser_worker\\Services\\BrowserNativeRouteService';
        if (! class_exists($writerClass) || ! class_exists($routesClass)) {
            return $this->finish(false, ['message' => 'The Browser Worker package is not installed.']);
        }
        $writer = app($writerClass);
        $routes = app($routesClass);

        if ($key !== '') {
            if (! array_key_exists($key, $nordvpn->servers())) {
                return $this->finish(false, ['message' => 'Unknown NordVPN server "'.$key.'". Servers: '.implode(', ', array_keys($nordvpn->servers())).'.']);
            }
            $sharing = $this->sessionsOn($nordvpn, $writer, $key, $profile);
            if ($sharing !== [] && ! $this->option('allow-shared')) {
                return $this->finish(false, ['message' => 'Server "'.$key.'" is already used by '.implode(', ', $sharing).'; pick another so this session gets its own IP (or pass --allow-shared).']);
            }
            try {
                $nordvpn->selectServerFor($profile, $key);
            } catch (\InvalidArgumentException $exception) {
                return $this->finish(false, ['message' => $exception->getMessage()]);
            }
            $test = $nordvpn->test($profile);
            if (! $test['success']) {
                return $this->finish(false, ['message' => 'NordVPN test failed: '.$test['message']]);
            }
            $written = $writer->write($nordvpn->proxyProfile($profile), $profile);
            if (($written['success'] ?? false) !== true) {
                return $this->finish(false, ['message' => 'The Browser Worker rejected the NordVPN route: '.($written['message'] ?? 'unknown error').'.']);
            }
            $switched = $routes->switch($profile, 'protected');
            if (($switched['success'] ?? false) !== true) {
                return $this->finish(false, ['message' => 'The session could not switch to the NordVPN route: '.($switched['message'] ?? 'unknown error').'.']);
            }
        }

        $egress = $routes->egress($profile, true);
        $server = $nordvpn->servers()[$nordvpn->serverFor($profile)] ?? ['label' => '', 'host' => ''];
        $data = (array) ($egress['data'] ?? []);
        $onNord = ($data['provider'] ?? '') === 'nordvpn' && ($data['route_mode'] ?? '') === 'protected_proxy';
        $checked = isset($data['checked_at']) ? now()->parse($data['checked_at'])->setTimezone('-05:00')->format('Y-m-d H:i:s').' EST' : '';
        $payload = [
            'profile' => $profile,
            'assigned' => $key !== '',
            'on_nordvpn' => $onNord,
            'route_mode' => $data['route_mode'] ?? null,
            'server_key' => $nordvpn->serverFor($profile),
            'server' => $server['label'] ?? '',
            'host' => $server['host'] ?? '',
            'ip' => $data['ip'] ?? null,
            'place' => implode(', ', array_filter([$data['city'] ?? '', $data['region'] ?? '', $data['country'] ?? ''])),
            'network' => $data['network'] ?? '',
            'checked' => $checked,
            'check_url' => Route::has('browser-console.sessions.ip-check') ? route('browser-console.sessions.ip-check', ['profile' => $profile]) : '',
        ];
        $payload['flag'] = $onNord
            ? '🛡️ NordVPN '.($key !== '' ? 'assigned' : 'in use').' — session '.$profile.' · server '.$payload['server'].' ('.$payload['host'].', key '.$payload['server_key'].') · exit '.$payload['ip'].' · '.$payload['place'].($payload['network'] !== '' ? ' · '.$payload['network'] : '').' · checked '.$checked
            : '⚠️ Not on NordVPN — session '.$profile.' · route '.($payload['route_mode'] ?? 'unknown').($payload['ip'] ? ' · exit '.$payload['ip'].' · '.$payload['place'].' · '.$payload['network'] : '');

        return $this->finish(($egress['success'] ?? false) === true && ($key === '' || $onNord), $payload + ['message' => (string) ($egress['message'] ?? '')]);
    }

    /**
     * Other sessions bound to NordVPN that exit through this server.
     *
     * @return array<int, string>
     */
    private function sessionsOn(NordVpnService $nordvpn, object $writer, string $key, string $except): array
    {
        $sessions = [];
        foreach ((array) ($writer->status()['profiles'] ?? []) as $item) {
            $browser = (string) ($item['browser_profile'] ?? '');
            if ($browser !== '' && $browser !== $except && ($item['profile_key'] ?? '') === 'nordvpn' && $nordvpn->serverFor($browser) === $key) {
                $sessions[] = $browser;
            }
        }

        return $sessions;
    }

    /** @param array<string, mixed> $payload */
    private function finish(bool $success, array $payload): int
    {
        if ($this->option('json')) {
            $this->line(json_encode(['success' => $success] + $payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        } else {
            $this->line((string) ($payload['flag'] ?? $payload['message'] ?? ''));
            if (($payload['check_url'] ?? '') !== '') {
                $this->line('Check IP: '.$payload['check_url']);
            }
            if (! $success && isset($payload['flag']) && ($payload['message'] ?? '') !== '') {
                $this->line($payload['message']);
            }
        }

        return $success ? self::SUCCESS : self::FAILURE;
    }
}
