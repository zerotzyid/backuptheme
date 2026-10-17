@extends('layouts.admin')

<?php $EXTENSION_ID = "darkpsyho"; ?>

@section('title')
  ⚡ Auto Deploy Server
@endsection

@section('content-header')
  <h1 style="display: flex; align-items: center; gap: 12px; margin: 0;">
    <span style="font-size: 28px; background: linear-gradient(135deg, #c77dff, #9d4edd); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-weight: 800;">🔥 Auto Deploy</span>
    <span style="font-size: 13px; padding: 4px 12px; background: rgba(157,78,221,0.15); border: 1px solid rgba(157,78,221,0.35); border-radius: 12px; color: #c77dff; font-weight: 600;">DARKPSYHO</span>
  </h1>
  <ol class="breadcrumb">
    <li><a href="/admin"><i class="fa fa-home"></i> Admin</a></li>
    <li><a href="{{ route('admin.extensions') }}">Extensions</a></li>
    <li class="active">Auto Deploy</li>
  </ol>
@endsection

@section('content')
<style>
  /* Auto Deploy Styles */
  .ad-wrap { max-width: 1200px; margin: 0 auto; }
  .ad-card {
    background: #121520;
    border: 1px solid rgba(157, 78, 221, 0.25);
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 20px;
    box-shadow: 0 4px 24px -4px rgba(0,0,0,0.6), 0 0 16px -6px rgba(157,78,221,0.15);
  }
  .ad-card:hover { border-color: rgba(199,125,255,0.4); }
  .ad-title {
    color: #e9d5ff;
    font-size: 16px;
    font-weight: 700;
    margin: 0 0 6px 0;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .ad-desc {
    color: #8b8fa3;
    font-size: 12.5px;
    margin: 0 0 18px 0;
  }
  .ad-grid { display: grid; gap: 20px; }
  @media (min-width: 968px) { .ad-grid { grid-template-columns: 1fr 1fr; } }
  
  /* Server List */
  .server-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 12px;
  }
  .server-item {
    position: relative;
    background: linear-gradient(145deg, #171b2a, #10141f);
    border: 2px solid rgba(157,78,221,0.2);
    border-radius: 10px;
    padding: 14px;
    cursor: pointer;
    transition: all 0.18s ease;
  }
  .server-item:hover {
    border-color: rgba(199,125,255,0.5);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px -4px rgba(0,0,0,0.8), 0 0 14px rgba(157,78,221,0.25);
  }
  .server-item input[type="radio"] { display: none; }
  .server-item input:checked + .server-inner {
    border-color: #9d4edd;
    background: linear-gradient(145deg, #231a33, #1a1425);
    box-shadow: inset 0 0 20px rgba(157,78,221,0.08);
  }
  .server-item:has(input:checked) .server-inner {
    border-color: #9d4edd;
    box-shadow: 0 0 20px -4px rgba(157,78,221,0.55), inset 0 0 20px rgba(157,78,221,0.08);
  }
  .server-name {
    font-size: 14px;
    font-weight: 700;
    color: #c77dff;
    margin-bottom: 4px;
  }
  .server-meta {
    font-size: 11px;
    color: #71799a;
    line-height: 1.5;
  }
  .server-badge {
    position: absolute;
    top: -8px; right: -8px;
    width: 10px; height: 10px;
    border-radius: 50%;
    background: radial-gradient(circle, #c77dff, #9d4edd);
    box-shadow: 0 0 10px rgba(157,78,221,0.8);
    opacity: 0;
    transition: opacity 0.2s;
  }
  .server-item:has(input:checked) .server-badge { opacity: 1; }

  /* Checkbox Cards */
  .checkbox-card {
    position: relative;
    background: #0d1018;
    border: 1px solid rgba(157,78,221,0.22);
    border-radius: 8px;
    padding: 16px;
    cursor: pointer;
    transition: all 0.18s;
  }
  .checkbox-card:hover { border-color: rgba(199,125,255,0.5); }
  .checkbox-card input[type="checkbox"] { display: none; }
  .checkbox-card input:checked + .card-label { color: #c77dff; font-weight: 700; }
  .checkbox-card:has(input:checked) {
    border-color: #9d4edd;
    background: linear-gradient(145deg, rgba(157,78,221,0.12), rgba(157,78,221,0.04));
  }
  .checkbox-card .card-icon {
    font-size: 20px;
    margin-right: 8px;
    color: #9d4edd;
  }
  .checkbox-card .card-label {
    color: #c4b5fd;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.5px;
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
  }

  /* Status Badges */
  .status-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
  }
  .status-pending { background: rgba(199,125,255,0.12); color: #c77dff; border: 1px solid rgba(199,125,255,0.3); }
  .status-configuring { background: rgba(247,37,133,0.12); color: #f72585; border: 1px solid rgba(247,37,133,0.3); }
  .status-completed { background: rgba(0,245,212,0.12); color: #00f5d4; border: 1px solid rgba(0,245,212,0.3); }
  .status-failed { background: rgba(255,0,84,0.12); color: #ff0054; border: 1px solid rgba(255,0,84,0.3); }
  
  .tunnel-active { color: #00f5d4; }
  .tunnel-inactive { color: #71799a; }

  /* Config History Table */
  .config-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
    background: #0d1018;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid rgba(157,78,221,0.2);
  }
  .config-table th {
    background: rgba(157,78,221,0.1);
    color: #c77dff;
    padding: 10px;
    text-align: left;
    font-weight: 700;
    text-transform: uppercase;
    font-size: 10px;
    letter-spacing: 0.5px;
  }
  .config-table td {
    padding: 10px;
    border-bottom: 1px solid rgba(157,78,221,0.1);
    color: #a89ec9;
  }
  .config-table tr:last-child td { border-bottom: none; }
  .config-table tr:hover td { background: rgba(157,78,221,0.03); }

  /* Actions */
  .ad-actions {
    display: flex;
    gap: 12px;
    margin-top: 24px;
  }
  .btn-ad {
    flex: 1;
    padding: 14px 20px;
    background: linear-gradient(135deg, rgba(157,78,221,0.2), rgba(157,78,221,0.08));
    border: 1px solid rgba(199,125,255,0.4);
    color: #c77dff;
    font-size: 14px;
    font-weight: 700;
    border-radius: 9px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
  }
  .btn-ad:hover:not(:disabled) {
    background: linear-gradient(135deg, rgba(157,78,221,0.4), rgba(157,78,221,0.2));
    border-color: #9d4edd;
    transform: translateY(-1px);
  }
  .btn-ad:active:not(:disabled) { transform: translateY(0); }
  .btn-ad:disabled { opacity: 0.4; cursor: not-allowed; }

  /* Alert Box */
  .alert-ad {
    background: linear-gradient(145deg, rgba(123,44,191,0.1), rgba(157,78,221,0.05));
    border: 1px dashed rgba(157,78,221,0.3);
    border-radius: 9px;
    padding: 16px 18px;
    font-size: 12px;
    color: #a89ec9;
    line-height: 1.7;
    margin-bottom: 16px;
  }
  .alert-ad b { color: #c77dff; }
  .alert-ad .info-icon { color: #9d4edd; margin-right: 6px; }

  /* Form Sections */
  .form-section-title {
    color: #c77dff;
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin: 20px 0 12px 0;
    padding-bottom: 6px;
    border-bottom: 1px solid rgba(157,78,221,0.2);
  }
  .form-field {
    margin-bottom: 14px;
  }
  .form-field label {
    display: block;
    color: #c4b5fd;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    margin-bottom: 6px;
  }
  .form-field input, .form-field select, .form-field textarea {
    width: 100%;
    background: #0d1018;
    border: 1px solid rgba(157,78,221,0.22);
    border-radius: 7px;
    padding: 10px 12px;
    color: #e9e8f0;
    font-size: 13px;
    transition: all 0.18s;
  }
  .form-field input:focus, .form-field select:focus, .form-field textarea:focus {
    outline: none;
    border-color: #9d4edd;
    box-shadow: 0 0 0 3px rgba(157,78,221,0.15);
  }
  .form-field input.file-input { padding: 8px 12px; }
  
  /* Toggle Mode */
  .mode-selector {
    display: flex;
    gap: 8px;
    margin-bottom: 16px;
  }
  .mode-btn {
    flex: 1;
    padding: 12px;
    background: #0d1018;
    border: 1px solid rgba(157,78,221,0.22);
    border-radius: 7px;
    color: #c4b5fd;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.18s;
    text-align: center;
  }
  .mode-btn.active {
    background: linear-gradient(135deg, #7b2cbf, #9d4edd);
    color: #fff;
    border-color: #9d4edd;
    box-shadow: 0 0 15px rgba(157,78,221,0.4);
  }

  /* Quick Create Link */
  .quick-link {
    margin-top: 24px;
    padding: 16px;
    background: linear-gradient(145deg, rgba(157,78,221,0.08), rgba(157,78,221,0.02));
    border: 1px dashed rgba(157,78,221,0.3);
    border-radius: 9px;
  }
</style>

<div class="ad-wrap">
  <!-- Info Alert -->
  <div class="alert-ad">
    <i class="fa fa-info-circle info-icon" style="color:#9d4edd;"></i>
    <b>Auto Deploy</b> — Konfigurasi otomatis UFW Firewall + Cloudflare Tunnel. Pastikan Anda punya akses SSH ke server sebelum deploy!
  </div>

  <!-- Config History -->
  @if($configs->count() > 0)
    <div class="ad-card">
      <h3 class="ad-title"><i class="fa fa-history" style="color:#c77dff;"></i> Riwayat Deployment</h3>
      <table class="config-table">
        <thead>
          <tr>
            <th>Tanggal</th>
            <th>Server</th>
            <th>Aksi</th>
            <th>Status</th>
            <th>Tunnel</th>
            <th>Port</th>
          </tr>
        </thead>
        <tbody>
          @foreach($configs as $cfg)
            <tr>
              <td>{{ $cfg->created_at ? $cfg->created_at->format('d/m/Y H:i') : '-' }}</td>
              <td>{{ $cfg->server ? $cfg->server->name : 'N/A' }}</td>
              <td>{{ $cfg->action === 'deploy_new' ? 'Deploy Baru' : 'Konfigurasi' }}</td>
              <td>
                <span class="status-badge status-{{ $cfg->status }}">{{ ucfirst($cfg->status) }}</span>
              </td>
              <td>
                <span class="{{ $cfg->tunnel_active ? 'tunnel-active' : 'tunnel-inactive' }}">
                  {{ $cfg->tunnel_active ? '✓ Aktif' : '✗ Off' }}
                </span>
              </td>
              <td>
                @if($cfg->open_port_http || $cfg->open_port_https)
                  {{ $cfg->open_port_http ? '80' : '' }} {{ $cfg->open_port_https ? '443' : '' }}
                @else
                  -
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @endif

  <form method="POST" action="{{ route('admin.extensions.darkpsyho.autodeploy.store') }}" id="autoDeployForm">
    @csrf

    <!-- Pilih Mode Deploy -->
    <div class="ad-card">
      <h3 class="ad-title"><i class="fa fa-cogs" style="color:#c77dff;"></i> Pilih Mode Deployment</h3>
      <p class="ad-desc">Pilih apakah mau buat server baru atau konfigur server yang sudah ada.</p>

      <div class="mode-selector">
        <label class="mode-btn active" onclick="setMode('deploy_new')">
          <i class="bi bi-plus-square"></i> Buat Server Baru
        </label>
        <label class="mode-btn" onclick="setMode('configure_existing')">
          <i class="bi bi-sliders"></i> Konfigurasi Existing
        </label>
      </div>

      <input type="hidden" name="auto_deploy_action" id="deployAction" value="deploy_new">
    </div>

    <!-- Server Selection / New Server Preset -->
    <div class="ad-card" id="serverSelectionCard">
      <h3 class="ad-title"><i class="fa fa-server" style="color:#00f5d4;"></i> Pilih Server Target</h3>
      <p class="ad-desc">Server mana yang mau di-deploy/konfigur dengan otomatisasi?</p>

      @if($servers->count() > 0)
        <div class="server-list">
          @foreach($servers as $server)
            <label class="server-item">
              <input type="radio" name="server_id" value="{{ $server->id }}" @if(old('server_id') == $server->id) checked @endif>
              <div class="server-inner">
                <div class="server-name">{{ $server->name }}</div>
                <div class="server-meta">
                  • RAM: {{ $server->memory }}MB | CPU: {{ $server->cpu }}%<br>
                  • Node: {{ $server->node ? $server->node->name : 'Unknown' }}<br>
                  • IP: {{ $server->allocation ? $server->allocation->ip . ':' . $server->allocation->port : 'N/A' }}
                </div>
                <span class="server-badge"></span>
              </div>
            </label>
          @endforeach
        </div>
      @else
        <p style="color:#8b8fa3;font-size:13px;margin-top:10px;">⚠️ Belum ada server. Silakan pilih 'Buat Server Baru' untuk deploy pertama kali.</p>
      @endif
    </div>

    <!-- Preset RAM (hanya untuk deploy baru) -->
    <div class="ad-card" id="presetCard">
      <h3 class="ad-title"><i class="fa fa-memory" style="color:#c77dff;"></i> Paket RAM</h3>
      <p class="ad-desc">Disk otomatis 2× RAM, CPU sesuai tier.</p>
      <div class="ad-grid">
        @foreach($presets as $key => $p)
          <label class="checkbox-card" style="cursor:pointer;">
            <input type="radio" name="preset" value="{{ $key }}" @if($key == 2) checked @endif>
            <span class="card-label">
              {{ $p['label'] }} — {{ $p['memory'] }}MB RAM, {{ $p['disk'] }}MB Disk, {{ $p['cpu'] }}% CPU
            </span>
          </label>
        @endforeach
      </div>
    </div>

    <!-- SSH & Tunnel Config -->
    <div class="ad-card">
      <h3 class="ad-title"><i class="fa fa-wrench" style="color:#ff0054;"></i> Konfigurasi Server & Tunnel</h3>
      <p class="ad-desc">Masukkan detail SSH server untuk otomatisasi UFW & Cloudflare Tunnel.</p>

      <div class="form-field">
        <label>SSH Host (IP/Domain)</label>
        <input type="text" name="ssh_host" placeholder="1.2.3.4 atau domain.com" required>
      </div>

      <div class="ad-grid">
        <div class="form-field">
          <label>SSH Port</label>
          <input type="number" name="ssh_port" value="22" min="1" max="65535">
        </div>
        <div class="form-field">
          <label>SSH Username</label>
          <input type="text" name="ssh_username" value="root">
        </div>
      </div>

      <div class="form-field">
        <label>Private Key (.pem/.key)</label>
        <input type="file" name="ssh_private_key" accept=".pem,.key,.txt" class="file-input">
        <small style="color:#71799a;">Opsional — jika kosong, akan menggunakan password dari settings panel.</small>
      </div>

      <div class="form-section-title">🌐 Cloudflare Tunnel</h4>
      <div class="ad-grid">
        <label class="checkbox-card">
          <input type="checkbox" name="add_cloudflare_tunnel" id="cfTunnel">
          <span class="card-label">
            <i class="card-icon bi bi-shield-check"></i> Aktifkan Cloudflare Tunnel
          </span>
        </label>
      </div>

      <div id="cfFields" style="display:none; margin-top:12px;">
        <div class="form-field">
          <label>Cloudflare Token</label>
          <input type="text" name="cloudflare_token" placeholder="token dari cloudflare.com">
          <small style="color:#71799a;">Ambil dari https://dash.cloudflare.com/?to=/:account/:team/tunnels</small>
        </div>
        <div class="form-field">
          <label>Tunnel Name (opsional)</label>
          <input type="text" name="cloudflare_tunnel_name" placeholder="zt-server-01">
          <small style="color:#71799a;">Contoh: zt-myserver1 akan jadi zt-myserver1.cfargotunnel.com</small>
        </div>
      </div>

      <div class="form-section-title">🔥 Firewall (UFW)</h4>
      <div class="ad-grid">
        <label class="checkbox-card">
          <input type="checkbox" name="open_port_http" id="httpPort" checked>
          <span class="card-label">
            <i class="card-icon bi bi-globe-americas"></i> Buka Port HTTP (80)
          </span>
        </label>
        <label class="checkbox-card">
          <input type="checkbox" name="open_port_https" id="httpsPort" checked>
          <span class="card-label">
            <i class="card-icon bi bi-lock"></i> Buka Port HTTPS (443)
          </span>
        </label>
      </div>

      <label class="checkbox-card" style="margin-top:12px;">
        <input type="checkbox" name="save_config" id="saveConfig">
        <span class="card-label">
          <i class="card-icon bi bi-save"></i> Simpan preferensi untuk next time
        </span>
      </label>
    </div>

    <!-- Action Buttons -->
    <div class="ad-actions">
      <button type="submit" class="btn-ad" id="deployBtn">
        <span id="btnText">
          <i class="fa fa-bolt"></i> 🔥 Deploy Sekarang
        </span>
        <span class="spinner" style="display:none;width:16px;height:16px;border:2px solid rgba(255,255,255,0.3);border-top-color:#fff;border-radius:50%;animation:spin 0.7s linear infinite;"></span>
      </button>
    </div>
  </form>

  <!-- Quick Create Alternative Card -->
  <div class="quick-link">
    <b>💡 Butuh quick deployment tanpa konfigurasi?</b><br>
    Gunakan halaman <b>⚡ Fast Create</b> untuk membuat server 1-8GB dalam sekejap (tanpa UFW/Cloudflare).
    <a href="{{ route('admin.extensions.darkpsyho.fastcreate.index') }}" style="color:#c77dff;text-decoration:none;">
      <i class="fa fa-arrow-right" style="color:#9d4edd;margin-left:6px;"></i> Buka Fast Create
    </a>
  </div>
</div>

<script>
function setMode(mode) {
  document.getElementById('deployAction').value = mode;
  
  const serverCard = document.getElementById('serverSelectionCard');
  const presetCard = document.getElementById('presetCard');
  const btnText = document.getElementById('btnText');
  
  // Update mode buttons
  document.querySelectorAll('.mode-btn').forEach(b => b.classList.remove('active'));
  event.currentTarget.classList.add('active');
  
  if (mode === 'deploy_new') {
    serverCard.style.display = 'none';
    presetCard.style.display = 'block';
    btnText.innerHTML = '<i class="fa fa-bolt"></i> 🔥 Deploy Server Baru';
  } else {
    serverCard.style.display = 'block';
    presetCard.style.display = 'none';
    btnText.innerHTML = '<i class="fa fa-bolt"></i> 🔧 Configure Existing';
  }
  
  toggleCFFields();
}

function toggleCFFields() {
  const cfCheck = document.getElementById('cfTunnel');
  const cfFields = document.getElementById('cfFields');
  if (cfFields) {
    cfFields.style.display = cfCheck.checked ? 'block' : 'none';
  }
}

document.getElementById('cfTunnel').addEventListener('change', toggleCFFields);

document.getElementById('autoDeployForm').addEventListener('submit', function() {
  const btn = document.getElementById('deployBtn');
  btn.classList.add('loading');
  btn.disabled = true;
});

// CSS animation
const styleSheet = document.createElement("style");
styleSheet.textContent = `
  @keyframes spin { to { transform: rotate(360deg); } }
`;
document.head.appendChild(styleSheet);

// Init
window.addEventListener('DOMContentLoaded', function() {
  setMode('deploy_new');
});
</script>
@endsection
