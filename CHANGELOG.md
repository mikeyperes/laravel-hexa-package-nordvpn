# Changelog

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
