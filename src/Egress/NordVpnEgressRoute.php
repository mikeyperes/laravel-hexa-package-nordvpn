<?php

namespace hexa_package_nordvpn\Egress;

use hexa_package_nordvpn\Services\NordVpnService;
use hexa_package_proxy\Egress\CountedEgressRoute;

/**
 * NordVPN's SOCKS5 servers as a server-side exit for the proxy package's
 * EgressRouter. Each attempt uses the next server in `nordvpn.egress_servers`,
 * so a retry exits from a different IP. Browser sessions keep their own pinned
 * machines; this route never touches them.
 */
final class NordVpnEgressRoute implements CountedEgressRoute
{
    public function __construct(private readonly NordVpnService $nord) {}

    public function key(): string
    {
        return 'nordvpn';
    }

    public function label(): string
    {
        return 'NordVPN';
    }

    public function available(): bool
    {
        return $this->nord->configured();
    }

    public function proxyUrl(int $attempt = 0): ?string
    {
        return $this->nord->egressProxyUrl($attempt);
    }

    public function exitCount(): int
    {
        return $this->nord->egressExitCount();
    }
}
