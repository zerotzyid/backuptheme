<?php

namespace Pterodactyl\Services\AutoDeploy;

use Pterodactyl\Models\AutoDeployConfig;
use Pterodactyl\Models\Server;
use Pterodactyl\BlueprintFramework\Libraries\ExtensionLibrary\Admin\BlueprintAdminLibrary as BlueprintAdminLibrary;
use phpseclib\Net\SSH2;
use phpseclib\Crypt\RSA;

class AutoDeployService
{
    protected BlueprintAdminLibrary $blueprint;

    public function __construct(
        BlueprintAdminLibrary $blueprint,
    ) {
        $this->blueprint = $blueprint;
    }

    /**
     * Execute auto-deploy for a given config.
     *
     * @param AutoDeployConfig $config
     * @return array ['success' => bool, 'message' => string, 'data' => array]
     */
    public function execute(AutoDeployConfig $config): array
    {
        $config->update(['status' => 'configuring']);

        try {
            $server = $config->server_id ? Server::find($config->server_id) : null;

            // STEP 1: Koneksi SSH ke server target
            $ssh = $this->connectSSH($config);
            if (!$ssh) {
                $config->update(['status' => 'failed', 'error_message' => 'SSH connection failed']);
                return ['success' => false, 'message' => 'Gagal terhubung ke server via SSH', 'data' => []];
            }

            // STEP 2: Cek UFW status dan enable jika perlu
            $ufwResult = $this->configureUfw($ssh, $config);
            if (!$ufwResult['success']) {
                $config->update(['status' => 'failed', 'error_message' => $ufwResult['message']]);
                return ['success' => false, 'message' => $ufwResult['message'], 'data' => []];
            }

            // STEP 3: Install & setup Cloudflare Tunnel jika diperlukan
            if ($config->add_cloudflare_tunnel) {
                $tunnelResult = $this->setupCloudflareTunnel($ssh, $config);
                if (!$tunnelResult['success']) {
                    // Tunnel gagal tapi UFW berhasil, lanjut tapi catat error
                    $config->update([
                        'tunnel_active' => false,
                        'error_message' => $tunnelResult['message'],
                    ]);
                } else {
                    $config->update([
                        'tunnel_active' => true,
                        'cloudflare_tunnel_url' => $tunnelResult['tunnel_url'] ?? null,
                        'cloudflare_tunnel_name' => $tunnelResult['tunnel_name'] ?? null,
                    ]);
                }
            }

            // STEP 4: Update status berhasil
            $config->update(['status' => 'completed']);

            $ssh->disconnect();

            return [
                'success' => true,
                'message' => 'Auto-deploy berhasil! UFW & Cloudflare Tunnel siap digunakan.',
                'data' => [
                    'server_id' => $config->server_id,
                    'preset' => $config->preset,
                    'tunnel_active' => $config->tunnel_active,
                    'ufw_configured' => true,
                ],
            ];
        } catch (\Throwable $e) {
            $config->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage(), 'data' => []];
        }
    }

    /**
     * Connect SSH ke server target.
     */
    private function connectSSH(AutoDeployConfig $config): ?SSH2
    {
        try {
            $ssh = new SSH2($config->ssh_host, $config->ssh_port);
            
            // Coba login dengan private key jika tersedia, else password
            if ($config->ssh_private_key) {
                $key = new RSA();
                $key->loadKey($config->ssh_private_key);
                $login = $ssh->login($config->ssh_username, $key);
            } else {
                // Ambil password dari settings (tidak aman untuk production, hanya contoh)
                $password = $this->blueprint->dbGet('darkpsyho', 'auto_deploy_ssh_password', '');
                $login = $ssh->login($config->ssh_username, $password);
            }

            return $login ? $ssh : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Konfigurasi UFW di server target.
     */
    private function configureUfw(SSH2 $ssh, AutoDeployConfig $config): array
    {
        $commands = [];
        $openedPorts = [];

        // Enable UFW jika belum
        $commands[] = 'echo y | ufw enable 2>/dev/null || true';

        // Buka port HTTP
        if ($config->open_port_http) {
            $commands[] = 'ufw allow 80/tcp 2>/dev/null || true';
            $openedPorts[] = '80/tcp';
        }

        // Buka port HTTPS
        if ($config->open_port_https) {
            $commands[] = 'ufw allow 443/tcp 2>/dev/null || true';
            $openedPorts[] = '443/tcp';
        }

        // Buka port SSH default
        $commands[] = 'ufw allow ' . $config->ssh_port . '/tcp 2>/dev/null || true';

        // Buka port untuk Cloudflare Tunnel jika aktif (biasanya 80/443 sudah cukup)
        if ($config->add_cloudflare_tunnel) {
            // Cloudflare Tunnel tidak membutuhkan port inbound tambahan
            // kecuali jika Anda ingin expose langsung
        }

        $output = [];
        $success = true;
        foreach ($commands as $cmd) {
            $result = $ssh->exec($cmd);
            if (strpos($result, 'Error') !== false || strpos($result, 'ERROR') !== false) {
                $success = false;
            }
            $output[] = $cmd . ' => ' . trim($result);
        }

        return [
            'success' => $success,
            'message' => implode("\n", $output),
            'opened_ports' => $openedPorts,
        ];
    }

    /**
     * Install & setup Cloudflare Tunnel di server.
     */
    private function setupCloudflareTunnel(SSH2 $ssh, AutoDeployConfig $config): array
    {
        $commands = [];

        // Install cloudflared
        $commands[] = 'wget -q https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64 -O /usr/local/bin/cloudflared 2>/dev/null || true';
        $commands[] = 'chmod +x /usr/local/bin/cloudflared 2>/dev/null || true';
        $commands[] = 'cloudflared version 2>/dev/null || echo "cloudflared not installed"';

        // Setup tunnel dengan token
        $token = $config->cloudflare_token;
        if (!$token) {
            return [
                'success' => false,
                'message' => 'Cloudflare token tidak tersedia. Ambil dari https://dash.cloudflare.com/?to=/:account/:team/tunnels',
            ];
        }

        // Generate tunnel name jika belum ada
        $tunnelName = $config->cloudflare_tunnel_name ?: 'zt-deploy-' . ($config->server_id ?: 'server');

        // Setup tunnel config
        $configDir = '/root/.cloudflared';
        $commands[] = 'mkdir -p ' . $configDir . ' 2>/dev/null || true';
        $commands[] = 'cloudflared tunnel create ' . $tunnelName . ' --token ' . escapeshellarg($token) . ' 2>/dev/null || true';

        // Buat config.yml untuk tunnel (dihandle oleh generateCloudflaredConfig)

        // Write config via SSH
        $configFileContent = $this->generateCloudflaredConfig($tunnelName, $configDir, $config->server_id);
        $commands[] = "echo " . escapeshellarg($configFileContent) . " > " . $configDir . "/config.yml 2>/dev/null || true";

        // Start tunnel as service
        $commands[] = 'cloudflared service install 2>/dev/null || true';
        $commands[] = 'cloudflared --config ' . $configDir . '/config.yml run --daemon 2>/dev/null || true';

        $output = [];
        foreach ($commands as $cmd) {
            $result = $ssh->exec($cmd);
            $output[] = $cmd . ' => ' . trim($result);
        }

        return [
            'success' => true,
            'message' => implode("\n", $output),
            'tunnel_name' => $tunnelName,
            'tunnel_url' => 'https://' . $tunnelName . '.cfargotunnel.com',
        ];
    }

    /**
     * Generate cloudflared config file content.
     */
    private function generateCloudflaredConfig(string $tunnelName, string $configDir, ?int $serverId): string
    {
        $ingress = "";
        if ($serverId) {
            // Pastikan tunnel mengarah ke port server yang benar
            $ingress = "  - hostname: " . $tunnelName . ".cfargotunnel.com\n    service: http://localhost:80\n";
        }

        return <<<CONFIG
tunnel: {$tunnelName}
credentials-file: {$configDir}/{$tunnelName}.json
ingress:
{$ingress}  - service: http_status:404
CONFIG;
    }

    /**
     * Get status konfigurasi untuk server.
     */
    public function getStatus(int $serverId): array
    {
        $config = AutoDeployConfig::where('server_id', $serverId)->latest()->first();
        if (!$config) {
            return ['status' => 'not_configured', 'message' => 'Belum ada konfigurasi auto-deploy'];
        }

        return [
            'status' => $config->status,
            'tunnel_active' => $config->tunnel_active,
            'cloudflare_tunnel_url' => $config->cloudflare_tunnel_url,
            'cloudflare_tunnel_name' => $config->cloudflare_tunnel_name,
            'error_message' => $config->error_message,
            'created_at' => $config->created_at,
            'updated_at' => $config->updated_at,
        ];
    }

    /**
     * List semua konfigurasi deploy.
     */
    public function listConfigs()
    {
        return AutoDeployConfig::with('server')->orderBy('created_at', 'desc')->limit(20)->get();
    }
}
