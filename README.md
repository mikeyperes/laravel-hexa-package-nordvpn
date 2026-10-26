# laravel-hexa-package-nordvpn

NordVPN SOCKS5 servers as a Browser Worker protected route.

- **Settings:** `/nordvpn/settings`, registered through Hexa Core `PackageRegistryService`.
- **Credentials:** NordVPN service username and password, stored encrypted by Hexa Core
  `CredentialService` (slug `nordvpn`), edited with `<x-hexa-credential-field>`.
- **Server:** one of `config('nordvpn.servers')`, stored in Hexa Core `Setting` `nordvpn_socks5_server`.
- **Test:** `NordVpnService::test()` logs into the selected server and reports the exit address.
- **Browser route:** `NordVpnService::proxyProfile()` returns the profile shape the Browser Worker
  proxy writer accepts (`provider` `nordvpn`, `socks5://host:1080`). The Browser Console uses it for a
  browser whose route is bound to the `nordvpn` profile; the Worker relay logs into SOCKS5 because
  Chrome cannot send SOCKS5 credentials.
- **New IP:** `prepareBrowser($profile, newIp: true)` (`nordvpn:assign <profile> <server> --new-ip`, the Browser
  Console's New IP button) pins a machine other than the session's current one on the same server, found through the
  system resolver and `config('nordvpn.doh_resolvers')`, or else from machines that already passed a test there
  (Setting `nordvpn_known_nodes`), and tests only that one. When Nord offers no other machine
  it says so and the current IP stays.
