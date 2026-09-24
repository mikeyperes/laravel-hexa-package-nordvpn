<?php

namespace hexa_package_nordvpn\Http\Controllers;

use hexa_package_nordvpn\Services\NordVpnService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class NordVpnController extends Controller
{
    public function __construct(private NordVpnService $nordvpn)
    {
    }

    /**
     * NordVPN settings page.
     */
    public function settings(): View
    {
        return view('nordvpn::settings.index', [
            'servers' => $this->nordvpn->servers(),
            'selectedServer' => $this->nordvpn->selectedServer(),
            'configured' => $this->nordvpn->configured(),
        ]);
    }

    /**
     * Save the selected SOCKS5 server.
     */
    public function saveSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'server' => ['required', 'string', 'in:'.implode(',', array_keys($this->nordvpn->servers()))],
        ]);
        $this->nordvpn->selectServer($validated['server']);

        return response()->json(['success' => true, 'message' => 'Server saved.']);
    }

    /**
     * Test the stored credentials against the selected server.
     */
    public function test(): JsonResponse
    {
        $result = $this->nordvpn->test();

        return response()->json($result, $result['success'] ? 200 : 422);
    }
}
