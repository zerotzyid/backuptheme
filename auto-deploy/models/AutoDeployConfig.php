<?php

namespace Pterodactyl\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $server_id
 * @property string $action
 * @property int|null $preset
 * @property string|null $username
 * @property string|null $ssh_host
 * @property int $ssh_port
 * @property string|null $ssh_username
 * @property string|null $ssh_private_key
 * @property bool $open_port_http
 * @property bool $open_port_https
 * @property bool $add_cloudflare_tunnel
 * @property string|null $cloudflare_token
 * @property string|null $cloudflare_tunnel_name
 * @property string|null $cloudflare_tunnel_url
 * @property bool $tunnel_active
 * @property string $status
 * @property string|null $error_message
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \Pterodactyl\Models\Server|null $server
 */
class AutoDeployConfig extends Model
{
    protected $table = 'auto_deploy_configs';

    protected $fillable = [
        'server_id',
        'action',
        'preset',
        'username',
        'ssh_host',
        'ssh_port',
        'ssh_username',
        'ssh_private_key',
        'open_port_http',
        'open_port_https',
        'add_cloudflare_tunnel',
        'cloudflare_token',
        'cloudflare_tunnel_name',
        'cloudflare_tunnel_url',
        'tunnel_active',
        'status',
        'error_message',
    ];

    protected $casts = [
        'open_port_http' => 'boolean',
        'open_port_https' => 'boolean',
        'add_cloudflare_tunnel' => 'boolean',
        'tunnel_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }
}
