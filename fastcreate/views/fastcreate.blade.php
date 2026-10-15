@extends('layouts.admin')

<?php
  $EXTENSION_ID = "darkpsyho";
  $EXTENSION_NAME = "ZeroTzy - Theme";
  $EXTENSION_VERSION = "1.0.0";
  $EXTENSION_DESCRIPTION = "Fast Create Server — Instant 1-8 GB RAM Presets";
  $EXTENSION_ICON = "/assets/extensions/darkpsyho/icon.jpg";
?>

@section('title')
  Fast Create Server
@endsection

@section('content-header')
  <h1 style="display: flex; align-items: center; gap: 12px; margin: 0;">
    <span style="font-size: 28px; background: linear-gradient(135deg, #c77dff, #9d4edd); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-weight: 800;">⚡ Fast Create</span>
    <span style="font-size: 13px; padding: 4px 12px; background: rgba(157,78,221,0.15); border: 1px solid rgba(157,78,221,0.35); border-radius: 12px; color: #c77dff; font-weight: 600;">DARKPSYHO</span>
  </h1>
  <ol class="breadcrumb">
    <li><a href="/admin"><i class="fa fa-home"></i> Admin</a></li>
    <li><a href="{{ route('admin.extensions') }}">Extensions</a></li>
    <li class="active">Fast Create</li>
  </ol>
@endsection

