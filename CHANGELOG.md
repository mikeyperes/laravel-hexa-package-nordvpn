# Changelog

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
