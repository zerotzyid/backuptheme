@if($n_init != "1.0.0")
  <style class="init-error-style">@import url("/extensions/darkpsyho/libraries/initStyles.css?t=1792029688");</style>
  @if(Auth::check() != true)
    @include('blueprint.extensions.darkpsyho.wrapper.initialize.auth')
  @else
    @if(Auth::user()->root_admin == 1)
      <div class="init-error-bg">
        <div class="init-error-overlay">
          <div class="nebula-onboarding-container" style="border: 1px solid rgba(157, 78, 221, 0.4); box-shadow: 0 0 25px rgba(157, 78, 221, 0.3);">
            <p class="nebula-onboarding-icon" style="color: #c77dff;">
              <i class="bi bi-shield-shaded"></i>
              <span>ZeroTzy - Theme</span>
            </p>
            <div class="nebula-onboarding-content">
              <p class="nebula-onboarding-text">Selamat datang! Tema <b>ZeroTzy - Darkpsyho</b> telah aktif. Kustomisasi tampilan panel di <b>Darkpsyho Designer</b>.</p>
              <p align="center"><a href="/admin/extensions/darkpsyho"><button type="button" class="nebula-onboarding-button" style="background: linear-gradient(135deg, #7b2cbf, #9d4edd); border: none; box-shadow: 0 0 12px rgba(157, 78, 221, 0.5);">Mulai Konfigurasi <i class="bi bi-arrow-right"></i></button></a></p>
            </div>
          </div>
        </div>
      </div>
    @else
      <div class="init-error-bg">
        <div class="init-error-overlay">
          <p>Anda tidak memiliki akses administrator untuk mengatur tema ZeroTzy Darkpsyho. Harap hubungi administrator.</p>
        </div>
      </div>
    @endif
  @endif
@endif
