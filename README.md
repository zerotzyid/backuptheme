# ZeroTzy - Theme : Darkpsyho 🔮

Tema kustom eksklusif **ZeroTzy - Theme** (Identifier: `darkpsyho`) untuk **Pterodactyl Panel** berbasis Blueprint Framework.

---

## 🎨 Perbedaan Tampilan dengan Nebula Standar

1. **Void Cyber Palette**:
   - Background utama mengusung deep void black (`#090a0f`) menggantikan abu-abu kebiruan standar.
   - Panel cards dan box konten menggunakan dark surface (`#121520`).
2. **Neon Psycho Accents**:
   - Glowing Neon Purple (`#9d4edd`, `#c77dff`) pada tombol, border aktif, dan indikator sidebar.
   - Status server cyberpunk: Online (`#00f5d4` - Electric Cyan), Starting (`#f72585` - Neon Pink), Error (`#ff0054` - Blood Crimson).
3. **Glowing Psycho Scrollbar**:
   - Scrollbar kustom dengan gradient neon purple glow (`#9d4edd` ke `#7209b7`).
4. **Glassmorphism & Neon Borders**:
   - Efek glow border halus pada kartu server, dropdown, dan modal dialog.
5. **Zero Telemetry & 100% Privacy**:
   - Pelacak bawaan Nebula (`dart.js`, `plausible_tracking`, dan script eksternal `prototype`) telah dibersihkan secara total.
6. **Custom Branding**:
   - Seluruh label, watermark, admin controller, dan customizer UI telah di-recode dengan identitas **ZeroTzy - Theme (Darkpsyho)**.

---

## 🚀 Cara Memasang Theme (Standalone Blueprint Extension)

Jika panel Anda sudah terpasang **Blueprint Framework**, pasang langsung menggunakan file `.blueprint`:

```bash
# 1. Unduh file tema
wget https://github.com/zerotzyid/backuptheme/raw/main/darkpsyho.blueprint

# 2. Pindahkan ke folder panel
mv darkpsyho.blueprint /var/www/pterodactyl/

# 3. Jalankan instalasi Blueprint
cd /var/www/pterodactyl
blueprint -i darkpsyho.blueprint
```

---

## 🛠️ Cara Restore Penuh ke Panel Baru

Jika ingin merestore seluruh panel (Blueprint + Theme + Database dump):

```bash
git clone https://github.com/zerotzyid/backuptheme.git
cd backuptheme
bash install.sh
```

---

## 📂 Struktur Repositori

- `darkpsyho.blueprint` : Paket installer resmi tema ZeroTzy (siap install via `blueprint -i`).
- `themes/darkpsyho/` : Source code hasil recode tema Darkpsyho.
- `database.sql` : Dump database panel Pterodactyl.
- `install.sh` : Skrip installer restore otomatis untuk panel baru.
- `files/` : Cadangan file Blueprint framework dan dependensi.

---
*Created by **zerotzyid**.*
