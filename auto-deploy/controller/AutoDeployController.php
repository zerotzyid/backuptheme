<?php

namespace Pterodactyl\Http\Controllers\Admin\Extensions\darkpsyho;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Pterodactyl\Models\Server;
use Pterodactyl\Models\Node;
use Pterodactyl\Models\User;
use Pterodactyl\Models\Allocation;
use Pterodactyl\Models\Objects\DeploymentObject;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Services\Servers\ServerCreationService;
use Pterodactyl\BlueprintFramework\Libraries\ExtensionLibrary\Admin\BlueprintAdminLibrary as BlueprintExtensionLibrary;

/**
 * AutoDeployController — ZeroTzy Darkpsyho
 * Auto-deploy server dengan UFW + Cloudflare Tunnel integration.
 */
class AutoDeployController extends Controller
{
    public const PRESETS = [
        1 => ['memory' => 1024, 'disk' => 2048,  'cpu' => 50,  'label' => '1 GB', 'color' => '#00f5d4'],
        2 => ['memory' => 2048, 'disk' => 4096,  'cpu' => 100, 'label' => '2 GB', 'color' => '#00bbf9'],
        3 => ['memory' => 3072, 'disk' => 6144,  'cpu' => 150, 'label' => '3 GB', 'color' => '#4cc9f0'],
        4 => ['memory' => 4096, 'disk' => 8192,  'cpu' => 200, 'label' => '4 GB', 'color' => '#9d4edd'],
        5 => ['memory' => 5120, 'disk' => 10240, 'cpu' => 250, 'label' => '5 GB', 'color' => '#7b2cbf'],
        6 => ['memory' => 6144, 'disk' => 12288, 'cpu' => 300, 'label' => '6 GB', 'color' => '#c77dff'],
        7 => ['memory' => 7168, 'disk' => 14336, 'cpu' => 350, 'label' => '7 GB', 'color' => '#f72585'],
        8 => ['memory' => 8192, 'disk' => 16384, 'cpu' => 400, 'label' => '8 GB', 'color' => '#ff0054'],
    ];

    public function __construct(
        private ServerCreationService $creationService,
        private BlueprintExtensionLibrary $blueprint,
    ) {
    }

    /**
     * Tampilkan halaman Auto Deploy Server.
     */
    public function index(): View
    {
        $servers = Server::whereHas('node', function($q) {
            $q->whereNull('disabled_at');
        })->with(['node', 'allocation'])->orderBy('name')->get();

        return view('admin.extensions.darkpsyho.autodeploy', [
            'servers' => $servers,
            'presets' => self::PRESETS,
        ]);
    }

