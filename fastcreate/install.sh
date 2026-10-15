#!/bin/bash
# ==============================================================
# ZeroTzy Darkpsyho — Fast Create Server (Admin)
# Installation script
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
        Fast Create Server — Admin Only (v1.0.0)
EOF
echo -e "${RESET}"

# Pastikan folder darkpsyho ada
mkdir -p "$PANEL/app/Http/Controllers/Admin/Extensions/darkpsyho"
mkdir -p "$PANEL/resources/views/admin/extensions/darkpsyho"

# 1. Copy controller
cp -f controller/FastServerController.php "$PANEL/app/Http/Controllers/Admin/Extensions/darkpsyho/"
cp -f controller/FastServerFormRequest.php "$PANEL/app/Http/Requests/Admin/Extensions/darkpsyho/" 2>/dev/null || {
  mkdir -p "$PANEL/app/Http/Requests/Admin/Extensions/darkpsyho"
  cp -f controller/FastServerFormRequest.php "$PANEL/app/Http/Requests/Admin/Extensions/darkpsyho/"
}
echo -e "${GREEN}[OK]${RESET} Controller terpasang."

# 2. Copy view
cp -f views/fastcreate.blade.php "$PANEL/resources/views/admin/extensions/darkpsyho/"
echo -e "${GREEN}[OK]${RESET} View terpasang."

# 3. Tambahkan route jika belum ada
ROUTE_FILE="$PANEL/routes/blueprint.php"
if ! grep -q "fastcreate" "$ROUTE_FILE"; then
  cat >> "$ROUTE_FILE" <<'EOF'

/* ═══════════════════════════════════════════════════════════
   ZeroTzy Darkpsyho — Fast Create Server (Admin only)
   ═══════════════════════════════════════════════════════════ */
Route::group(['prefix' => 'extensions/darkpsyho', 'middleware' => ['web', 'auth', 'admin']], function () {
  Route::get('/fastcreate', [\Pterodactyl\Http\Controllers\Admin\Extensions\darkpsyho\FastServerController::class, 'index'])
    ->name('admin.extensions.darkpsyho.fastcreate.index');
  Route::post('/fastcreate', [\Pterodactyl\Http\Controllers\Admin\Extensions\darkpsyho\FastServerController::class, 'store'])
    ->name('admin.extensions.darkpsyho.fastcreate.store');
});
EOF
  echo -e "${GREEN}[OK]${RESET} Route ditambahkan ke routes/blueprint.php"
else
  echo -e "${CYAN}[SKIP]${RESET} Route sudah ada."
fi

# 4. Tambahkan sidebar link di admin layout
LAYOUT_FILE="$PANEL/resources/views/layouts/admin.blade.php"
if ! grep -q "fastcreate" "$LAYOUT_FILE"; then
  # sisipkan sebelum "SERVICE MANAGEMENT" header
  python3 - <<PYEOF
path = "$LAYOUT_FILE"
with open(path, "r") as f:
    content = f.read()

sidebar_item = '''                        <li class="header">QUICK ACTIONS</li>
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.extensions.darkpsyho.fastcreate') ?: 'active' }}">
                            <a href="{{ route('admin.extensions.darkpsyho.fastcreate.index') }}" style="color: #c77dff;">
                                <i class="fa fa-bolt"></i> <span>⚡ Fast Create</span>
                            </a>
                        </li>
                        <li class="header">SERVICE MANAGEMENT</li>'''

content = content.replace('<li class="header">SERVICE MANAGEMENT</li>', sidebar_item, 1)
with open(path, "w") as f:
    f.write(content)
print("Sidebar injected.")
PYEOF
  echo -e "${GREEN}[OK]${RESET} Sidebar link ditambahkan."
else
  echo -e "${CYAN}[SKIP]${RESET} Sidebar link sudah ada."
fi

# 5. Copy icon ke public
mkdir -p "$PANEL/public/assets/extensions/darkpsyho"
if [ -f darkpsyho.jpg ]; then
  cp -f darkpsyho.jpg "$PANEL/public/assets/extensions/darkpsyho/icon.jpg"
  echo -e "${GREEN}[OK]${RESET} Icon terpasang."
fi

# 6. Fix permission
chown -R www-data:www-data \
  "$PANEL/app/Http/Controllers/Admin/Extensions/darkpsyho" \
  "$PANEL/app/Http/Requests/Admin/Extensions/darkpsyho" \
  "$PANEL/resources/views/admin/extensions/darkpsyho" \
  "$PANEL/public/assets/extensions/darkpsyho" \
  2>/dev/null || true

# 7. Clear cache
cd "$PANEL"
php artisan view:clear > /dev/null 2>&1 || true
php artisan route:clear > /dev/null 2>&1 || true
php artisan config:clear > /dev/null 2>&1 || true
echo -e "${GREEN}[OK]${RESET} Cache panel dibersihkan."

echo ""
echo -e "${GREEN}════════════════════════════════════════════════${RESET}"
echo -e "${GREEN} ✓ SELESAI! Fast Create Server terpasang.${RESET}"
echo -e "${CYAN} → Akses: https://panel-kamu.com/admin/extensions/darkpsyho/fastcreate${RESET}"
echo -e "${GREEN}════════════════════════════════════════════════${RESET}"
