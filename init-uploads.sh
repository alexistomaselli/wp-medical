#!/bin/bash
set -e

# Copiar uploads desde la imagen (seed) al volumen persistente si existen
# Usamos -n para no sobrescribir archivos que ya existan en el volumen (persistencia)
if [ -d "/usr/src/uploads_seed" ] && [ -d "/var/www/html/wp-content/uploads" ]; then
    echo "Sincronizando uploads desde la imagen al volumen..."
    cp -rn /usr/src/uploads_seed/* /var/www/html/wp-content/uploads/ || true
fi

# Sincronizar temas y plugins desde la imagen (seed) al volumen persistente
# Aquí usamos -r (sin -n) porque QUEREMOS sobrescribir con el código nuevo del deploy
if [ -d "/usr/src/user_wp_content" ]; then
    echo "Sincronizando temas y plugins desde el deploy..."
    
    # Sincronizar Temas
    if [ -d "/usr/src/user_wp_content/themes" ]; then
        cp -r /usr/src/user_wp_content/themes/* /var/www/html/wp-content/themes/ || true
    fi

    # Sincronizar Plugins
    if [ -d "/usr/src/user_wp_content/plugins" ]; then
        cp -r /usr/src/user_wp_content/plugins/* /var/www/html/wp-content/plugins/ || true
    fi
    
    # Asegurar permisos generales
    chown -R www-data:www-data /var/www/html/wp-content
fi

echo "Aplicando reglas de seguridad (Hardening)..."
# Bloquear listados de directorios y acceso a archivos sensibles en wp-content
cat << 'EOF' > /var/www/html/wp-content/.htaccess
Options -Indexes
<FilesMatch "\.(sql|log|sh|env|bak)$">
    Require all denied
</FilesMatch>
EOF

# Bloquear ejecución de PHP en el directorio de subidas (Uploads)
mkdir -p /var/www/html/wp-content/uploads
cat << 'EOF' > /var/www/html/wp-content/uploads/.htaccess
<FilesMatch "\.ph(p[3-8]?|t|tml)$">
    Require all denied
</FilesMatch>
EOF

# Desactivar edición de archivos de plugins y themes desde el panel de WP
if [ -f "/var/www/html/wp-config.php" ]; then
    if ! grep -q "DISALLOW_FILE_EDIT" /var/www/html/wp-config.php; then
        sed -i "/table_prefix/a define('DISALLOW_FILE_EDIT', true);" /var/www/html/wp-config.php
    fi
fi

# Ejecutar el entrypoint original de WordPress
exec docker-entrypoint.sh "$@"
