#!/bin/bash
# ============================================================
# Blueprint Panel Restore Script
# Theme Nebula v2.0-1 + Blueprint Framework
# Dipakai untuk deploy ke panel Pterodactyl lain
# ============================================================
set -e

RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; CYAN='\033[0;36m'; RESET='\033[0m'
info()  { echo -e "${CYAN}[INFO]${RESET} $1"; }
ok()    { echo -e "${GREEN}[OK]${RESET} $1"; }
warn()  { echo -e "${YELLOW}[WARN]${RESET} $1"; }
fail()  { echo -e "${RED}[FAIL]${RESET} $1"; exit 1; }

PANEL_DIR="/var/www/pterodactyl"
BACKUP_DIR="$(cd "$(dirname "$0")" && pwd)"

[ -f "$PANEL_DIR/artisan" ] || fail "Panel Pterodactyl tidak ditemukan di $PANEL_DIR"
[ -f "$BACKUP_DIR/files/.blueprint/extensions/nebula/private/.store/conf.yml" ] || fail "File backup theme tidak lengkap"

info "Memulai restore Blueprint + Theme Nebula..."

# --- 1. Salin file framework & theme ---
info "Menyalin file Blueprint Framework & Theme Nebula..."
cp -r "$BACKUP_DIR/files/.blueprint" "$PANEL_DIR/"
cp -r "$BACKUP_DIR/files/app/BlueprintFramework" "$PANEL_DIR/app/"
cp -r "$BACKUP_DIR/files/app/Console/Commands" "$PANEL_DIR/app/Console/" 2>/dev/null || true
cp -r "$BACKUP_DIR/files/app/Http/Controllers/Admin/Extensions" "$PANEL_DIR/app/Http/Controllers/Admin/" 2>/dev/null || true
cp "$BACKUP_DIR/files/app/Http/Controllers/Admin/ExtensionsController.php" "$PANEL_DIR/app/Http/Controllers/Admin/" 2>/dev/null || true
cp -r "$BACKUP_DIR/files/resources/views/blueprint" "$PANEL_DIR/resources/views/"
cp -r "$BACKUP_DIR/files/resources/views/admin" "$PANEL_DIR/resources/views/"
cp -r "$BACKUP_DIR/files/resources/views/layouts" "$PANEL_DIR/resources/views/"
cp -r "$BACKUP_DIR/files/resources/views/templates" "$PANEL_DIR/resources/views/" 2>/dev/null || true
cp -r "$BACKUP_DIR/files/resources/scripts/blueprint" "$PANEL_DIR/resources/scripts/"
cp -r "$BACKUP_DIR/files/public/extensions" "$PANEL_DIR/public/"
cp -r "$BACKUP_DIR/files/public/themes" "$PANEL_DIR/public/"
cp -r "$BACKUP_DIR/files/routes/blueprint.php" "$PANEL_DIR/routes/" 2>/dev/null || true
cp -r "$BACKUP_DIR/files/routes/blueprint" "$PANEL_DIR/routes/" 2>/dev/null || true
cp "$BACKUP_DIR/files/blueprint.sh" "$PANEL_DIR/"
cp -r "$BACKUP_DIR/files/scripts" "$PANEL_DIR/"
cp -r "$BACKUP_DIR/files/config" "$PANEL_DIR/"
ok "File disalin."

# --- 2. Salin .env ---
if [ -f "$PANEL_DIR/.env" ]; then
  warn ".env sudah ada, dibackup ke .env.pre-blueprint.bak"
  cp "$PANEL_DIR/.env" "$PANEL_DIR/.env.pre-blueprint.bak"
fi
cp "$BACKUP_DIR/files/.env" "$PANEL_DIR/.env"
ok ".env dipasang (pastikan sesuaikan APP_URL, DB_*, MAIL_* dengan server baru!)"

# --- 3. Database ---
info "Restore database..."
read -rp "Masukkan nama database MySQL baru: " DB_NAME
read -rp "Masukkan user database: " DB_USER
read -rp "Masukkan password database: " DB_PASS

mysql -u"$DB_USER" -p"$DB_PASS" -e "CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$BACKUP_DIR/database.sql"

# Update .env dengan kredensial database baru
sed -i "s/^DB_DATABASE=.*/DB_DATABASE=$DB_NAME/" "$PANEL_DIR/.env"
sed -i "s/^DB_USERNAME=.*/DB_USERNAME=$DB_USER/" "$PANEL_DIR/.env"
sed -i "s/^DB_PASSWORD=.*/DB_PASSWORD=$DB_PASS/" "$PANEL_DIR/.env"
ok "Database diimport & .env diperbarui."

# --- 4. Install blueprint CLI + cache ---
info "Menginstal Blueprint CLI..."
cd "$PANEL_DIR"
bash blueprint.sh --install >/dev/null 2>&1 || true
chmod +x /usr/local/bin/blueprint 2>/dev/null || true

# --- 5. Rebuild frontend ---
info "Build ulang frontend (yarn)..."
command -v yarn >/dev/null 2>&1 || npm i -g yarn
yarn install
yarn build:production

# --- 6. Cache & permission ---
php artisan config:clear
php artisan view:clear
php artisan route:clear
php artisan cache:clear
php artisan bp:cache || true
chown -R www-data:www-data "$PANEL_DIR" || true
ok "Cache dibersihkan & permission disesuaikan."

echo ""
ok "SELESAI! Blueprint + Theme Nebula sudah terpasang di panel ini."
echo -e "  ${CYAN}→${RESET} Login ke panel, cek halaman Admin > Extensions untuk memastikan theme Nebula aktif."
