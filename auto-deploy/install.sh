#!/bin/bash
# ==============================================================
# ZeroTzy Darkpsyho — Auto Deploy Server (UFW + Cloudflare)
# Installation Script
# ==============================================================
set -e

PANEL="/var/www/pterodactyl"
RED='\033[0;31m'; GREEN='\033[0;32m'; CYAN='\033[0;36m'; MAG='\033[0;35m'; RESET='\033[0m'

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
      ⚡ Auto Deploy Server — UFW + Cloudflare Tunnel v1.0.0
EOF
echo -e "${RESET}"

# Pastikan folder auto-deploy ada
mkdir -p "$PANEL/app/Http/Controllers/Admin/Extensions/darkpsyho"
mkdir -p "$PANEL/resources/views/admin/extensions/darkpsyho"

# 1. Copy controller
cp -f controller/AutoDeployController.php "$PANEL/app/Http/Controllers/Admin/Extensions/darkpsyho/"
echo -e "${GREEN}[OK]${RESET} Auto Deploy Controller terpasang."

# 2. Copy view
cp -f views/autodeploy.blade.php "$PANEL/resources/views/admin/extensions/darkpsyho/"
echo -e "${GREEN}[OK]${RESET} Auto Deploy View terpasang."

# 3. Tambahkan route
ROUTE_FILE="$PANEL/routes/blueprint.php"
if ! grep -q "autodeploy" "$ROUTE_FILE"; then
  cat >> "$ROUTE_FILE" <<'ROUTE_EOF'

/* ZeroTzy Darkpsyho — Auto Deploy Server (Admin only) */
Route::group(['prefix' => 'extensions/darkpsyho'], function () {
  Route::get('/autodeploy', [\Pterodactyl\Http\Controllers\Admin\Extensions\darkpsyho\AutoDeployController::class, 'index'])
    ->name('admin.extensions.darkpsyho.autodeploy.index');
  Route::post('/autodeploy', [\Pterodactyl\Http\Controllers\Admin\Extensions\darkpsyho\AutoDeployController::class, 'store'])
    ->name('admin.extensions.darkpsyho.autodeploy.store');
});
ROUTE_EOF
  echo -e "${GREEN}[OK]${RESET} Route ditambahkan ke routes/blueprint.php"
else
  echo -e "${CYAN}[SKIP]${RESET} Route sudah ada."
fi

# 4. Tambahkan sidebar link
LAYOUT_FILE="$PANEL/resources/views/layouts/admin.blade.php"
if ! grep -q "Autodeploy" "$LAYOUT_FILE"; then
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

# 5. Copy icon ke public (reuse darkpsyho icon)
mkdir -p "$PANEL/public/assets/extensions/darkpsyho"
if [ -f darkpsyho.jpg ]; then
  cp -f darkpsyho.jpg "$PANEL/public/assets/extensions/darkpsyho/icon.jpg" 2>/dev/null || true
  echo -e "${GREEN}[OK]${RESET} Icon terpasang."
fi

# 6. Fix permission & cache
chown -R www-data:www-data \
  "$PANEL/app/Http/Controllers/Admin/Extensions/darkpsyho" \
  "$PANEL/resources/views/admin/extensions/darkpsyho" \
  2>/dev/null || true

cd "$PANEL"
php artisan view:clear > /dev/null 2>&1 || true
php artisan route:clear > /dev/null 2>&1 || true
php artisan config:clear > /dev/null 2>&1 || true
echo -e "${GREEN}[OK]${RESET} Cache panel dibersihkan."

echo ""
echo -e "${GREEN}════════════════════════════════════════════════${RESET}"
echo -e "${GREEN} ✓ SELESAI! Auto Deploy Server terpasang.${RESET}"
echo -e "${CYAN} → Akses: https://panel-kamu.com/admin/extensions/darkpsyho/autodeploy${RESET}"
echo -e "${CYAN} 💡 Fitur ini juga bisa akses dari sidebar DEPLOYMENT${RESET}"
echo -e "${GREEN}════════════════════════════════════════════════${RESET}"

echo ""
echo -e "${YELLOW}ℹ️ Catatan:${RESET}"
echo "• Cloudflare Tunnel memerlukan token autentikasi yang dapat diambil dari dashboard.cloudflare.com"
echo "• UFW akan dikonfigurasi secara otomatis jika Anda punya akses SSH ke server"
echo "• Untuk deployment pertama kali tanpa UFW, pastikan firewall tidak memblokir port!"
