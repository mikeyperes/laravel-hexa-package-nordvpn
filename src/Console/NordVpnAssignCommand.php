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
        {--new-ip : Drop the session\'s pinned NordVPN machine and pin a fresh one (a different exit IP)}
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
            // Chooses, tests and pins the server; a failed server leaves the session on its previous one.
            $prepared = $nordvpn->prepareBrowser($profile, $key, (bool) $this->option('allow-shared'), (bool) $this->option('new-ip'));
            if (! $prepared['success']) {
                return $this->finish(false, ['message' => $prepared['message']]);
            }
            $previous = $prepared['data']['previous_server'];
            $restore = fn () => $nordvpn->restoreBrowser($profile, $previous, (string) $prepared['data']['previous_node']);
            $written = $writer->write($nordvpn->proxyProfile($profile), $profile);
            if (($written['success'] ?? false) !== true) {
                $restore();

                return $this->finish(false, ['message' => 'The Browser Worker rejected the NordVPN route: '.($written['message'] ?? 'unknown error').'.']);
            }
            // CRITICAL — see browser-worker BUGLOG.md BW-2026-09-27-01: reapply restarts a session already on its
            // protected route, so Chrome leaves through the new server instead of the old one.
            $switched = $routes->switch($profile, 'protected', true);
            if (($switched['success'] ?? false) !== true) {
                $restore();
                $writer->write($nordvpn->proxyProfile($profile), $profile);

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
            'node' => $nordvpn->nodeFor($profile),
            'ip' => $data['ip'] ?? null,
            'place' => implode(', ', array_filter([$data['city'] ?? '', $data['region'] ?? '', $data['country'] ?? ''])),
            'network' => $data['network'] ?? '',
            'checked' => $checked,
            'check_url' => Route::has('browser-console.sessions.ip-check') ? route('browser-console.sessions.ip-check', ['profile' => $profile]) : '',
        ];
        $payload['flag'] = $onNord
            ? '🛡️ NordVPN '.($key !== '' ? 'assigned' : 'in use').' — session '.$profile.' · server '.$payload['server'].' ('.$payload['host'].($payload['node'] !== '' ? ' → machine '.$payload['node'] : '').', key '.$payload['server_key'].') · exit '.$payload['ip'].' · '.$payload['place'].($payload['network'] !== '' ? ' · '.$payload['network'] : '').' · checked '.$checked
            : '⚠️ Not on NordVPN — session '.$profile.' · route '.($payload['route_mode'] ?? 'unknown').($payload['ip'] ? ' · exit '.$payload['ip'].' · '.$payload['place'].' · '.$payload['network'] : '');

        return $this->finish(($egress['success'] ?? false) === true && ($key === '' || $onNord), $payload + ['message' => (string) ($egress['message'] ?? '')]);
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
