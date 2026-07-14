#!/bin/sh
set -eu

# Génère php/config.php depuis les secrets Discord OAuth2 (Komodo) — pas de
# volume nécessaire, régénéré à chaque démarrage.
cat > /var/www/html/php/config.php <<EOF
<?php
define("CLIENT_ID", "${DISCORD_CLIENT_ID:?DISCORD_CLIENT_ID manquant}");
define("CLIENT_SECRET", "${DISCORD_CLIENT_SECRET:?DISCORD_CLIENT_SECRET manquant}");
define("REDIRECT_URI", "${DISCORD_REDIRECT_URI:?DISCORD_REDIRECT_URI manquant}");
?>
EOF

exec "$@"
