#!/bin/bash
# ==============================================================
# ZeroTzy Darkpsyho — Auto Deploy Server (UFW + Cloudflare Tunnel)
# Installation Script v1.1.0
# ==============================================================
set -e

PANEL="/var/www/pterodactyl"
RED='\033[0;31m'; GREEN='\033[0;32m'; CYAN='\033[0;36m'; MAG='\033[0;35m'; YEL='\033[1;33m'; RESET='\033[0m'

if [ "$EUID" -ne 0 ]; then
  echo -e "${RED}[FAIL]${RESET} Script ini harus dijalankan sebagai root."
  exit 1
fi

echo -e "${MAG}"
cat << "EOF"
 ██████╗  █████╗ ███████╗████████╗ ██████╗██████╗ ███████╗
 ██╔══██╗██╔══██╗██╔════╝╚══██╔══╝██╔════╝██╔══██╗██╔════╝
 ██████╔╝███████║███████╗   ██║   ██║     ██████╔╝█████╗
 ██╔══██╗██╔══██║╚════██║   ██║   ██║     ██╔══██╗██╔══╝
 ██║  ██║██║  ██║███████║   ██║   ╚██████╗██║  ██║███████╗
 ╚═╝  ╚═╝╚═╝  ╚═╝╚══════╝   ╚═╝    ╚═════╝╚═╝  ╚═╝╚══════╝
      ⚡ Auto Deploy Server — UFW + Cloudflare Tunnel v1.1.0
EOF
echo -e "${RESET}"

# Pastikan folder ada
mkdir -p "$PANEL/app/Http/Controllers/Admin/Extensions/darkpsyho"
mkdir -p "$PANEL/app/Services/AutoDeploy"
mkdir -p "$PANEL/database/migrations"
mkdir -p "$PANEL/resources/views/admin/extensions/darkpsyho"
mkdir -p "$PANEL/public/assets/extensions/darkpsyho"

# 1. Copy controller
cp -f controller/AutoDeployController.php "$PANEL/app/Http/Controllers/Admin/Extensions/darkpsyho/"
echo -e "${GREEN}[OK]${RESET} Auto Deploy Controller terpasang."

# 2. Copy AutoDeployService
cp -f scripts/AutoDeployService.php "$PANEL/app/Services/AutoDeploy/"
echo -e "${GREEN}[OK]${RESET} AutoDeployService terpasang."

# 3. Copy model
cp -f models/AutoDeployConfig.php "$PANEL/app/Models/"
echo -e "${GREEN}[OK]${RESET} AutoDeployConfig Model terpasang."

# 4. Copy migration
cp -f database/migrations/create_auto_deploy_configs_table.php "$PANEL/database/migrations/"
echo -e "${GREEN}[OK]${RESET} Migration terpasang."

# 5. Copy view
cp -f views/autodeploy.blade.php "$PANEL/resources/views/admin/extensions/darkpsyho/"
echo -e "${GREEN}[OK]${RESET} Auto Deploy View terpasang."

# 6. Run migration
cd "$PANEL"
php artisan migrate --graceful --ansi > /dev/null 2>&1 || {
  echo -e "${YEL}[WARN]${RESET} Migration mungkin sudah ada atau gagal, coba cek manually."
}
echo -e "${GREEN}[OK]${RESET} Database migration dijalankan."

# 7. Tambahkan route
ROUTE_FILE="$PANEL/routes/blueprint.php"
if ! grep -q "autodeploy" "$ROUTE_FILE"; then
  cat >> "$ROUTE_FILE" <<'ROUTE_EOF'

/* ZeroTzy Darkpsyho — Auto Deploy Server (Admin only) */
Route::group(['prefix' => 'extensions/darkpsyho'], function () {
  Route::get('/autodeploy', [\Pterodactyl\Http\Controllers\Admin\Extensions\darkpsyho\AutoDeployController::class, 'index'])
    ->name('admin.extensions.darkpsyho.autodeploy.index');
  Route::post('/autodeploy', [\Pterodactyl\Http\Controllers\Admin\Extensions\darkpsyho\AutoDeployController::class, 'store'])
    ->name('admin.extensions.darkpsyho.autodeploy.store');
  Route::get('/autodeploy/status/{serverId}', [\Pterodactyl\Http\Controllers\Admin\Extensions\darkpsyho\AutoDeployController::class, 'status'])
    ->name('admin.extensions.darkpsyho.autodeploy.status');
});
ROUTE_EOF
  echo -e "${GREEN}[OK]${RESET} Route ditambahkan ke routes/blueprint.php"
