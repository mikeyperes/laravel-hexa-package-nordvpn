# Changelog

## 1.1.0 - 2026-09-24

- A browser profile can have its own server (`selectServerFor`, `serverFor`), so two sessions exit through
  different NordVPN IPs; `proxyProfile()` and `test()` take the browser profile. Others keep the selected server.

## 1.0.0 - 2026-09-24

- NordVPN SOCKS5 settings page, encrypted service credentials, server choice, connection test,
  and the proxy profile for Browser Worker routes.
