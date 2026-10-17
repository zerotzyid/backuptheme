<?php

namespace Pterodactyl\Http\Controllers\Admin\Extensions\darkpsyho;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Pterodactyl\Models\Server;
use Pterodactyl\Models\User;
use Pterodactyl\Models\Allocation;
use Pterodactyl\Models\Objects\DeploymentObject;
use Pterodactyl\Models\AutoDeployConfig;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Services\Servers\ServerCreationService;
use Pterodactyl\Services\AutoDeploy\AutoDeployService;
use Pterodactyl\BlueprintFramework\Libraries\ExtensionLibrary\Admin\BlueprintAdminLibrary as BlueprintAdminLibrary;

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
        private BlueprintAdminLibrary $blueprint,
        private AutoDeployService $autoDeployService,
    ) {
    }

    /**
     * Tampilkan halaman Auto Deploy Server.
     */
    public function index(): View
    {
        $servers = Server::whereHas('node', function ($q) {
            $q->whereNull('disabled_at');
        })->with(['node', 'allocation'])->orderBy('name')->get();

        $users = User::orderBy('username')->get(['id', 'username', 'email', 'root_admin']);
        $configs = $this->autoDeployService->listConfigs();

        return view('admin.extensions.darkpsyho.autodeploy', [
            'servers' => $servers,
            'users' => $users,
            'configs' => $configs,
            'presets' => self::PRESETS,
        ]);
    }

    /**
     * Proses auto-deploy dengan validasi & konfigurasi port.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'server_id' => 'required_if:auto_deploy_action,configure_existing|nullable|integer|exists:servers,id',
            'auto_deploy_action' => 'required|string|in:deploy_new,configure_existing',
            'preset' => 'required_if:auto_deploy_action,deploy_new|nullable|integer|between:1,8',
            'username' => 'nullable|integer|exists:users,id',
            'ssh_host' => 'nullable|string|max:255',
            'ssh_port' => 'nullable|integer|min:1|max:65535',
            'ssh_username' => 'nullable|string|max:100',
            'ssh_private_key' => 'nullable|file|mimes:pem,key',
            'add_cloudflare_tunnel' => 'nullable|boolean',
            'cloudflare_token' => 'nullable|string|max:500',
            'cloudflare_tunnel_name' => 'nullable|string|max:100',
            'open_port_http' => 'nullable|boolean',
            'open_port_https' => 'nullable|boolean',
            'save_config' => 'nullable|boolean',
        ]);

        // Tentukan server target
        $serverId = $validated['server_id'] ?? null;
        $preset = $validated['preset'] ?? 2;
        $presetData = self::PRESETS[$preset] ?? self::PRESETS[2];

        $config = AutoDeployConfig::create([
            'server_id' => $serverId,
            'action' => $validated['auto_deploy_action'],
            'preset' => $preset,
            'username' => $validated['username'] ?? null,
            'ssh_host' => $validated['ssh_host'] ?? null,
            'ssh_port' => $validated['ssh_port'] ?? 22,
            'ssh_username' => $validated['ssh_username'] ?? 'root',
            'open_port_http' => !empty($validated['open_port_http']),
            'open_port_https' => !empty($validated['open_port_https']),
            'add_cloudflare_tunnel' => !empty($validated['add_cloudflare_tunnel']),
            'cloudflare_token' => $validated['cloudflare_token'] ?? null,
            'cloudflare_tunnel_name' => $validated['cloudflare_tunnel_name'] ?? null,
            'status' => 'pending',
        ]);

        // Handle private key upload
        if ($request->hasFile('ssh_private_key')) {
            $keyPath = $request->file('ssh_private_key')->getRealPath();
            $keyContent = file_get_contents($keyPath);
            $config->update(['ssh_private_key' => $keyContent]);
        }

        // Jika deploy_new, buat server baru terlebih dahulu
        if ($validated['auto_deploy_action'] === 'deploy_new') {
            try {
                $data = [
                    'name' => 'Auto-Deploy ' . date('Y-m-d H:i'),
                    'owner_id' => 1,
                    'egg_id' => 3, // Paper default
                    'nest_id' => 1,
                    'memory' => $presetData['memory'],
                    'disk' => $presetData['disk'],
                    'cpu' => $presetData['cpu'],
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
                $deployment->setLocations([1]);

                $newServer = $this->creationService->handle($data, $deployment);
                $config->update(['server_id' => $newServer->id]);
                $serverId = $newServer->id;
            } catch (\Throwable $e) {
                $config->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
                $this->blueprint->alert('danger', 'Gagal membuat server baru: ' . $e->getMessage());
                return redirect()->route('admin.extensions.darkpsyho.autodeploy.index');
            }
        }

        // Jalankan auto-deploy service
        $config->refresh();
        $result = $this->autoDeployService->execute($config);

        if ($result['success']) {
            $this->blueprint->alert('success', $result['message']);
            if ($config->server_id) {
                return redirect('/admin/servers/view/' . $config->server_id);
            }
        } else {
            $this->blueprint->alert('danger', $result['message']);
        }

        return redirect()->route('admin.extensions.darkpsyho.autodeploy.index');
    }

    /**
     * Cek status konfigurasi deploy untuk server tertentu.
     */
    public function status(int $serverId): RedirectResponse
    {
        $config = AutoDeployConfig::where('server_id', $serverId)->latest()->first();

        if (!$config) {
            $this->blueprint->alert('warning', 'Belum ada konfigurasi auto-deploy untuk server ini.');
            return redirect()->route('admin.extensions.darkpsyho.autodeploy.index');
        }

        $this->blueprint->alert('info', 'Status: ' . $config->status . ' | Tunnel: ' . ($config->tunnel_active ? 'Aktif' : 'Tidak Aktif'));
        return redirect()->route('admin.extensions.darkpsyho.autodeploy.index');
    }
}