else
  echo -e "${CYAN}[SKIP]${RESET} Route sudah ada."
fi

# 8. Tambahkan sidebar link
LAYOUT_FILE="$PANEL/resources/views/layouts/admin.blade.php"
if ! grep -q "autodeploy" "$LAYOUT_FILE"; then
  python3 - <<PYEOF
path = "$LAYOUT_FILE"
with open(path) as f:
    content = f.read()

old = '<li class="header">QUICK ACTIONS</li>\n                        <li class="{{ ! starts_with(Route::currentRouteName(), "admin.extensions.darkpsyho.fastcreate") ?: "active" }}">'
new = '''<li class="header">DEPLOYMENT</li>
                        <li class="{{ ! starts_with(Route::currentRouteName(), "admin.extensions.darkpsyho.autodeploy") ?: "active" }}">
                            <a href="{{ route("admin.extensions.darkpsyho.autodeploy.index") }}" style="color: #ff0054;">
                                <i class="fa fa-bolt"></i> <span>⚡ Auto Deploy</span>
                            </a>
                        </li>
                        <li class="header">QUICK ACTIONS</li>
                        <li class="{{ ! starts_with(Route::currentRouteName(), "admin.extensions.darkpsyho.fastcreate") ?: "active" }}">'''

assert old in content, "anchor not found"
content = content.replace(old, new, 1)
with open(path, "w") as f:
    f.write(content)
print("SIDEBAR_INJECTED")
PYEOF
  echo -e "${GREEN}[OK]${RESET} Sidebar link ditambahkan."
else
  echo -e "${CYAN}[SKIP]${RESET} Sidebar link sudah ada."
fi

# 9. Copy icon
if [ -f darkpsyho.jpg ]; then
  cp -f darkpsyho.jpg "$PANEL/public/assets/extensions/darkpsyho/icon.jpg" 2>/dev/null || true
  echo -e "${GREEN}[OK]${RESET} Icon terpasang."
fi

# 10. Fix permission & cache
chown -R www-data:www-data \
  "$PANEL/app/Http/Controllers/Admin/Extensions/darkpsyho" \
  "$PANEL/app/Services/AutoDeploy" \
  "$PANEL/app/Models/AutoDeployConfig.php" \
  "$PANEL/database/migrations/create_auto_deploy_configs_table.php" \
  "$PANEL/resources/views/admin/extensions/darkpsyho" \
  "$PANEL/public/assets/extensions/darkpsyho" \
  2>/dev/null || true

cd "$PANEL"
php artisan view:clear > /dev/null 2>&1 || true
php artisan route:clear > /dev/null 2>&1 || true
php artisan config:clear > /dev/null 2>&1 || true
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
echo -e "${GREEN}[OK]${RESET} Cache panel dibersihkan & permission di-fix."

echo ""
echo -e "${GREEN}════════════════════════════════════════════════${RESET}"
echo -e "${GREEN} ✓ SELESAI! Auto Deploy Server v1.1.0 terpasang.${RESET}"
echo -e "${CYAN} → Akses: https://panel-kamu.com/admin/extensions/darkpsyho/autodeploy${RESET}"
echo -e "${CYAN} 💡 Fitur ini juga bisa akses dari sidebar DEPLOYMENT${RESET}"
echo -e "${GREEN}════════════════════════════════════════════════${RESET}"

echo ""
echo -e "${YEL}ℹ️ Catatan Penting:${RESET}"
echo "1. Cloudflare Tunnel memerlukan token autentikasi dari dashboard.cloudflare.com"
echo "2. UFW akan dikonfigurasi otomatis jika Anda punya akses SSH ke server"
echo "3. Untuk pertama kali, pastikan firewall tidak memblokir port SSH!"
echo "4. Database tabel 'auto_deploy_configs' telah dibuat."
