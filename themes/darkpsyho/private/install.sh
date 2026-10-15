#!/bin/bash
# ==============================================================
# ZeroTzy - Theme : Darkpsyho
# Official Installation Script
# ==============================================================

echo -e "\n\x1b[35;1m
 ███████╗███████╗██████╗  ██████╗ ████████╗███████╗██╗   ██╗
 ╚══███╔╝██╔════╝██╔══██╗██╔═══██╗╚══██╔══╝╚══███╔╝╚██╗ ██╔╝
   ███╔╝ █████╗  ██████╔╝██║   ██║   ██║     ███╔╝  ╚████╔╝ 
  ███╔╝  ██╔══╝  ██╔══██╗██║   ██║   ██║    ███╔╝    ╚██╔╝  
 ███████╗███████╗██║  ██║╚██████╔╝   ██║   ███████╗   ██║   
 ╚══════╝╚══════╝╚═╝  ╚═╝ ╚═════╝    ╚═╝   ╚══════╝   ╚═╝   
\x1b[0m\x1b[36;1m         -= ZeroTzy - Theme : Darkpsyho v1.0.0 =-\x1b[0m"

echo -e "
\x1b[35;1m┃ ✦ ZeroTzy Darkpsyho Theme\x1b[0m
\x1b[35;1m┃\x1b[0m Installing aesthetic dark cyberpunk theme with glowing purple accents.
\x1b[35;1m┃\x1b[0m Author: \x1b[36mzerotzyid\x1b[0m
\x1b[35;1m┃\x1b[0m Repository: \x1b[36mhttps://github.com/zerotzyid/backuptheme\x1b[0m
"

printf "\r\x1b[35;1m┃\x1b[0m Menyiapkan berkas tema ZeroTzy Darkpsyho..\n"

# Ensure permissions
chmod -R 755 "$PTERODACTYL_DIRECTORY/.blueprint/extensions/darkpsyho" 2>/dev/null || true

printf "\x1b[32;1m┃ [OK]\x1b[0m Tema ZeroTzy Darkpsyho berhasil terpasang!\n\n"
