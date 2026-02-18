
FROM wordpress:latest

# Copiar el contenido de wp-content personalizado
COPY wp-content/ /var/www/html/wp-content/

# Crear copia de seguridad de uploads para inicializar el volumen
RUN mkdir -p /usr/src/uploads_seed && \
    cp -r /var/www/html/wp-content/uploads/* /usr/src/uploads_seed/ || true

# Copiar script de inicialización
COPY init-uploads.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/init-uploads.sh

# Asegurar permisos correctos en el directorio web
RUN chown -R www-data:www-data /var/www/html/wp-content

# Usar script personalizado como Entrypoint
ENTRYPOINT ["init-uploads.sh"]
CMD ["apache2-foreground"]
