#!/bin/bash
set -e

# Copiar uploads desde la imagen (seed) al volumen persistente si existen
# Usamos -n para no sobrescribir archivos que ya existan en el volumen (persistencia)
if [ -d "/usr/src/uploads_seed" ] && [ -d "/var/www/html/wp-content/uploads" ]; then
    echo "Sincronizando uploads desde la imagen al volumen..."
    cp -rn /usr/src/uploads_seed/* /var/www/html/wp-content/uploads/ || true
    
    # Asegurar permisos
    chown -R www-data:www-data /var/www/html/wp-content/uploads/
fi

# Ejecutar el entrypoint original de WordPress
exec docker-entrypoint.sh "$@"
