@extends('layouts.app')
@section('title', 'NordVPN Settings')
@section('header', 'NordVPN Settings')

@section('content')
<div class="max-w-3xl space-y-6">
    <section class="rounded-xl border border-blue-200 bg-blue-50 p-4 sm:p-6">
        <h2 class="text-base font-semibold text-blue-900">Setup</h2>
        <ol class="mt-2 list-decimal space-y-1 pl-5 text-sm text-blue-800">
            <li>Open <a href="{{ config('nordvpn.links.service_credentials') }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 font-medium underline">NordVPN manual setup <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg></a> and choose <strong>Set up NordVPN manually</strong>.</li>
            <li>Open the <strong>Service credentials</strong> tab. Nord may email a verification code first.</li>
            <li>Save the service username and password below. They are not your Nord login.</li>
            <li>Pick a server and run <strong>Test connection</strong>.</li>
        </ol>
        <p class="mt-2 break-words text-xs text-blue-700">Credentials are stored encrypted by Hexa Core and shown masked. Browser sessions bound to NordVPN use them through the Browser Worker's local relay.</p>
    </section>

    <x-hexa-credential-field
        :slug="config('nordvpn.credential_slug')"
        :key-name="config('nordvpn.credential_keys.username')"
        label="NordVPN service username"
        :test-url="route('nordvpn.test')"
        help="From the Service credentials tab. Test logs into the selected server with both credentials."
    />

    <x-hexa-credential-field
        :slug="config('nordvpn.credential_slug')"
        :key-name="config('nordvpn.credential_keys.password')"
        label="NordVPN service password"
        :test-url="route('nordvpn.test')"
        help="From the Service credentials tab."
    />

    <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">
        <h2 class="text-base font-semibold text-gray-900">SOCKS5 server</h2>
        <p class="mt-1 text-xs text-gray-500">Browser sessions on NordVPN leave through this server. A browser picks up a change the next time NordVPN is turned on for it.</p>
        <x-hexa-ajax-form :action="route('nordvpn.settings.save')" class="mt-4 space-y-3">
            <label for="nordvpn-server" class="block text-sm font-medium text-gray-700">Server</label>
            <select id="nordvpn-server" name="server" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                @foreach($servers as $key => $server)
                    <option value="{{ $key }}" @selected($key === $selectedServer)>{{ $server['label'] }} — {{ $server['host'] }}</option>
                @endforeach
            </select>
            <div class="flex flex-wrap items-center gap-3">
                <x-hexa-dynamic-submit-button label="Save server" loading-label="Saving..." />
                <span data-hexa-submit-status class="break-words text-sm text-gray-500"></span>
            </div>
        </x-hexa-ajax-form>
    </section>

    <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">
        <h2 class="text-base font-semibold text-gray-900">Connection test</h2>
        <p class="mt-1 text-xs text-gray-500">Logs into the selected server with the stored credentials and shows the exit address.</p>
        <x-hexa-ajax-form :action="route('nordvpn.test')" class="mt-4">
            <div class="flex flex-wrap items-center gap-3">
                <x-hexa-dynamic-submit-button label="Test connection" loading-label="Testing..." :disabled="! $configured" />
                <span data-hexa-submit-status class="break-words text-sm text-gray-500">{{ $configured ? '' : 'Save both credentials to enable the test.' }}</span>
            </div>
        </x-hexa-ajax-form>
    </section>
</div>
@endsection
