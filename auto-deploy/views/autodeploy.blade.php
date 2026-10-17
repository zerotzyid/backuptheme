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
  .ad-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
  }
  @media (max-width: 968px) { .ad-grid { grid-template-columns: 1fr; } }
  
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
</style>

<div class="ad-wrap">
  <!-- Info Alert -->
  <div class="alert-ad">
    <i class="fa fa-info-circle info-icon" style="color:#9d4edd;"></i>
    <b>Auto Deploy</b> — Konfigurasi otomatis UFW Firewall + Cloudflare Tunnel. Pastikan Anda punya akses SSH ke server sebelum deploy!
  </div>

  <form method="POST" action="{{ route('admin.extensions.darkpsyho.autodeploy.store') }}" id="autoDeployForm">
    @csrf

    <!-- Pilih Mode Deploy -->
    <div class="ad-card">
      <h3 class="ad-title"><i class="fa fa-cogs" style="color:#c77dff;"></i> Pilih Mode Deployment</h3>
      <p class="ad-desc">Pilih apakah mau buat server baru atau konfigur server yang sudah ada.</p>

      <div class="ad-grid">
        <label class="checkbox-card">
          <input type="radio" name="auto_deploy_action" value="deploy_new" checked onchange="toggleMode()">
          <span class="card-label">
            <i class="card-icon bi bi-plus-square"></i> Buat Server Baru
          </span>
        </label>

        <label class="checkbox-card">
          <input type="radio" name="auto_deploy_action" value="configure_existing" onchange="toggleMode()">
          <span class="card-label">
            <i class="card-icon bi bi-sliders"></i> Konfigur Server Existing
          </span>
        </label>
      </div>
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
                  • Egg: {{ $server->eggs ?? 'N/A' }}<br>
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
        <p style="color:#8b8fa3;font-size:13px;margin-top:10px;">⚠️ Belum ada server. Silakan deploy server baru terlebih dahulu.</p>
      @endif
    </div>

    <!-- Konfigurasi Auto -->
    <div class="ad-card">
      <h3 class="ad-title"><i class="fa fa-wrench" style="color:#ff0054;"></i> Otorisasi & Konfigurasi</h3>
      <p class="ad-desc">Pilih opsi keamanan dan access yang diinginkan.</p>

      <div class="ad-grid">
        <label class="checkbox-card">
          <input type="checkbox" name="add_cloudflare_tunnel" id="cfTunnel">
          <span class="card-label">
            <i class="card-icon bi bi-shield-check"></i> Aktifkan Cloudflare Tunnel
          </span>
        </label>

        <label class="checkbox-card">
          <input type="checkbox" name="open_port_http" id="httpPort" checked>
          <span class="card-label">
            <i class="card-icon bi bi-globe-americas"></i> Buka Port HTTP (80) via UFW
          </span>
        </label>

        <label class="checkbox-card">
          <input type="checkbox" name="open_port_https" id="httpsPort" checked>
          <span class="card-label">
            <i class="card-icon bi bi-lock"></i> Buka Port HTTPS (443) via UFW
          </span>
        </label>

        <label class="checkbox-card">
          <input type="checkbox" name="save_config" id="saveConfig">
          <span class="card-label">
            <i class="card-icon bi bi-save"></i> Simpan preferensi ini untuk next time
          </span>
        </label>
      </div>

      <div style="margin-top: 16px;">
        <small style="color:#71799a;font-size:11px;">
          ℹ️ <b>Cloudflare Tunnel:</b> Membutuhkan token autentikasi dari dashboard Cloudflare. Akan di-prompt saat pertama kali.<br>
          ℹ️ <b>UFW Auto-open:</b> Script akan run `ufw allow 80/tcp && ufw allow 443/tcp` secara otomatis.
        </small>
      </div>
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
  <div class="alert-ad" style="margin-top: 24px;">
    <b>💡 Butuh quick deployment tanpa konfigurasi?</b><br>
    Gunakan halaman <b>⚡ Fast Create</b> untuk membuat server 1-8GB dalam sekejap (tanpa UFW/Cloudflare).
    <a href="{{ route('admin.extensions.darkpsyho.fastcreate.index') }}" style="color:#c77dff;text-decoration:none;">
      <i class="fa fa-arrow-right" style="color:#9d4edd;margin-left:6px;"></i> Buka Fast Create
    </a>
  </div>
</div>

<script>
function toggleMode() {
  const mode = document.querySelector('input[name="auto_deploy_action"]:checked').value;
  const serverCard = document.getElementById('serverSelectionCard');
  const btn = document.getElementById('deployBtn');
  
  if (mode === 'deploy_new') {
    serverCard.style.display = 'none';
    btn.innerHTML = '<i class="fa fa-bolt"></i> Buat & Deploy Baru';
  } else {
    serverCard.style.display = 'block';
    btn.innerHTML = '<i class="fa fa-bolt"></i> Configure & Deploy';
  }
}

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

// Init toggle
window.addEventListener('DOMContentLoaded', toggleMode);
</script>
@endsection