@section('content')
<style>
  /* ═══════════════════════════════════════════
     ZeroTzy Darkpsyho — Fast Create Styles
     ═══════════════════════════════════════════ */
  .fc-wrap {
    max-width: 1180px;
    margin: 0 auto;
    font-family: 'Segoe UI', Roboto, sans-serif;
  }
  .fc-card {
    background: #121520;
    border: 1px solid rgba(157, 78, 221, 0.25);
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 20px;
    box-shadow: 0 4px 24px -4px rgba(0,0,0,0.6), 0 0 16px -6px rgba(157,78,221,0.15);
  }
  .fc-card:hover { border-color: rgba(199,125,255,0.4); }
  .fc-title {
    color: #e9d5ff;
    font-size: 16px;
    font-weight: 700;
    margin: 0 0 6px 0;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .fc-desc {
    color: #8b8fa3;
    font-size: 12.5px;
    margin: 0 0 18px 0;
  }
  .fc-grid-preset {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
  }
  @media (max-width: 768px) { .fc-grid-preset { grid-template-columns: repeat(2, 1fr); } }
  .fc-preset {
    position: relative;
    background: linear-gradient(145deg, #171b2a, #10141f);
    border: 2px solid rgba(157,78,221,0.2);
    border-radius: 10px;
    padding: 16px 14px;
    cursor: pointer;
    transition: all 0.18s ease;
    text-align: center;
    user-select: none;
  }
  .fc-preset:hover {
    border-color: rgba(199,125,255,0.5);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px -4px rgba(0,0,0,0.8), 0 0 14px rgba(157,78,221,0.25);
  }
  .fc-preset input { display: none; }
  .fc-preset input:checked + .fc-preset-inner {
    color: #fff;
  }
  .fc-preset input:checked ~ .fc-preset-inner { color: #fff; }
  .fc-preset:has(input:checked) {
    border-color: #9d4edd;
    background: linear-gradient(145deg, #231a33, #1a1425);
    box-shadow: 0 0 20px -4px rgba(157,78,221,0.55), inset 0 0 20px rgba(157,78,221,0.08);
  }
  .fc-preset-ram {
    font-size: 26px;
    font-weight: 800;
    letter-spacing: -1px;
    background: linear-gradient(135deg, #c77dff, #9d4edd);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    line-height: 1.1;
  }
  .fc-preset-detail {
    font-size: 10.5px;
    color: #71799a;
    margin-top: 6px;
    line-height: 1.5;
  }
  .fc-preset-badge {
    position: absolute;
    top: -8px; right: -8px;
    width: 10px; height: 10px;
    border-radius: 50%;
    background: radial-gradient(circle, #c77dff, #9d4edd);
    box-shadow: 0 0 10px rgba(157,78,221,0.8);
    opacity: 0;
    transition: opacity 0.2s;
  }
  .fc-preset:has(input:checked) .fc-preset-badge { opacity: 1; }

  .fc-form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 14px;
  }
  @media (max-width: 768px) { .fc-form-grid { grid-template-columns: 1fr; } }
  .fc-field label {
    display: block;
    color: #c4b5fd;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    margin-bottom: 6px;
  }
  .fc-field select, .fc-field input[type="text"], .fc-field input[type="number"] {
    width: 100%;
    background: #0d1018;
    border: 1px solid rgba(157,78,221,0.22);
    border-radius: 7px;
    padding: 10px 12px;
    color: #e9e8f0;
    font-size: 13px;
    transition: all 0.18s;
  }
  .fc-field select:focus, .fc-field input:focus {
    outline: none;
    border-color: #9d4edd;
    box-shadow: 0 0 0 3px rgba(157,78,221,0.15);
  }
  .fc-field option { background: #121520; color: #e9e8f0; }
  .fc-node-info {
    display: flex; flex-wrap: wrap;
    gap: 6px; margin-top: 5px;
    font-size: 10.5px; color: #6b7194;
  }
  .fc-node-info span {
    padding: 2px 8px;
    background: rgba(157,78,221,0.1);
    border-radius: 4px;
  }
  .fc-quantity-row {
    display: flex; align-items: center; gap: 10px; margin-top: 4px;
  }
  .fc-qty-btn {
    width: 34px; height: 34px;
    background: rgba(157,78,221,0.12);
    border: 1px solid rgba(157,78,221,0.3);
    color: #c77dff;
    border-radius: 7px;
    font-size: 16px; font-weight: 700;
    cursor: pointer; transition: all 0.15s;
    display: flex; align-items: center; justify-content: center;
  }
  .fc-qty-btn:hover { background: rgba(157,78,221,0.25); border-color: #9d4edd; }
  .fc-qty-input {
    width: 64px; text-align: center; font-weight: 700; font-size: 15px;
    background: #0d1018; border: 1px solid rgba(157,78,221,0.22);
    border-radius: 7px; padding: 8px 6px; color: #fff;
  }
  .fc-submit {
    width: 100%;
    margin-top: 20px;
    padding: 14px;
    background: linear-gradient(135deg, #7b2cbf, #9d4edd);
    border: 1px solid rgba(199,125,255,0.4);
    color: #fff;
    font-size: 14px;
    font-weight: 800;
    letter-spacing: 0.5px;
    border-radius: 9px;
    cursor: pointer;
    box-shadow: 0 4px 20px rgba(123,44,191,0.4);
    transition: all 0.2s;
    display: flex; align-items: center; justify-content: center; gap: 10px;
  }
  .fc-submit:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 26px rgba(157,78,221,0.6);
  }
  .fc-submit:active { transform: translateY(0); }
  .fc-submit:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }
  .fc-summary {
    background: linear-gradient(145deg, rgba(157,78,221,0.08), rgba(157,78,221,0.02));
    border: 1px dashed rgba(157,78,221,0.3);
    border-radius: 9px;
    padding: 14px 16px;
    margin-top: 18px;
    font-size: 12px;
    color: #a89ec9;
    line-height: 1.9;
  }
  .fc-summary b { color: #c77dff; }
  .fc-badge-ok {
    display: inline-block;
    padding: 3px 10px;
    background: rgba(0,245,212,0.12);
    color: #00f5d4;
    border: 1px solid rgba(0,245,212,0.3);
    border-radius: 20px;
    font-size: 10.5px;
    font-weight: 700;
    margin-left: 8px;
  }
  .fc-spinner {
    display: none;
    width: 16px; height: 16px;
    border: 2px solid rgba(255,255,255,0.3);
    border-top-color: #fff;
    border-radius: 50%;
    animation: fcspin 0.7s linear infinite;
  }
  @keyframes fcspin { to { transform: rotate(360deg); } }
  .fc-submit.loading .fc-spinner { display: inline-block; }
</style>

<div class="fc-wrap">
  <!-- Preset Selection Card -->
  <div class="fc-card">
    <h3 class="fc-title"><i class="fa fa-microchip" style="color:#c77dff"></i> Pilih Paket RAM</h3>
    <p class="fc-desc">Pilih preset sumber daya — disk mengikuti 2× RAM, CPU sesuai tier.</p>

    <form method="POST" action="{{ route('admin.extensions.darkpsyho.fastcreate.store') }}" id="fastCreateForm">
      @csrf

      <div class="fc-grid-preset">
        @foreach($presets as $key => $preset)
        <label class="fc-preset">
          <input type="radio" name="preset" value="{{ $key }}" @if($key == 2) checked @endif>
          <div class="fc-preset-inner">
            <div class="fc-preset-ram">{{ $preset['label'] }}</div>
            <div class="fc-preset-detail">
              {{ number_format($preset['disk'] / 1024, 0) }} GB Disk<br>
              {{ $preset['cpu'] }}% CPU
            </div>
          </div>
          <span class="fc-preset-badge"></span>
        </label>
        @endforeach
      </div>

      <!-- Configuration Card -->
      <div style="margin-top: 24px;">
        <div class="fc-form-grid">
          <!-- Nama Server -->
          <div class="fc-field">
            <label>Nama Server</label>
            <input type="text" name="name" placeholder="Contoh: server-utama" required maxlength="120" value="server">
          </div>

          <!-- Jumlah -->
          <div class="fc-field">
            <label>Jumlah Server (batch)</label>
            <div class="fc-quantity-row">
              <button type="button" class="fc-qty-btn" onclick="fcQty(-1)">−</button>
              <input type="number" class="fc-qty-input" name="quantity" id="fcQtyInput" value="1" min="1" max="20">
              <button type="button" class="fc-qty-btn" onclick="fcQty(1)">+</button>
              <span style="font-size:11px;color:#71799a;">Maks 20 sekaligus</span>
            </div>
          </div>

          <!-- Owner -->
          <div class="fc-field">
            <label>Pemilik (User)</label>
            <select name="user_id" required>
              <option value="">— Pilih user —</option>
              @foreach($users as $user)
                <option value="{{ $user->id }}" @if($user->root_admin) data-admin="1" @endif>
                  {{ $user->username }} ({{ $user->email }}) @if($user->root_admin) [admin] @endif
                </option>
              @endforeach
            </select>
          </div>

          <!-- Egg -->
          <div class="fc-field">
            <label>Game / Nest (Egg)</label>
            <select name="egg_id" required>
              <option value="">— Pilih egg —</option>
              @foreach($eggs as $egg)
                <option value="{{ $egg->id }}">{{ $egg->nest->name }} › {{ $egg->name }}</option>
              @endforeach
            </select>
          </div>

          <!-- Location -->
          <div class="fc-field">
            <label>Lokasi (Opsional — kosong = otomatis)</label>
            <select name="location_id">
              <option value="">— Otomatis pilih node terbaik —</option>
              @foreach($locations as $location)
                <option value="{{ $location->id }}">{{ $location->long }} ({{ $location->short }})</option>
              @endforeach
            </select>
          </div>

          <div class="fc-field">
            <label>&nbsp;</label>
            <div style="font-size: 11.5px; color: #71799a; line-height: 1.7; padding-top: 10px;">
              <i class="fa fa-info-circle" style="color: #9d4edd"></i>
              Server akan dibuat dengan <b style="color: #c77dff">auto-allocation</b> — port & node terbaik dipilih otomatis.
            </div>
          </div>
        </div>

        <!-- Summary -->
        <div class="fc-summary" id="fcSummary"></div>

        <button type="submit" class="fc-submit" id="fcSubmit">
          <span class="fc-spinner"></span>
          <span id="fcSubmitText"><i class="fa fa-bolt"></i> Buat Server Sekarang</span>
        </button>
      </div>
    </form>
  </div>

  <!-- Info Card -->
  <div class="fc-card" style="background: linear-gradient(145deg, rgba(157,78,221,0.06), rgba(18,21,32,1));">
    <h3 class="fc-title" style="font-size: 13px;"><i class="fa fa-shield"></i> ZeroTzy Darkpsyho</h3>
    <p style="color:#71799a; font-size:11.5px; margin:0; line-height:1.8;">
      Panel Fast Create ini khusus administrator. Server yang dibuat otomatis mendapat konfigurasi standar:<br>
      • Swap 0 MB &nbsp;• IO Weight 500 &nbsp;• Database limit 2 &nbsp;• Backup limit 2 &nbsp;• OOM Killer aktif
    </p>
  </div>
</div>

<script>
  function fcQty(delta) {
    const input = document.getElementById('fcQtyInput');
    let v = parseInt(input.value) || 1;
    v = Math.min(20, Math.max(1, v + delta));
    input.value = v;
    updateSummary();
  }

  function getPreset() {
    const checked = document.querySelector('input[name="preset"]:checked');
    return checked ? @json($presets)[checked.value] : null;
  }

  function updateSummary() {
    const preset = getPreset();
    const qty = parseInt(document.getElementById('fcQtyInput').value) || 1;
    const name = document.querySelector('input[name="name"]').value || '(belum diisi)';
    const el = document.getElementById('fcSummary');
    if (!preset) { el.innerHTML = ''; return; }
    const totalMem = (preset.memory * qty) / 1024;
    const totalDisk = (preset.disk * qty) / 1024;
    el.innerHTML = `
      <i class="fa fa-list-ul" style="color:#9d4edd"></i> <b>Ringkasan:</b><br>
      • Preset: <b>${preset.label} RAM</b> / ${preset.disk/1024} GB Disk / ${preset.cpu}% CPU<br>
      • Jumlah: <b>${qty} server</b> — Total sumber daya: <b>${totalMem} GB RAM</b>, <b>${totalDisk} GB Disk</b><br>
      • Nama: <b>${name}</b>${qty > 1 ? ' (akan jadi ' + name + ' #1, #2, dst)' : ''}
    `;
  }

  document.querySelectorAll('input[name="preset"]').forEach(r => r.addEventListener('change', updateSummary));
  document.getElementById('fcQtyInput').addEventListener('input', updateSummary);
  document.querySelector('input[name="name"]').addEventListener('input', updateSummary);
  updateSummary();

  document.getElementById('fastCreateForm').addEventListener('submit', function() {
    const btn = document.getElementById('fcSubmit');
    btn.classList.add('loading');
    btn.disabled = true;
    document.getElementById('fcSubmitText').innerHTML = 'Membuat server...';
  });
</script>
@endsection