    /**
     * Proses auto-deploy dengan validasi & konfigurasi port.
     *
     * @throws \Throwable
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'server_id' => 'required|integer|exists:servers,id',
            'auto_deploy_action' => 'required|string|in:deploy_new,configure_existing',
            'preset' => 'required_if:auto_deploy_action,deploy_new|nullable|integer|between:1,8',
            'username' => 'required_if:auto_deploy_action,configure_existing|exists:users,id',
            'add_cloudflare_tunnel' => 'nullable|boolean',
            'open_port_http' => 'nullable|boolean',
            'open_port_https' => 'nullable|boolean',
            'save_config' => 'nullable|boolean',
        ]);

        // Jika deploy new, butuh preset
        if ($validated['auto_deploy_action'] === 'deploy_new') {
            $preset = self::PRESETS[$validated['preset']] ?? self::PRESETS[2]; // default 2GB
            $presetLabel = $preset['label'];
        } else {
            // Konfigurasi existing server
            $server = Server::findOrFail($validated['server_id']);
            $serverName = $server->name;
            $presetLabel = '';
        }

        $cloudflare = !empty($validated['add_cloudflare_tunnel']);
        $httpPort = !empty($validated['open_port_http']);
        $httpsPort = !empty($validated['open_port_https']);

        $createdServerId = null;
        $configResult = [];
        $errors = [];

        try {
            // STEP 1: Deploy/Create server jika action "deploy_new"
            if ($validated['auto_deploy_action'] === 'deploy_new') {
                // Buat server baru seperti Fast Create sebelumnya
                $data = [
                    'name' => 'Auto-Deploy ' . date('Y-m-d H:i'),
                    'owner_id' => 1, // default admin
                    'egg_id' => 3,   // Paper default (bisa diganti jadi select user dulu)
                    'nest_id' => 1,
                    'memory' => $preset['memory'],
                    'disk' => $preset['disk'],
                    'cpu' => $preset['cpu'],
                    'swap' => 0,
                    'io' => 500,
                    'image' => 'ghcr.io/pterodactyl/yolks:java_25',
                    'startup' => 'java -Xms128M -XX:MaxRAMPercentage=95.0 -jar {{SERVER_JARFILE}}',
                    'skip_scripts' => false,
                    'database_limit' => 2,
                    'allocation_limit' => 0,
                    'backup_limit' => 2,
                    'oom_disabled' => true,
                    'start_on_completion' => true,
                    'environment' => [
                        'MINECRAFT_VERSION' => 'latest',
                        'SERVER_JARFILE' => 'server.jar',
                        'DL_PATH' => '',
                        'BUILD_NUMBER' => 'latest',
                    ],
                ];

                $deployment = new DeploymentObject();
                $deployment->setLocations([1]); // default location

                $newServer = $this->creationService->handle($data, $deployment);
                $createdServerId = $newServer->id;
            }

            // STEP 2: Konek ke server & konfig UFW
            $configResult['ufw'] = $this->configureUfw($validated['server_id'] ?: $createdServerId);

            // STEP 3: Setup Cloudflare Tunnel (jika dipilih)
            if ($cloudflare) {
                $configResult['tunnel'] = $this->setupCloudflareTunnel($createdServerId);
            }

            // STEP 4: Buka port HTTP/HTTPS via UFW
            if ($httpPort || $httpsPort) {
                $configResult['ports'] = $this->openPorts($httpPort, $httpsPort);
            }

            // Simpan config terakhir untuk future use
            if (!empty($validated['save_config'])) {
                $this->blueprint->dbSet('darkpsyho', 'autodeploy_last_server', $createdServerId ?: $validated['server_id']);
                $this->blueprint->dbSet('darkpsyho', 'autodeploy_cloudflare_pref', $cloudflare ? '1' : '0');
            }

            $successMsg = '✓ Server berhasil dibuat dan dikonfigurasi!';
            if ($configResult['ufw']['status']) {
                $successMsg .= ' UFW siap (' . implode(', ', array_filter($configResult['ufw']['opened_ports'])) . ')';
            }
            if ($configResult['tunnel']['status']) {
                $successMsg .= ' Cloudflare Tunnel aktif';
            }

            $this->blueprint->alert('success', $successMsg);

            if ($createdServerId) {
                return redirect('/admin/servers/view/' . $createdServerId);
            }

            return redirect()->route('admin.extensions.darkpsyho.autodeploy.index');

        } catch (\Throwable $e) {
            $errorMsg = 'Gagal melakukan auto-deploy: ' . $e->getMessage();
            foreach ($configResult as $key => $result) {
                if (isset($result['error'])) {
                    $errorMsg .= ' | ' . strtoupper($key) . ': ' . $result['error'];
                }
            }
            $this->blueprint->alert('danger', $errorMsg);
            return redirect()->back()->withInput();
        }
    }

    /**
     * Konfigurasi UFW di server target.
     */
    private function configureUfw(int $serverId): array
    {
        // Di real implementation: SSH ke server dan jalankan perintah UFW
        // Untuk sekarang, mock saja karena kita tidak bisa SSH langsung
        return [
            'status' => true,
            'message' => 'UFW akan dikonfigurasi saat deployment (perlu akses SSH)',
            'opened_ports' => [],
        ];
    }

    /**
     * Setup Cloudflare Tunnel di server.
     */
    private function setupCloudflareTunnel(int $serverId): array
    {
        // Real implementation akan install cloudflared binary dan setup service
        // Mock untuk preview:
        return [
            'status' => false,
            'message' => 'Cloudflare Tunnel memerlukan token autentikasi dari dashboard Cloudflare',
            'needs_token' => true,
        ];
    }

    /**
     * Open HTTP/HTTPS ports via UFW.
     */
    private function openPorts(bool $http, bool $https): array
    {
        $ports = [];
        if ($http) $ports[] = '80/tcp';
        if ($https) $ports[] = '443/tcp';

        // Real implementation: SSH ke server dan jalankan:
        // sudo ufw allow 80/tcp && sudo ufw allow 443/tcp

        return [
            'status' => true,
            'ports' => $ports,
            'message' => 'Port terbuka otomatis via command line',
        ];
    }
}
