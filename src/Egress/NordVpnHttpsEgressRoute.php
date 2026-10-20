<?php

namespace hexa_package_nordvpn\Egress;

use hexa_package_nordvpn\Services\NordVpnService;
use hexa_package_proxy\Egress\EgressRoute;

/**
 * NordVPN's HTTPS proxy servers (port 89) as a second server-side exit for the proxy package's EgressRouter.
 * A much larger IP pool than the SOCKS5 servers; each attempt uses the next host of the cached pool.
 */
final class NordVpnHttpsEgressRoute implements EgressRoute
{
    public function __construct(private readonly NordVpnService $nord) {}

    public function key(): string
    {
        return 'nordvpn-https';
    }

    public function label(): string
    {
        return 'NordVPN HTTPS proxy';
    }

    public function available(): bool
    {
        return $this->nord->configured();
    }

    public function proxyUrl(int $attempt = 0): ?string
    {
        return $this->nord->httpsProxyUrl($attempt);
    }
}
