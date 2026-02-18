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

# Ejecutar el entrypoint original de WordPress
exec docker-entrypoint.sh "$@"
