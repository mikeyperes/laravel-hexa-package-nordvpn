# Changelog

## 1.9.0 - 2026-10-26

- New IP without a named server (the Browser Console button, `nordvpn:assign <profile> --new-ip`) moves the
  session to a random US city from `nordvpn.new_ip_servers`, a different city first, so each press gives a new
  place and IP. A city another session uses may be shared, but never a machine another session is pinned to, so
  no two sessions share an exit IP. Only DNS is asked per city; one machine is tested. NordVPN offers no SOCKS5
  servers in Canada. `nordvpn:assign <profile> <server> --new-ip` still keeps the named server.

## 1.8.1 - 2026-10-26

- New IP (`prepareBrowser(..., newIp: true)`, `nordvpn:assign --new-ip`) now always lands on a different NordVPN
  machine. A NordVPN host name answers with one machine for about a minute, so New IP often re-pinned the same
  machine and kept the same exit IP. It now picks a machine other than the current one from the system resolver
  and the `nordvpn.doh_resolvers` DNS-over-HTTPS resolvers, or else from the machines that already passed a test
  on that server (Setting `nordvpn_known_nodes`, `nordvpn.known_nodes_per_server`), tests only that one, and when
  none is known it says so and keeps the current IP.

## 1.8.0 - 2026-10-23

- Both NordVPN exit routes implement `CountedEgressRoute` (`exitCount()`: SOCKS5 egress servers, HTTPS pool size), so
  callers can try every NordVPN server with `exits(0)`. Needs Proxy 1.4.0.

## 1.7.0 - 2026-10-20

- `NordVpnHttpsEgressRoute` (key `nordvpn-https`) adds NordVPN's HTTPS proxy servers (port 89) as a second
  EgressRouter exit. `NordVpnService::httpsProxyUrl($attempt)` rotates a small cached pool from Nord's server API
  (`nordvpn.https_proxy`); hosts are addressed as `usNNNN.proxy.nordvpn.com` to match Nord's certificate. Tested
  2026-10-20: YouTube trusts these exits while most SOCKS5 exits get its bot check.

## 1.6.0 - 2026-10-14

- `NordVpnEgressRoute` offers NordVPN as a server-side exit to the proxy package's `EgressRouter` (the shared
  "switch exit IP" interface) when that package is installed. `NordVpnService::egressProxyUrl($attempt)` rotates
  through `nordvpn.egress_servers`, so each retry exits from another IP. Browser sessions are untouched.

## 1.5.0 - 2026-09-27

- `NordVpnService::prepareBrowser()` chooses, tests and pins a session's server (the one asked for, else its own,
  else the first server no other NordVPN session uses) and `restoreBrowser()` undoes it. `nordvpn:assign` and the
  Browser Console NordVPN switch share it, so a session without its own server never lands on another session's
  IP pool (the default Atlanta is jpn-miami's).
- `nordvpn:assign` restarts a session that is already on its protected route (Browser Worker `reapply`), so Chrome
  really moves to the new server; before, only the saved route changed (browser-worker BW-2026-09-27-01).

## 1.4.0 - 2026-09-24

- Each server name rotates across several NordVPN machines, so a session could change exit IP mid-session and use
  one connection slot per machine. `nordvpn:assign` now pins the session to the one machine that passed its test
  (fixed exit IP, shown in the flag as `→ machine <ip>`); `--new-ip` drops the pin and pins a fresh machine.
- A rejected login now says so and names the likely cause (the account's simultaneous-connection limit is full)
  instead of "check the credentials".

## 1.3.0 - 2026-09-24

- Four more NordVPN SOCKS5 servers that accept the service login: Chicago, Phoenix, San Francisco and
  Amsterdam. NordVPN's HTTPS proxies (port 89) refuse the service login (407), so SOCKS5 stays the only route.

## 1.2.1 - 2026-09-24

- `nordvpn:assign` restores the session's previous server when the new one fails its test, binding or switch.

## 1.2.0 - 2026-09-24

- `nordvpn:assign <session> [server]` gives one browser session its own NordVPN server (refusing a server another
  session uses unless `--allow-shared`), switches it onto that route and prints the NordVPN flag: server, host,
  exit IP, place, network and the Browser Console IP-check link. Without a server it shows the current one.

## 1.1.0 - 2026-09-24

- A browser profile can have its own server (`selectServerFor`, `serverFor`), so two sessions exit through
  different NordVPN IPs; `proxyProfile()` and `test()` take the browser profile. Others keep the selected server.

## 1.0.0 - 2026-09-24

- NordVPN SOCKS5 settings page, encrypted service credentials, server choice, connection test,
  and the proxy profile for Browser Worker routes.
